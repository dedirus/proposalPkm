<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposal_reviews', function (Blueprint $table) {
            $table->boolean('is_resolved')->default(false)->after('status_bagian');
            $table->text('catatan_mahasiswa')->nullable()->after('is_resolved');
        });
    }

    public function down(): void
    {
        Schema::table('proposal_reviews', function (Blueprint $table) {
            $table->dropColumn(['is_resolved', 'catatan_mahasiswa']);
        });
    }
};
