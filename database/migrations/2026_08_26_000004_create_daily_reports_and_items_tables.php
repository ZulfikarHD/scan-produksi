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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            // unique() already creates a B-Tree index; do not chain ->index() (US-6.2)
            $table->string('report_code', 50)->unique();
            $table->string('product_series', 30)->index();
            $table->date('report_date')->index();
            $table->string('shift', 30)->default('shift_1')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_items_count')->default(0);
            $table->unsignedInteger('total_good_quantity')->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamps();

            // Memastikan hanya ada satu sesi laporan per seri produk, tanggal, dan shift
            $table->unique(['product_series', 'report_date', 'shift']);
            $table->index(['status', 'report_date']);
        });

        Schema::create('daily_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained('production_orders')->nullOnDelete();
            $table->string('po_number', 50)->index();
            $table->string('product_name');
            $table->text('specification')->nullable();
            $table->string('machine_number', 50)->nullable()->index();
            $table->unsignedInteger('target_quantity')->default(0);
            $table->unsignedInteger('good_quantity')->default(0);
            $table->string('unit', 30)->default('lembar');
            // Match parent daily_reports.notes (TEXT) to avoid VARCHAR(255) truncation (US-6.2)
            $table->text('notes')->nullable();
            $table->dateTime('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['daily_report_id', 'po_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_items');
        Schema::dropIfExists('daily_reports');
    }
};
