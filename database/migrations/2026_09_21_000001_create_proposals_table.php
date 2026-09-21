<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ketua_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('dosen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul_pkm');
            $table->string('skema_pkm');
            $table->string('file_path');
            $table->enum('status', ['diajukan', 'sedang_dibimbing', 'revisi', 'selesai'])->default('diajukan');
            $table->timestamps();

            // Index untuk optimasi pencarian proposal yang mencari pembimbing
            $table->index(['dosen_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
