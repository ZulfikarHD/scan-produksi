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
            $table->string('po_number')->unique();
            $table->string('product_code')->nullable()->index();
            $table->string('product_name')->index();
            $table->string('customer_name')->index();
            $table->text('specification')->nullable();
            $table->string('paper_type')->nullable();
            $table->string('machine_number')->nullable()->index();
            $table->unsignedInteger('target_quantity')->default(0);
            $table->string('unit', 30)->default('lembar');
            $table->date('order_date');
            $table->date('delivery_deadline')->nullable();
            $table->string('status', 30)->default('ready')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Composite indexes for rapid filtering on Master Data & Production dashboard
            $table->index(['status', 'order_date']);
            $table->index(['machine_number', 'status']);
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
