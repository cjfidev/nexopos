<?php

namespace App\Events;

use App\Models\ProcurementReturn;
use Illuminate\Queue\SerializesModels;

class ProcurementReturnDeletionEvent
{
    use SerializesModels;

    public function __construct( public ProcurementReturn $procurement_return )
    {
        // ...
    }
}
