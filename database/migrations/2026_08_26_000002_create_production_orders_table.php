<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();

            // Identitas Pokok Order & Barcode SAP (Code 128)
            $table->string('po_number')->unique(); // NO PO (Primary Barcode Lookup)
            $table->string('no_obc')->nullable()->index(); // No OBC
            $table->string('perso_type', 50)->nullable(); // Perso / non Perso
            $table->string('seri', 50)->nullable(); // SERI
            $table->string('product_code')->nullable()->index(); // Bagian dari Material SAP
            $table->string('product_name')->index(); // Material / Nama Produk
            $table->string('customer_name')->nullable()->index(); // Pelanggan / Institusi
            $table->text('specification')->nullable(); // Spesifikasi teknis kertas & sekuriti
            $table->string('paper_type')->nullable(); // Jenis bahan baku kertas
            $table->string('machine_number')->nullable()->index(); // WORK CENTER / Mesin

            // Timeline & Tanggal Siklus Produksi SAP
            $table->date('order_date'); // TGL OBC
            $table->date('delivery_deadline')->nullable(); // TGL JTempo
            $table->date('tgl_pakai_bb')->nullable(); // TGL_PAKAI_BB
            $table->date('tgl_cetak')->nullable(); // TGL_CETAK
            $table->date('tgl_verifikasi')->nullable(); // TGL_VERIFIKASI
            $table->date('packaging_date')->nullable(); // Tanggal Kemas

            // Kuantitas & Akumulasi Lembar Kerja SAP
            $table->unsignedInteger('target_quantity')->default(0); // QTY PESAN (Target Order)
            $table->unsignedInteger('rencet_quantity')->default(0); // RENCET (Rencana Cetak)
            $table->unsignedInteger('jml_bb')->default(0); // JML_BB (LK) - Lembar Kerja Bahan Baku
            $table->unsignedInteger('jml_cd')->default(0); // JLM_CD (LK) - Cadangan / Inschiet
            $table->unsignedInteger('total_bb')->default(0); // TOTAL_BB (LK) = JML_BB + JLM_CD
            $table->unsignedInteger('jml_cetak')->default(0); // JML_CETAK
            $table->unsignedInteger('baik_verifikasi')->default(0); // BAIK_VERIFIKASI
            $table->unsignedInteger('rusak_verifikasi')->default(0); // RUSAK_VERIFIKASI (Afval)
            $table->unsignedInteger('hcts_baik')->default(0); // HASIL BAIK DIJADIKAN HCTS
            $table->unsignedInteger('total_hcts')->default(0); // TOTAL_HCTS
            $table->unsignedInteger('kemas_quantity')->default(0); // KEMAS
            $table->unsignedInteger('kirim_quantity')->default(0); // KIRIM

            // Status, Satuan & Audit Trail
            $table->string('unit', 30)->default('lembar'); // Satuan kemas (lembar/rim/buku)
            $table->string('status', 30)->default('ready')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Composite indexes untuk akselerasi query Master Data Explorer & Dashboard
            $table->index(['status', 'order_date']);
            $table->index(['machine_number', 'status']);
            $table->index(['no_obc', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
