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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'pegawai'])->default('pegawai');
            $table->string('email')->unique();
            $table->string('nim', 100)->nullable()->unique();
            $table->string('nama');
            $table->string('prodi')->nullable();
            $table->string('startup')->nullable();
            $table->longText('foto_base64')->nullable();
            $table->longText('face_embedding')->nullable();
            $table->timestamp('face_embedding_updated')->nullable();
            $table->longText('advanced_features')->nullable();
            $table->longText('facial_geometry')->nullable();
            $table->longText('feature_vector')->nullable();
            $table->string('password');
            $table->string('google_authenticator_secret')->nullable();
            $table->string('password_reset_token')->nullable();
            $table->dateTime('password_reset_expires')->nullable();
            $table->date('work_start_date')->nullable();
            $table->date('employment_start_date')->nullable();
            
            // Laravel defaults (might be useful)
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps(); // created_at and updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
