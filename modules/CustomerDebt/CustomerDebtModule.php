<?php
namespace Modules\CustomerDebt;

use Illuminate\Support\Facades\Event;
use App\Services\Module;

class CustomerDebtModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );
    }
}