<?php

namespace App\Services;

use App\Enums\ProductionOrderStatus;
use App\Models\ProductionOrder;
use App\Models\SapImportLog;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Throwable;

class SapImportService
{
    /**
     * Map of known column aliases for flexible SAP header detection.
     *
     * @var array<string, list<string>>
     */
    protected array $headerAliases = [
        'po_number' => [
            'no po', 'po number', 'po_number', 'order number', 'no order', 'nomor po', 'nomor order', 'nopo', 'po',
        ],
        'no_obc' => [
            'no obc', 'obc', 'no_obc', 'nomor obc', 'no. obc', 'no.obc',
        ],
        'perso_type' => [
            'perso', 'perso / non perso', 'perso/non perso', 'perso type', 'perso_type', 'tipe perso', 'perso / non',
        ],
        'seri' => [
            'seri', 'series', 'no seri', 'nomor seri',
        ],
        'order_date' => [
            'tgl obc', 'order date', 'tgl order', 'tanggal order', 'tgl_obc', 'order_date', 'tanggal obc', 'tgl masuk',
        ],
        'delivery_deadline' => [
            'tgl jtempo', 'tgl jatuh tempo', 'delivery deadline', 'deadline', 'tgl_jtempo', 'delivery_date', 'tgl kirim', 'tgl tempo',
        ],
        'tgl_pakai_bb' => [
            'tgl_pakai_bb', 'tgl pakai bb', 'tanggal pakai bb', 'tgl pakai',
        ],
        'tgl_cetak' => [
            'tgl_cetak', 'tgl cetak', 'tanggal cetak',
        ],
        'tgl_verifikasi' => [
            'tgl_verifikasi', 'tgl verifikasi', 'tanggal verifikasi',
        ],
        'packaging_date' => [
            'tanggal kemas', 'tgl kemas', 'packaging date', 'packaging_date', 'tgl packaging',
        ],
        'target_quantity' => [
            'qty pesan', 'target qty', 'target kemas', 'target quantity', 'target_quantity', 'target', 'qty', 'jumlah pesan', 'qty order',
        ],
        'rencet_quantity' => [
            'rencet', 'rencana cetak', 'rencet quantity', 'rencet_quantity', 'qty rencet',
        ],
        'jml_bb' => [
            'jml_bb (lk)', 'jml_bb', 'jml bb', 'jumlah bb', 'jml_bb(lk)', 'jml bb (lk)',
        ],
        'jml_cd' => [
            'jlm_cd (lk)', 'jml_cd', 'jml cd', 'cadangan', 'inschiet', 'jml_cd (lk)', 'jlm_cd',
        ],
        'total_bb' => [
            'total_bb (lk)', 'total_bb', 'total bb', 'total_bb(lk)',
        ],
        'jml_cetak' => [
            'jml_cetak', 'jml cetak', 'jumlah cetak',
        ],
        'baik_verifikasi' => [
            'baik_verifikasi', 'baik verifikasi', 'hasil baik', 'baik',
        ],
        'rusak_verifikasi' => [
            'rusak_verifikasi', 'rusak verifikasi', 'afval', 'rusak',
        ],
        'hcts_baik' => [
            'hasil baik dijadikan hcts', 'hcts_baik', 'hcts baik', 'hcts',
        ],
        'total_hcts' => [
            'total_hcts', 'total hcts',
        ],
        'kemas_quantity' => [
            'kemas', 'kemas quantity', 'kemas_quantity', 'qty kemas', 'jumlah kemas',
        ],
        'kirim_quantity' => [
            'kirim', 'kirim quantity', 'kirim_quantity', 'qty kirim', 'jumlah kirim',
        ],
        'machine_number' => [
            'work center', 'no mesin', 'mesin', 'machine', 'machine number', 'machine_number', 'mesin cetak', 'workcenter',
        ],
        'product_name' => [
            'material', 'nama produk', 'deskripsi', 'nama barang', 'product name', 'product_name', 'item name', 'nama material',
        ],
        'product_code' => [
            'material code', 'kode barang', 'kode produk', 'product code', 'product_code', 'item code', 'kode material',
        ],
        'customer_name' => [
            'pelanggan', 'customer', 'customer name', 'nama pelanggan', 'customer_name', 'institusi', 'klien',
        ],
        'specification' => [
            'spesifikasi', 'spec', 'specification', 'spesifikasi teknis', 'spesifikasi cetak',
        ],
        'paper_type' => [
            'jenis kertas', 'paper type', 'paper_type', 'bahan kertas', 'bahan', 'jenis bahan',
        ],
        'unit' => [
            'satuan', 'unit', 'uom', 'satuan kemas',
        ],
    ];

