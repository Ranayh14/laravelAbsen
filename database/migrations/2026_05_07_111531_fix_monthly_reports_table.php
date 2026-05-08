<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_reports', function (Blueprint $table) {
            // Tambahkan kolom content jika belum ada
            if (!Schema::hasColumn('monthly_reports', 'content')) {
                $table->text('content')->nullable()->after('month');
            }
        });

        // Update ENUM status menggunakan DB statement karena Laravel Blueprint terbatas untuk modifikasi ENUM yang sudah ada
        DB::statement("ALTER TABLE monthly_reports MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'disapproved', 'belum di approve') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('monthly_reports', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_reports', 'content')) {
                $table->dropColumn('content');
            }
        });
        
        DB::statement("ALTER TABLE monthly_reports MODIFY COLUMN status ENUM('draft', 'belum di approve', 'approved', 'disapproved') DEFAULT 'draft'");
    }
};
