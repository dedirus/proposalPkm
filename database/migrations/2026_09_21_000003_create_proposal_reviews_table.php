<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->integer('halaman')->nullable();
            $table->text('catatan_review');
            $table->enum('status_bagian', ['perlu_perbaikan', 'oke'])->default('perlu_perbaikan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_reviews');
    }
};
