<?php

/**
 * The Procurement Return Controller
 * @since 1.0
 * @package modules/ProcurementReturn
**/

namespace Modules\ProcurementReturn\Http\Controllers;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use Modules\ProcurementReturn\Crud\ProcurementReturnCrud;
use Modules\ProcurementReturn\Models\ProcurementReturn;

class ProcurementBackupReturnController extends Controller
{
    /**
     * Main Page
     * @since 1.0
    **/
    public function index()
    {
        return $this->view( 'ProcurementReturn::index' );
    }

    public function returnList()
    {
         return ProcurementReturnCrud::table();
    }

    public function createReturn()
    {
         return ProcurementReturnCrud::form();
    }


    public function editReturn($id)
    {
        $return = ProcurementReturn::find($id);
        if (!$return) {
            abort(404, 'return not found');
        }

        return ProcurementReturnCrud::form($return);
    }

    public function getReturns()
    {
        $returns = ProcurementReturn::select('id')->get();
        return response()->json($returns);
    }
    
}
