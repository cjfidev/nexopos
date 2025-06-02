<?php

namespace App\Events;

use App\Models\ProcurementReturn;
use Illuminate\Queue\SerializesModels;

class ProcurementReturnDeliveryEvent
{
    use SerializesModels;

    public function __construct( public ProcurementReturn $procurement_return )
    {
        // ...
    }
}
