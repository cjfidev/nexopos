<?php
namespace Modules\ProcurementReturn;

use Illuminate\Support\Facades\Event;
use App\Services\Module;

class ProcurementReturnModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );
    }
}