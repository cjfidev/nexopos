<?php
namespace Modules\PiutangReport;

use Illuminate\Support\Facades\Event;
use App\Services\Module;

class PiutangReportModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );
    }
}