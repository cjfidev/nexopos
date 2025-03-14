<?php

namespace Modules\ProcurementReturn\Database\Migrations;

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
        if (!Schema::hasTable('procurements_returns_details')) {
            Schema::create('procurements_returns_details', function (Blueprint $table) {
                $table->id(); // Primary keys                
                $table->foreignId('procurement_return_id')->constrained('procurements_returns')->onDelete('cascade');
                $table->foreignId('procurement_product_id')->constrained('nexopos_procurements_products')->onDelete('cascade');
                $table->integer('return_quantity'); // Kuantitas yang dikirim
                $table->decimal('return_amount', 15, 2); // Harga total dari barang yang dikirim
                $table->tinyInteger('status')->default(0);
                $table->text('notes')->nullable();
                $table->string('author');
                $table->timestamps();
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