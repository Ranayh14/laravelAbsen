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
        Schema::table('attendance', function (Blueprint $table) {
            $table->mediumText('foto_masuk')->nullable()->after('ekspresi_masuk')->comment('Path to compressed evidence image for clock in');
            $table->mediumText('foto_pulang')->nullable()->after('ekspresi_pulang')->comment('Path to compressed evidence image for clock out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['foto_masuk', 'foto_pulang']);
        });
    }
};