    /**
     * Parse and inspect the spreadsheet for the preview modal (US-2.3).
     *
     * @return array{
     *     total_rows: int,
     *     valid_rows_count: int,
     *     invalid_rows_count: int,
     *     preview_rows: list<array<string, mixed>>,
     *     all_errors: list<string>
     * }
     */
    public function parsePreview(string $filePath, int $previewLimit = 20): array
    {
        $reader = $this->createReaderForFile($filePath);
        $reader->open($filePath);

        $totalRows = 0;
        $validRowsCount = 0;
        $invalidRowsCount = 0;
        $previewRows = [];
        $allErrors = [];

        $headerMap = null;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $rawCells = $this->extractRowCells($row);

                    // Row 1 is treated as header if it contains recognized string columns
                    if ($rowIndex === 1) {
                        $headerMap = $this->buildHeaderMap($rawCells);

                        continue;
                    }

                    // Skip completely empty lines
                    if ($this->isRowCompletelyEmpty($rawCells)) {
                        continue;
                    }

                    $totalRows++;
                    $parsed = $this->mapAndValidateRow($rawCells, $headerMap, $rowIndex);

                    if ($parsed['is_valid']) {
                        $validRowsCount++;
                    } else {
                        $invalidRowsCount++;
                        foreach ($parsed['errors'] as $error) {
                            $allErrors[] = $error;
                        }
                    }

                    if (count($previewRows) < $previewLimit) {
                        $previewRows[] = [
                            'row_index' => $rowIndex,
                            'po_number' => $parsed['data']['po_number'] ?? '-',
                            'no_obc' => $parsed['data']['no_obc'] ?? '-',
                            'product_name' => $parsed['data']['product_name'] ?? '-',
                            'customer_name' => $parsed['data']['customer_name'] ?? '-',
                            'machine_number' => $parsed['data']['machine_number'] ?? '-',
                            'target_quantity' => $parsed['data']['target_quantity'] ?? 0,
                            'order_date' => $parsed['data']['order_date'] ?? '-',
                            'is_valid' => $parsed['is_valid'],
                            'errors' => $parsed['errors'],
                        ];
                    }
                }
                // Only process the first worksheet of the workbook
                break;
            }
        } finally {
            $reader->close();
        }

        return [
            'total_rows' => $totalRows,
            'valid_rows_count' => $validRowsCount,
            'invalid_rows_count' => $invalidRowsCount,
            'preview_rows' => $previewRows,
            'all_errors' => $allErrors,
        ];
    }

    /**
     * Import spreadsheet records with transaction safety and upsert logic (US-2.2).
     *
     * @return array{
     *     success: bool,
     *     imported: int,
     *     updated: int,
     *     skipped: int,
     *     total_rows: int,
     *     errors: list<string>,
     *     log_id: int|null
     * }
     */
    public function import(
        string $filePath,
        string $originalFileName,
        User $user,
        bool $skipInvalid = true,
        ?string $storedRelativePath = null
    ): array {
        $reader = $this->createReaderForFile($filePath);
        $reader->open($filePath);

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $totalRows = 0;
        $errors = [];

        $headerMap = null;

        DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $rawCells = $this->extractRowCells($row);

                    if ($rowIndex === 1) {
                        $headerMap = $this->buildHeaderMap($rawCells);

                        continue;
                    }

                    if ($this->isRowCompletelyEmpty($rawCells)) {
                        continue;
                    }

                    $totalRows++;
                    $parsed = $this->mapAndValidateRow($rawCells, $headerMap, $rowIndex);

                    if (! $parsed['is_valid']) {
                        foreach ($parsed['errors'] as $error) {
                            $errors[] = $error;
                        }

                        if ($skipInvalid) {
                            $skipped++;

                            continue;
                        }

                        // If not skipping invalid rows, rollback immediately
                        throw new Exception("Impor dibatalkan karena terdapat baris data yang tidak valid pada baris {$rowIndex}.");
                    }

                    $data = $parsed['data'];

                    $order = ProductionOrder::updateOrCreate(
                        ['po_number' => $data['po_number']],
                        [
                            'no_obc' => $data['no_obc'] ?? null,
                            'perso_type' => $data['perso_type'] ?? null,
                            'seri' => $data['seri'] ?? null,
                            'product_code' => $data['product_code'] ?? null,
                            'product_name' => $data['product_name'],
                            'customer_name' => $data['customer_name'] ?? null,
                            'specification' => $data['specification'] ?? null,
                            'paper_type' => $data['paper_type'] ?? null,
                            'machine_number' => $data['machine_number'] ?? null,
                            'order_date' => $data['order_date'] ?? now()->toDateString(),
                            'delivery_deadline' => $data['delivery_deadline'] ?? null,
                            'tgl_pakai_bb' => $data['tgl_pakai_bb'] ?? null,
                            'tgl_cetak' => $data['tgl_cetak'] ?? null,
                            'tgl_verifikasi' => $data['tgl_verifikasi'] ?? null,
                            'packaging_date' => $data['packaging_date'] ?? null,
                            'target_quantity' => $data['target_quantity'] ?? 0,
                            'rencet_quantity' => $data['rencet_quantity'] ?? 0,
                            'jml_bb' => $data['jml_bb'] ?? 0,
                            'jml_cd' => $data['jml_cd'] ?? 0,
                            'total_bb' => $data['total_bb'] ?? 0,
                            'jml_cetak' => $data['jml_cetak'] ?? 0,
                            'baik_verifikasi' => $data['baik_verifikasi'] ?? 0,
                            'rusak_verifikasi' => $data['rusak_verifikasi'] ?? 0,
                            'hcts_baik' => $data['hcts_baik'] ?? 0,
                            'total_hcts' => $data['total_hcts'] ?? 0,
                            'kemas_quantity' => $data['kemas_quantity'] ?? 0,
                            'kirim_quantity' => $data['kirim_quantity'] ?? 0,
                            'unit' => $data['unit'] ?? 'lembar',
                            'status' => ProductionOrderStatus::READY,
                            'created_by' => $user->id,
                        ]
                    );

                    if ($order->wasRecentlyCreated) {
                        $imported++;
                    } else {
                        $updated++;
                    }
                }
                break;
            }

            $reader->close();

            $status = match (true) {
                empty($errors) && ($imported + $updated) > 0 => 'success',
                ! empty($errors) && ($imported + $updated) > 0 => 'partial',
                default => 'failed',
            };

            $log = SapImportLog::create([
                'user_id' => $user->id,
                'file_name' => $originalFileName,
                'file_path' => $storedRelativePath ?? $filePath,
                'total_rows' => $totalRows,
                'imported_count' => $imported,
                'updated_count' => $updated,
                'failed_count' => count($errors),
                'status' => $status,
                'error_log' => $errors,
            ]);

            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total_rows' => $totalRows,
                'errors' => $errors,
                'log_id' => $log->id,
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            $reader->close();

            throw $e;
        }
    }

    /**
     * Map raw row cells into structured attributes and perform field-level validation.
     *
     * @param  list<mixed>  $cells
     * @param  array<string, int>|null  $headerMap
     * @return array{
     *     is_valid: bool,
     *     errors: list<string>,
     *     data: array<string, mixed>
     * }
     */
    public function mapAndValidateRow(array $cells, ?array $headerMap, int $rowIndex): array
    {
        $errors = [];
        $data = [];

        if (! empty($headerMap)) {
            // Header-driven positional extraction
            foreach ($this->headerAliases as $key => $aliases) {
                if (isset($headerMap[$key]) && isset($cells[$headerMap[$key]])) {
                    $data[$key] = $cells[$headerMap[$key]];
                }
            }
        } else {
            // Positional fallback matching standard 24 SAP columns
            $data = $this->mapPositionalFallback($cells);
        }

        // 1. Sanitize PO Number
        $poNumber = trim((string) ($data['po_number'] ?? ''));
        if ($poNumber === '') {
            $errors[] = "Baris {$rowIndex}: Nomor PO tidak boleh kosong.";
        }
        $data['po_number'] = $poNumber;

        // 2. Sanitize Product Name & Product Code
        $productName = trim((string) ($data['product_name'] ?? ''));
        $productCode = trim((string) ($data['product_code'] ?? ''));

        if ($productName === '' && $productCode !== '') {
            $productName = $productCode;
        } elseif ($productCode === '' && $productName !== '') {
            $productCode = $productName;
        }

        if ($productName === '') {
            $errors[] = "Baris {$rowIndex}: Nama Produk / Material tidak boleh kosong.";
        }

        $data['product_name'] = $productName;
        $data['product_code'] = $productCode !== '' ? $productCode : null;

        // 3. Sanitize and Validate Quantities
        $rawTargetQty = $data['target_quantity'] ?? 0;
        $targetQty = $this->normalizeInteger($rawTargetQty);
        if ($targetQty < 0) {
            $errors[] = "Baris {$rowIndex}: Target Qty (Jumlah Pesan) harus berupa angka positif.";
        }
        $data['target_quantity'] = $targetQty;

        $data['rencet_quantity'] = $this->normalizeInteger($data['rencet_quantity'] ?? 0);
        $data['jml_bb'] = $this->normalizeInteger($data['jml_bb'] ?? 0);
        $data['jml_cd'] = $this->normalizeInteger($data['jml_cd'] ?? 0);
        $data['total_bb'] = $this->normalizeInteger($data['total_bb'] ?? 0);
        $data['jml_cetak'] = $this->normalizeInteger($data['jml_cetak'] ?? 0);
        $data['baik_verifikasi'] = $this->normalizeInteger($data['baik_verifikasi'] ?? 0);
        $data['rusak_verifikasi'] = $this->normalizeInteger($data['rusak_verifikasi'] ?? 0);
        $data['hcts_baik'] = $this->normalizeInteger($data['hcts_baik'] ?? 0);
        $data['total_hcts'] = $this->normalizeInteger($data['total_hcts'] ?? 0);
        $data['kemas_quantity'] = $this->normalizeInteger($data['kemas_quantity'] ?? 0);
        $data['kirim_quantity'] = $this->normalizeInteger($data['kirim_quantity'] ?? 0);

        // 4. Sanitize Dates
        $data['order_date'] = $this->normalizeDate($data['order_date'] ?? null) ?? now()->toDateString();
        $data['delivery_deadline'] = $this->normalizeDate($data['delivery_deadline'] ?? null);
        $data['tgl_pakai_bb'] = $this->normalizeDate($data['tgl_pakai_bb'] ?? null);
        $data['tgl_cetak'] = $this->normalizeDate($data['tgl_cetak'] ?? null);
        $data['tgl_verifikasi'] = $this->normalizeDate($data['tgl_verifikasi'] ?? null);
        $data['packaging_date'] = $this->normalizeDate($data['packaging_date'] ?? null);

        // 5. Sanitize Text Attributes
        $data['no_obc'] = filled($data['no_obc'] ?? null) ? trim((string) $data['no_obc']) : null;
        $data['perso_type'] = filled($data['perso_type'] ?? null) ? trim((string) $data['perso_type']) : null;
        $data['seri'] = filled($data['seri'] ?? null) ? trim((string) $data['seri']) : null;
        $data['customer_name'] = filled($data['customer_name'] ?? null) ? trim((string) $data['customer_name']) : null;
        $data['specification'] = filled($data['specification'] ?? null) ? trim((string) $data['specification']) : null;
        $data['paper_type'] = filled($data['paper_type'] ?? null) ? trim((string) $data['paper_type']) : null;
        $data['machine_number'] = filled($data['machine_number'] ?? null) ? trim((string) $data['machine_number']) : null;
        $data['unit'] = filled($data['unit'] ?? null) ? trim((string) $data['unit']) : 'lembar';

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
        ];
    }

    /**
     * Map positional columns based on standard 24 SAP header layout.
     *
     * @param  list<mixed>  $cells
     * @return array<string, mixed>
     */
    protected function mapPositionalFallback(array $cells): array
    {
        return [
            'po_number' => $cells[0] ?? null,
            'no_obc' => $cells[1] ?? null,
            'perso_type' => $cells[2] ?? null,
            'seri' => $cells[3] ?? null,
            'order_date' => $cells[4] ?? null,
            'delivery_deadline' => $cells[5] ?? null,
            'tgl_pakai_bb' => $cells[6] ?? null,
            'tgl_cetak' => $cells[7] ?? null,
            'tgl_verifikasi' => $cells[8] ?? null,
            'packaging_date' => $cells[9] ?? null,
            'target_quantity' => $cells[10] ?? null,
            'rencet_quantity' => $cells[11] ?? null,
            'jml_bb' => $cells[12] ?? null,
            'jml_cd' => $cells[13] ?? null,
            'total_bb' => $cells[14] ?? null,
            'jml_cetak' => $cells[15] ?? null,
            'baik_verifikasi' => $cells[16] ?? null,
            'rusak_verifikasi' => $cells[17] ?? null,
            'hcts_baik' => $cells[18] ?? null,
            'total_hcts' => $cells[19] ?? null,
            'kemas_quantity' => $cells[20] ?? null,
            'kirim_quantity' => $cells[21] ?? null,
            'machine_number' => $cells[22] ?? null,
            'product_name' => $cells[23] ?? null,
            'product_code' => $cells[23] ?? null,
        ];
    }

    /**
     * Build header index map from raw header row.
     *
     * @param  list<mixed>  $headerCells
     * @return array<string, int>
     */
    public function buildHeaderMap(array $headerCells): array
    {
        $map = [];

        foreach ($headerCells as $index => $cellValue) {
            $normalized = $this->normalizeHeaderString((string) $cellValue);
            if ($normalized === '') {
                continue;
            }

            foreach ($this->headerAliases as $canonicalKey => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$canonicalKey] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Normalize header string for fuzzy matching (lowercase, stripped punctuation, trimmed spaces).
     */
    protected function normalizeHeaderString(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = str_replace(['_', '-', '.', '/', '(', ')'], ' ', $header);

        return preg_replace('/\s+/', ' ', $header) ?? '';
    }

    /**
     * Convert various numerical representations from SAP exports into standard integer.
     */
    public function normalizeInteger(mixed $value): int
    {
        if ($value === null || $value === '' || $value === '-') {
            return 0;
        }

        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        $str = trim((string) $value);
        if ($str === '-' || $str === '') {
            return 0;
        }

        // If string contains Indonesian thousand separators (e.g., "2.068" or "12.500")
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $str)) {
            $str = str_replace('.', '', $str);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+$/', $str)) {
            $str = str_replace(',', '', $str);
        } else {
            // Remove any non-digit characters except minus
            $str = preg_replace('/[^\d-]/', '', $str) ?? '0';
        }

        $intVal = (int) $str;

        return max(0, $intVal);
    }

    /**
     * Convert various date formats from Excel/SAP into standard YYYY-MM-DD.
     */
    public function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        // Numeric Excel timestamp serial number
        if (is_numeric($value) && (int) $value > 20000 && (int) $value < 60000) {
            $days = (int) $value;
            $timestamp = ($days - 25569) * 86400;

            return gmdate('Y-m-d', $timestamp);
        }

        $str = trim((string) $value);
        if ($str === '' || $str === '-') {
            return null;
        }

        // Match m/d/Y or d/m/Y or Y-m-d
        $formats = [
            'm/d/Y',
            'n/j/Y',
            'd/m/Y',
            'j/n/Y',
            'Y-m-d',
            'Y/m/d',
            'd-m-Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $str);
                if ($date !== null) {
                    return $date->format('Y-m-d');
                }
            } catch (Throwable) {
                // Continue to next format
            }
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Create appropriate OpenSpout Reader instance based on file extension and delimiter.
     */
    protected function createReaderForFile(string $filePath): CsvReader|XlsxReader
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            $delimiter = $this->detectCsvDelimiter($filePath);
            $options = new CsvOptions(FIELD_DELIMITER: $delimiter);

            return new CsvReader($options);
        }

        // Default to XLSX reader for .xlsx and other spreadsheet files
        return new XlsxReader;
    }

    /**
     * Detect CSV field delimiter (comma vs semicolon vs tab).
     */
    protected function detectCsvDelimiter(string $filePath): string
    {
        $firstLine = '';
        $handle = @fopen($filePath, 'r');
        if ($handle) {
            $firstLine = (string) fgets($handle, 4096);
            fclose($handle);
        }

        $delimiters = [';', ',', "\t", '|'];
        $bestDelimiter = ',';
        $maxCount = 0;

        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        return $bestDelimiter;
    }

    /**
     * Extract cell values from OpenSpout Row instance into array of scalar values.
     *
     * @return list<mixed>
     */
    protected function extractRowCells(Row $row): array
    {
        return array_values($row->toArray());
    }

    /**
     * Determine if a row has no non-empty values.
     *
     * @param  list<mixed>  $cells
     */
    protected function isRowCompletelyEmpty(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate standard sample template XLSX file for supervisor download.
     */
    public function generateSampleXlsx(string $destinationPath): void
    {
        $writer = new XlsxWriter;
        $writer->openToFile($destinationPath);

        // Header Row (Standard 24 SAP ERP Columns)
        $headers = [
            'NO PO',
            'No OBC',
            'Perso / non Perso',
            'SERI',
            'TGL OBC',
            'TGL JTempo',
            'TGL_PAKAI_BB',
            'TGL_CETAK',
            'TGL_VERIFIKASI',
            'Tanggal Kemas',
            'QTY PESAN',
            'RENCET',
            'JML_BB (LK)',
            'JLM_CD (LK)',
            'TOTAL_BB (LK)',
            'JML_CETAK',
            'BAIK_VERIFIKASI',
            'RUSAK_VERIFIKASI',
            'HASIL BAIK DIJADIKAN HCTS',
            'TOTAL_HCTS',
            'KEMAS',
            'KIRIM',
            'WORK CENTER',
            'Material',
        ];

        $writer->addRow(Row::fromValues($headers));

        // Sample Data Rows matching shop-floor master records
        $sampleRows = [
            [
                '3000189509',
                'ACE211474',
                'P',
                '1',
                '12/18/2023',
                '1/10/2024',
                '1/6/2024',
                '1/9/2024',
                '1/10/2024',
                '1/11/2024',
                2000,
                2068,
                0,
                2068,
                2068,
                2068,
                2037,
                31,
                37,
                68,
                2000,
                0,
                'HDR-1001',
                '931110001-24',
            ],
            [
                '3000189510',
                'ACE211475',
                'NP',
                '2',
                '12/19/2023',
                '1/12/2024',
                '1/7/2024',
                '1/10/2024',
                '1/11/2024',
                '1/12/2024',
                5000,
                5150,
                5000,
                150,
                5150,
                5150,
                5130,
                20,
                0,
                20,
                5000,
                5000,
                'MESIN-02',
                'Sertifikat Sekuriti A4',
            ],
        ];

        foreach ($sampleRows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
    }

    /**
     * Export a collection or query of Production Orders into an Excel spreadsheet.
     *
     * @param  iterable<ProductionOrder>  $orders
     */
    public function exportOrdersToXlsx(iterable $orders, string $destinationPath): void
    {
        $writer = new XlsxWriter;
        $writer->openToFile($destinationPath);

        // Standard 24 SAP ERP Columns + Status & Satuan
        $headers = [
            'NO PO',
            'No OBC',
            'Perso / non Perso',
            'SERI',
            'TGL OBC',
            'TGL JTempo',
            'TGL_PAKAI_BB',
            'TGL_CETAK',
            'TGL_VERIFIKASI',
            'Tanggal Kemas',
            'QTY PESAN',
            'RENCET',
            'JML_BB (LK)',
            'JLM_CD (LK)',
            'TOTAL_BB (LK)',
            'JML_CETAK',
            'BAIK_VERIFIKASI',
            'RUSAK_VERIFIKASI',
            'HASIL BAIK DIJADIKAN HCTS',
            'TOTAL_HCTS',
            'KEMAS',
            'KIRIM',
            'WORK CENTER',
            'Material',
            'Pelanggan',
            'Spesifikasi',
            'Jenis Kertas',
            'Satuan',
            'Status Order',
        ];

        $writer->addRow(Row::fromValues($headers));

        foreach ($orders as $order) {
            $writer->addRow(Row::fromValues([
                $order->po_number,
                $order->no_obc ?? '',
                $order->perso_type ?? '',
                $order->seri ?? '',
                $order->order_date->format('Y-m-d'),
                $order->delivery_deadline ? $order->delivery_deadline->format('Y-m-d') : '',
                $order->tgl_pakai_bb ? $order->tgl_pakai_bb->format('Y-m-d') : '',
                $order->tgl_cetak ? $order->tgl_cetak->format('Y-m-d') : '',
                $order->tgl_verifikasi ? $order->tgl_verifikasi->format('Y-m-d') : '',
                $order->packaging_date ? $order->packaging_date->format('Y-m-d') : '',
                $order->target_quantity,
                $order->rencet_quantity,
                $order->jml_bb,
                $order->jml_cd,
                $order->total_bb,
                $order->jml_cetak,
                $order->baik_verifikasi,
                $order->rusak_verifikasi,
                $order->hcts_baik,
                $order->total_hcts,
                $order->kemas_quantity,
                $order->kirim_quantity,
                $order->machine_number ?? '',
                $order->product_name,
                $order->customer_name ?? '',
                $order->specification ?? '',
                $order->paper_type ?? '',
                $order->unit,
                $order->status->label(),
            ]));
        }

        $writer->close();
    }
}
