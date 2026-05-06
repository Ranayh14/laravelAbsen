<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('jam_masuk', 20)->nullable();
            $table->dateTime('jam_masuk_iso')->nullable();
            $table->string('ekspresi_masuk', 50)->nullable();
            $table->longText('screenshot_masuk')->nullable();
            $table->string('lokasi_masuk')->nullable();
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('lng_masuk', 10, 7)->nullable();
            
            $table->string('jam_pulang', 20)->nullable();
            $table->dateTime('jam_pulang_iso')->nullable();
            $table->string('ekspresi_pulang', 50)->nullable();
            $table->longText('screenshot_pulang')->nullable();
            $table->string('lokasi_pulang')->nullable();
            $table->decimal('lat_pulang', 10, 7)->nullable();
            $table->decimal('lng_pulang', 10, 7)->nullable();
            
            $table->string('status')->default('ontime');
            $table->string('ket')->default('wfo');
            
            $table->text('alasan_wfa')->nullable();
            $table->text('alasan_overtime')->nullable();
            $table->string('lokasi_overtime')->nullable();
            $table->text('alasan_izin_sakit')->nullable();
            $table->longText('bukti_izin_sakit')->nullable();
            $table->text('alasan_pulang_awal')->nullable();
            
            $table->integer('daily_report_id')->nullable();
            $table->boolean('is_overtime')->default(0);
            $table->decimal('overtime_bonus', 5, 2)->default(0.00);
            
            $table->timestamps(); // automatically creates created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
