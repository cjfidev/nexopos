<?php

/**
 * Service Management Controller
 * @since 1.0
 * @package modules/ServiceManagement
**/

namespace Modules\ServiceManagement\Http\Controllers;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use Modules\ServiceManagement\Crud\ServiceCrud;
use Modules\ServiceManagement\Models\Service;


class ServiceManagementController extends Controller
{
    /**
     * Main Page
     * @since 1.0
    **/
    public function index()
    {
        return $this->view( 'ServiceManagement::index' );
    }

    public function serviceList()
    {
         return ServiceCrud::table();
    }

    public function createService()
    {
         return ServiceCrud::form();
    }

    public function editService($id)
    {
        $service = Service::find($id);
        if (!$service) {
            abort(404, 'Service not found');
        }

        return ServiceCrud::form($service);
    }

    public function getServices()
    {
        $services = Service::select('id', 'service_name', 'service_price')->get();
        return response()->json($services);
    }
}
