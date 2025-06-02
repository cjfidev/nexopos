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
        Schema::create('nexopos_procurements_returns', function (Blueprint $table) {
            $table->id(); // Primary keys                
            $table->foreignId('procurement_id')->constrained('nexopos_procurements')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('nexopos_providers')->onDelete('cascade');
            $table->date('return_date')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_return_amount', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->text('notes')->nullable();
            $table->string('author');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_procurements_returns');
    }
};
