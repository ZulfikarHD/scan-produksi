<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('docs/BAB_III_Draft.md contains synchronized Chen ERD with academic primary keys', function () {
    $bab3 = File::get(base_path('docs/BAB_III_Draft.md'));

    // Chen ERD must exist and highlight academic tablename_id (PK)
    expect($bab3)->toContain('user_id (PK)');
    expect($bab3)->toContain('production_order_id (PK)');
    expect($bab3)->toContain('daily_report_id (PK)');
    expect($bab3)->toContain('daily_report_item_id (PK)');
    expect($bab3)->toContain('sap_import_log_id (PK)');

    // Chen ERD must contain entity subgraphs
    expect($bab3)->toContain('subgraph USR_GRP ["Entitas User"]');
    expect($bab3)->toContain('subgraph PO_GRP ["Entitas Production Order"]');
    expect($bab3)->toContain('subgraph DR_GRP ["Entitas Daily Report"]');
    expect($bab3)->toContain('subgraph DRI_GRP ["Entitas Daily Report Item"]');
    expect($bab3)->toContain('subgraph LOG_GRP ["Entitas SAP Import Log"]');

    // Chen ERD must define relational diamonds
    expect($bab3)->toContain('R_IMP{"mengimpor"}');
    expect($bab3)->toContain('R_CATAT{"mencatat"}');
    expect($bab3)->toContain('R_VERIF{"memverifikasi"}');
    expect($bab3)->toContain('R_MUAT{"memuat"}');
    expect($bab3)->toContain('R_ACUAN{"menjadi acuan"}');
    expect($bab3)->toContain('R_LOG{"merekam log"}');
});

test('docs/BAB_III_Draft.md contains synchronized LRS class diagram with bounded types and clean keys', function () {
    $bab3 = File::get(base_path('docs/BAB_III_Draft.md'));

    // LRS class diagram section
    expect($bab3)->toContain('#### 4. Logical Record Structure (LRS)');
    expect($bab3)->toContain('class USERS {');
    expect($bab3)->toContain('*user_id (PK) : BIGINT');
    expect($bab3)->toContain('nip : VARCHAR(30) [UK]');
    expect($bab3)->toContain('username : VARCHAR(50) [UK]');

    expect($bab3)->toContain('class PRODUCTION_ORDERS {');
    expect($bab3)->toContain('*production_order_id (PK) : BIGINT');
    expect($bab3)->toContain('po_number : VARCHAR(50) [UK]');
    expect($bab3)->toContain('machine_number : VARCHAR(50)');
    expect($bab3)->toContain('specification : TEXT');
    expect($bab3)->toContain('unit : VARCHAR(30)');
    expect($bab3)->toContain('status : VARCHAR(30)');
    expect($bab3)->toContain('#created_by (FK) : BIGINT');

    expect($bab3)->toContain('class DAILY_REPORTS {');
    expect($bab3)->toContain('*daily_report_id (PK) : BIGINT');
    expect($bab3)->toContain('report_code : VARCHAR(50) [UK]');
    expect($bab3)->toContain('total_reject_quantity : INT');
    expect($bab3)->toContain('#user_id (FK) : BIGINT');
    expect($bab3)->toContain('#supervisor_id (FK) : BIGINT');

    expect($bab3)->toContain('class DAILY_REPORT_ITEMS {');
    expect($bab3)->toContain('*daily_report_item_id (PK) : BIGINT');
    expect($bab3)->toContain('#daily_report_id (FK) : BIGINT');
    expect($bab3)->toContain('#production_order_id (FK) : BIGINT');
    expect($bab3)->toContain('reject_quantity : INT');
    expect($bab3)->toContain('notes : TEXT');

    expect($bab3)->toContain('class SAP_IMPORT_LOGS {');
    expect($bab3)->toContain('*sap_import_log_id (PK) : BIGINT');
    expect($bab3)->toContain('file_path : VARCHAR(255)');
    expect($bab3)->toContain('error_log : JSON');
    expect($bab3)->toContain('#user_id (FK) : BIGINT');
});

