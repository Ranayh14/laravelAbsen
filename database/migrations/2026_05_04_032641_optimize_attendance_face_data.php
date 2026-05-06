<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Optimasi database:
     *  - Hapus screenshot longText (base64 gambar) dari tabel attendance
     *  - Ganti dengan landmark JSON 68 titik wajah (~1-2KB vs 50-100KB)
     *  - Hapus kolom redundan di users (tidak dipakai)
     *  - Tambah database index untuk query yang sering
     */
    public function up(): void
    {
        // ── TABEL attendance ──────────────────────────────────────────────
        Schema::table('attendance', function (Blueprint $table) {
            // Hapus kolom screenshot (penyebab DB membengkak - base64 image longText)
            if (Schema::hasColumn('attendance', 'screenshot_masuk')) {
                $table->dropColumn('screenshot_masuk');
            }
            if (Schema::hasColumn('attendance', 'screenshot_pulang')) {
                $table->dropColumn('screenshot_pulang');
            }

            // Tambah kolom landmark (68 titik {x,y} JSON ~1-2KB vs screenshot ~50-100KB)
            if (!Schema::hasColumn('attendance', 'landmark_masuk')) {
                $table->text('landmark_masuk')->nullable()->comment('JSON 68 titik landmark wajah saat absen masuk');
            }
            if (!Schema::hasColumn('attendance', 'landmark_pulang')) {
                $table->text('landmark_pulang')->nullable()->comment('JSON 68 titik landmark wajah saat absen pulang');
            }

            // Tambah indeks untuk query yang paling sering dipakai
            try {
                $table->index(['user_id', 'jam_masuk_iso'], 'idx_attendance_user_date');
            } catch (\Exception $e) { /* Index sudah ada */ }
            try {
                $table->index('jam_masuk_iso', 'idx_attendance_date');
            } catch (\Exception $e) { /* Index sudah ada */ }
        });

        // ── TABEL users ───────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom redundan (tidak dipakai, tapi makan space di setiap row)
            $redundantCols = ['advanced_features', 'facial_geometry', 'feature_vector'];
            foreach ($redundantCols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }

            // Tambah kolom landmark referensi wajah (68 titik untuk keperluan identitas)
            if (!Schema::hasColumn('users', 'face_landmarks')) {
                $table->text('face_landmarks')->nullable()->comment('JSON 68 titik landmark wajah referensi (dari foto profil)');
            }

            // Tambah indeks role untuk filter query admin
            try {
                $table->index('role', 'idx_users_role');
            } catch (\Exception $e) { /* Index sudah ada */ }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance', 'screenshot_masuk')) {
                $table->longText('screenshot_masuk')->nullable();
            }
            if (!Schema::hasColumn('attendance', 'screenshot_pulang')) {
                $table->longText('screenshot_pulang')->nullable();
            }
            if (Schema::hasColumn('attendance', 'landmark_masuk')) {
                $table->dropColumn('landmark_masuk');
            }
            if (Schema::hasColumn('attendance', 'landmark_pulang')) {
                $table->dropColumn('landmark_pulang');
            }
            try { $table->dropIndex('idx_attendance_user_date'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_attendance_date'); } catch (\Exception $e) {}
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'advanced_features')) {
                $table->longText('advanced_features')->nullable();
            }
            if (!Schema::hasColumn('users', 'facial_geometry')) {
                $table->longText('facial_geometry')->nullable();
            }
            if (!Schema::hasColumn('users', 'feature_vector')) {
                $table->longText('feature_vector')->nullable();
            }
            if (Schema::hasColumn('users', 'face_landmarks')) {
                $table->dropColumn('face_landmarks');
            }
            try { $table->dropIndex('idx_users_role'); } catch (\Exception $e) {}
        });
    }
};
