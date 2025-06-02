<?php

namespace App\Events;

use App\Models\ProcurementReturn;
use Illuminate\Queue\SerializesModels;

class ProcurementReturnAfterDeleteProductEvent
{
    use SerializesModels;

    public function __construct( public $product_id, public ProcurementReturn $procurement_return )
    {
        // ...
    }
}