test('docs/BAB_III_Draft.md contains all five file specification tables matching physical schema', function () {
    $bab3 = File::get(base_path('docs/BAB_III_Draft.md'));

    // Tables 3.10 through 3.14 headers
    expect($bab3)->toContain('*Tabel 3.10: Spesifikasi File Tabel `users`*');
    expect($bab3)->toContain('*Tabel 3.11: Spesifikasi File Tabel `production_orders`*');
    expect($bab3)->toContain('*Tabel 3.12: Spesifikasi File Tabel `daily_reports`*');
    expect($bab3)->toContain('*Tabel 3.13: Spesifikasi File Tabel `daily_report_items`*');
    expect($bab3)->toContain('*Tabel 3.14: Spesifikasi File Tabel `sap_import_logs`*');

    // Expected columns for users (Tabel 3.10)
    $userCols = ['user_id', 'name', 'nip', 'username', 'email', 'email_verified_at', 'password', 'remember_token', 'role', 'is_active', 'last_login_at', 'created_at', 'updated_at'];
    foreach ($userCols as $col) {
        expect($bab3)->toContain("`{$col}`");
        expect(Schema::hasColumn('users', $col))->toBeTrue("Column users.{$col} must exist physically.");
    }

    // Expected columns for production_orders (Tabel 3.11)
    $orderCols = [
        'production_order_id', 'po_number', 'no_obc', 'perso_type', 'seri', 'product_code', 'product_name',
        'customer_name', 'specification', 'paper_type', 'machine_number', 'order_date', 'delivery_deadline',
        'tgl_pakai_bb', 'tgl_cetak', 'tgl_verifikasi', 'packaging_date', 'target_quantity', 'rencet_quantity',
        'jml_bb', 'jml_cd', 'total_bb', 'jml_cetak', 'baik_verifikasi', 'rusak_verifikasi', 'hcts_baik',
        'total_hcts', 'kemas_quantity', 'kirim_quantity', 'unit', 'status', 'created_by', 'created_at', 'updated_at',
    ];
    foreach ($orderCols as $col) {
        expect($bab3)->toContain("`{$col}`");
        expect(Schema::hasColumn('production_orders', $col))->toBeTrue("Column production_orders.{$col} must exist physically.");
    }

    // Expected columns for daily_reports (Tabel 3.12)
    $reportCols = [
        'daily_report_id', 'report_code', 'product_series', 'report_date', 'shift', 'user_id',
        'supervisor_id', 'verified_at', 'total_items_count', 'total_good_quantity', 'total_reject_quantity',
        'notes', 'status', 'created_at', 'updated_at',
    ];
    foreach ($reportCols as $col) {
        expect($bab3)->toContain("`{$col}`");
        expect(Schema::hasColumn('daily_reports', $col))->toBeTrue("Column daily_reports.{$col} must exist physically.");
    }

    // Expected columns for daily_report_items (Tabel 3.13)
    $itemCols = [
        'daily_report_item_id', 'daily_report_id', 'production_order_id', 'po_number', 'product_name',
        'specification', 'machine_number', 'target_quantity', 'good_quantity', 'reject_quantity',
        'unit', 'notes', 'scanned_at', 'created_at', 'updated_at',
    ];
    foreach ($itemCols as $col) {
        expect($bab3)->toContain("`{$col}`");
        expect(Schema::hasColumn('daily_report_items', $col))->toBeTrue("Column daily_report_items.{$col} must exist physically.");
    }

    // Expected columns for sap_import_logs (Tabel 3.14)
    $logCols = [
        'sap_import_log_id', 'user_id', 'file_name', 'file_path', 'total_rows',
        'imported_count', 'updated_count', 'failed_count', 'status', 'error_log',
        'created_at', 'updated_at',
    ];
    foreach ($logCols as $col) {
        expect($bab3)->toContain("`{$col}`");
        expect(Schema::hasColumn('sap_import_logs', $col))->toBeTrue("Column sap_import_logs.{$col} must exist physically.");
    }
});

test('docs/BAB_III_Draft.md adheres to industrial-paper-writing academic tone without prohibited AI cliches', function () {
    $bab3 = File::get(base_path('docs/BAB_III_Draft.md'));

    // Extract database design section
    $erdPos = strpos($bab3, '#### 3. Entity Relationship Diagram (ERD)');
    $implPos = strpos($bab3, '### C. Implementasi');
    expect($erdPos)->not->toBeFalse();
    expect($implPos)->not->toBeFalse();

    $dbSection = substr($bab3, $erdPos, $implPos - $erdPos);

    // Prohibited AI slop / clichés
    $bannedPhrases = [
        'bukan sekadar',
        'bukan hanya',
        'mengukir tonggak',
        'lompatan paradigma',
        'menyelami lebih dalam',
        'data yang absolut',
        'artefak dokumentasi',
        'pengkodean',
        'penulisan baris kode',
        'masukan scanner fisik',
        'penyelia laporan',
        'tolok ukur',
        'data agregat',
    ];

    foreach ($bannedPhrases as $banned) {
        expect(stripos($dbSection, $banned))->toBeFalse("Prohibited phrase '{$banned}' found in Bab III database design section.");
    }

    // Grounded operational terms must be present
    expect($dbSection)->toContain('Nomor Pegawai');
    expect($dbSection)->toContain('Unit Pengemasan');
    expect($dbSection)->toContain('Kartu Mesin Produksi');
    expect($dbSection)->toContain('Code 128');
    expect($dbSection)->toContain('Supervisor');
});
