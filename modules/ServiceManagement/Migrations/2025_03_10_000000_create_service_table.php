<?php

namespace Modules\ServiceManagement\Database\Migrations;

use App\Classes\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('service_managements')) {
            Schema::create('service_managements', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->string('service_name'); // Nama layanan
                $table->decimal('service_price', 10, 2); // Harga layanan, dengan 2 angka desimal
                $table->string('author'); // Nama penulis/pembuat layanan
                $table->timestamps(); // Kolom created_at dan updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('service_managements');
    }
};