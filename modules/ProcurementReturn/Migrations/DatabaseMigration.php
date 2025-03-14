<?php
/**
 * Table Migration
 * @package 5.3.3
**/

namespace Modules\ProcurementReturn\Migrations;

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
        if (!Schema::hasTable('procurements_returns')) {
            Schema::create('procurements_returns', function (Blueprint $table) {
                $table->id(); // Primary keys                
                $table->foreignId('procurement_id')->constrained('nexopos_procurements')->onDelete('cascade');
                $table->foreignId('provider_id')->constrained('nexopos_providers')->onDelete('cascade');
                $table->integer('total_quantity')->default(0);
                $table->decimal('total_return_amount', 15, 2)->default(0);
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
        Schema::dropIfExists('procurements_returns');
    }
};
