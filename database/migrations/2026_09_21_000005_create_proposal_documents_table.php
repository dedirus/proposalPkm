<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('file_path');
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();

            $table->index(['proposal_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_documents');
    }
};
