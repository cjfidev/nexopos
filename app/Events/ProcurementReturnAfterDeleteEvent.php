<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class ProcurementReturnAfterDeleteEvent
{
    use SerializesModels;

    public function __construct( public $procurement_return_data )
    {
        // ...
    }
}
