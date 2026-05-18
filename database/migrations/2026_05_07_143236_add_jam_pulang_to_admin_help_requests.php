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
            if (!Schema::hasColumn('admin_help_requests', 'jam_pulang')) {
                $table->time('jam_pulang')->nullable()->after('jam_masuk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_help_requests', function (Blueprint $table) {
            if (Schema::hasColumn('admin_help_requests', 'jam_pulang')) {
                $table->dropColumn('jam_pulang');
            }
        });
    }
};
