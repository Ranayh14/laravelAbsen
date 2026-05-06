<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('report_date');
            $table->text('content')->nullable();
            $table->string('status')->default('pending');
            $table->text('evaluation')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'report_date'], 'uniq_user_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
