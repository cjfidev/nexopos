<?php

/**
 * The Procurement Return Controller
 * @since 1.0
 * @package modules/CustomerDebtPayment
**/

namespace Modules\CustomerDebtPayment\Http\Controllers;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use Modules\CustomerDebtPayment\Crud\DebtPaymentCrud;
use App\Models\CustomerDebtPayment;

class CustomerDebtPaymentController extends Controller
{
    /**
     * Main Page
     * @since 1.0
    **/
    public function index()
    {
        return $this->view( 'CustomerDebtPayment::index' );
    }

    public function debtPaymentList()
    {
         return DebtPaymentCrud::table();
    }

    public function createDebtPayment()
    {
         return DebtPaymentCrud::form();
    }


    public function editDebtPayment($id)
    {
        $debts = CustomerDebtPayment::find($id);
        if (!$debts) {
            abort(404, 'debts not found');
        }

        return DebtPaymentCrud::form($debts);
    }

    public function getDebtPayment()
    {
        $debts = CustomerDebtPayment::select('id')->get();
        return response()->json($debts);
    }
    
}
