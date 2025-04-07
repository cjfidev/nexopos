<?php

/**
 * The Procurement Return Controller
 * @since 1.0
 * @package modules/CustomerDebt
**/

namespace Modules\CustomerDebt\Http\Controllers;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use Modules\CustomerDebt\Crud\DebtCrud;
use App\Models\CustomerDebt;
use App\Models\CustomerDebtSummary;

class CustomerDebtController extends Controller
{
    /**
     * Main Page
     * @since 1.0
    **/
    public function index()
    {
        return $this->view( 'CustomerDebt::index' );
    }

    public function debtList()
    {
         return DebtCrud::table();
    }

    public function createDebt()
    {
         return DebtCrud::form();
    }


    public function editDebt($id)
    {
        $debts = CustomerDebt::find($id);
        if (!$debts) {
            abort(404, 'debts not found');
        }

        return DebtCrud::form($debts);
    }

    public function getDebt()
    {
        $debts = CustomerDebt::select('id')->get();
        return response()->json($debts);
    }

    public function getDebtSummary($customerId)
    {
        // Menyaring berdasarkan customer_id
        $debtSummary = CustomerDebtSummary::where('customer_id', $customerId)->first();

        // Mengecek apakah data ditemukan
        if (!$debtSummary) {
            return response()->json(['error' => 'Debt summary not found for this customer'], 404);
        }

        // Mengembalikan data summary utang dalam bentuk JSON
        return response()->json($debtSummary);
    }
    
}
