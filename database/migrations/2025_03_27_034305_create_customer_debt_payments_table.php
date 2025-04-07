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
        Schema::create('nexopos_customers_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->integer( 'customer_id' );
            $table->string( 'name' )->nullable();
            $table->date( 'payment_date' )->nullable();
            $table->decimal( 'amount_paid', 15, 2 )->default( 0 );
            $table->decimal( 'total_debt', 15, 2 )->default( 0 );
            $table->json( 'payment_allocation' )->nullable();
            $table->integer( 'author' );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_customers_debt_payments');
    }
};
