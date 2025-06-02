<?php

namespace App\Events;

use App\Models\ProcurementReturnProduct;
use Illuminate\Queue\SerializesModels;

class ProcurementReturnBeforeDeleteProductEvent
{
    use SerializesModels;

    public function __construct( public ProcurementReturnProduct $return_product )
    {
        // ...
    }
}
