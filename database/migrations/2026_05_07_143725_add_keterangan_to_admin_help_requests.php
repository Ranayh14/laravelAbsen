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
        Schema::table('admin_help_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_help_requests', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('bug_proof');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_help_requests', function (Blueprint $table) {
            if (Schema::hasColumn('admin_help_requests', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
