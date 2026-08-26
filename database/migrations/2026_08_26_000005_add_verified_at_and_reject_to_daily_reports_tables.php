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
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unsignedInteger('total_reject_quantity')->default(0)->after('total_good_quantity');
            $table->timestamp('verified_at')->nullable()->after('supervisor_id');
        });

        Schema::table('daily_report_items', function (Blueprint $table) {
            $table->unsignedInteger('reject_quantity')->default(0)->after('good_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_items', function (Blueprint $table) {
            $table->dropColumn('reject_quantity');
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn(['total_reject_quantity', 'verified_at']);
        });
    }
};
