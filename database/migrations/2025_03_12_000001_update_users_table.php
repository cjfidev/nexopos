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
        Schema::table('nexopos_users', function (Blueprint $table) {
            // Tambahkan kolom baru
            $table->string('customer_no')->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nexopos_users', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback
            $table->dropColumn('customer_no');
        });
    }
};
