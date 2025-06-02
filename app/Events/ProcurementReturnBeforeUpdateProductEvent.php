<?php

namespace App\Events;

use App\Models\ProcurementReturnProduct;
use Illuminate\Queue\SerializesModels;

class ProcurementReturnBeforeUpdateProductEvent
{
    use SerializesModels;

    public function __construct( public ProcurementReturnProduct $return_product, public $fields )
    {
        // ...
    }
}
