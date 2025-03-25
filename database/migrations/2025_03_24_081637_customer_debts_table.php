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
        Schema::create('nexopos_customers_debts', function (Blueprint $table) {
            $table->id(); 
            $table->integer( 'customer_id' );
            $table->integer( 'order_id' );
            $table->decimal( 'amount_due', 15, 2 );
            $table->decimal( 'amount_paid', 15, 2 )->default( 0 );
            $table->decimal( 'remaining_debt', 15, 2 );
            $table->date( 'due_date' )->nullable();
            $table->date( 'paid_date' )->nullable();
            $table->integer( 'author' );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_customers_debts');
    }
};
