<?php
namespace Modules\ServiceManagement;

use Illuminate\Support\Facades\Event;
use App\Services\Module;

class ServiceManagementModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );
    }
}