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
use App\Models\CustomerAccountHistory;
use DB;

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
        // Ambil entri 'add' terbaru sebagai dasar limit kredit
        $latestAddOperation = CustomerAccountHistory::where('customer_id', $customerId)
            ->where('operation', 'add')
            ->latest('created_at')
            ->first();

        if (!$latestAddOperation) {
            return response()->json(0);
        }

        // Limit kredit dari entri 'add' terbaru
        $limitCredit = $latestAddOperation->amount;

        // Ambil semua transaksi setelah entri 'add' terbaru
        $transactions = CustomerAccountHistory::where('customer_id', $customerId)
            ->where('created_at', '>', $latestAddOperation->created_at)
            ->whereIn('operation', ['payment', 'deduct'])
            ->get();

        // Hitung total pembayaran dan pengurangan manual
        $totalPaid = $transactions->sum('amount');

        // Hitung sisa piutang
        $totalDebt = $limitCredit - $totalPaid;

        return response()->json($totalPaid);
    }
    
}
