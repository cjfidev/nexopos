<?php
namespace Modules\CustomerDebtPayment;

use Illuminate\Support\Facades\Event;
use App\Services\Module;

class CustomerDebtPaymentModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );
    }
}