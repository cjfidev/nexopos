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
        Schema::create('nexopos_procurements_returns_products', function (Blueprint $table) {
            $table->bigIncrements( 'id' );
            $table->string( 'name' );
            $table->float( 'gross_purchase_price', 18, 5 )->default( 0 );
            $table->float( 'net_purchase_price', 18, 5 )->default( 0 );
            $table->integer( 'procurement_return_id' );
            $table->integer( 'product_id' );
            $table->float( 'purchase_price', 18, 5 )->default( 0 );
            $table->float( 'quantity', 18, 5 );
            $table->float( 'available_quantity', 18, 5 );
            $table->integer( 'tax_group_id' )->nullable();
            $table->string( 'barcode' )->nullable();
            $table->datetime( 'expiration_date' )->nullable();
            $table->string( 'tax_type' ); // inclusive or exclusive;
            $table->float( 'tax_value', 18, 5 )->default( 0 );
            $table->float( 'total_purchase_price', 18, 5 )->default( 0 );
            $table->float( 'total_return_amount', 18, 5 )->default( 0 );
            $table->integer( 'unit_id' );
            $table->integer( 'convert_unit_id' )->nullable();
            $table->integer( 'author' );
            $table->string( 'uuid' )->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_procurements_returns_products');
    }
};
