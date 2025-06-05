<?php
namespace Modules\PiutangReport\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Register Event
**/
class PiutangReportEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        // ...
    }
}