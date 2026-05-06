<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->text('summary')->nullable();
            $table->json('achievements')->nullable();
            $table->json('obstacles')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            
            $table->unique(['user_id', 'year', 'month'], 'uniq_user_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
