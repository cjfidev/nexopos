<?php

namespace Modules\PiutangReport\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{ CustomerAccountHistory, User };
use Illuminate\Support\Facades\Validator;
use DB;

class PiutangReportController extends Controller
{
    public function index()
    {
        return view('PiutangReport::index');
    }

    /**
     * Get filtered piutang report data by created_at
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
{
    // Validasi input tanggal
    $validator = Validator::make($request->all(), [
        'startDate' => 'required|date',
        'endDate'   => 'required|date|after_or_equal:startDate',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'message' => 'Invalid date range.',
            'details' => $validator->errors()
        ], 422);
    }

    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // Step 1: Ambil entri 'add' terakhir dengan previous_amount NULL untuk setiap customer lengkap dengan amount-nya
    $lastAddEntries = CustomerAccountHistory::where('operation', 'add')
        ->select('customer_id', 'amount', DB::raw('MAX(created_at) as last_add_created_at'))
        ->groupBy('customer_id', 'amount')  // groupBy amount supaya ambil amount yg sesuai
        ->get()
        ->keyBy('customer_id'); // agar mudah akses per customer_id

    // Ambil hanya created_at per customer untuk filter step 2
    $lastAddMap = $lastAddEntries->mapWithKeys(function ($item) {
        return [$item->customer_id => $item->last_add_created_at];
    });

    // Step 2: Ambil semua data payment dan deduct setelah last add date per customer
    $data = CustomerAccountHistory::query()
        ->join('nexopos_users', 'nexopos_customers_account_history.customer_id', '=', 'nexopos_users.id')
        ->whereIn('nexopos_customers_account_history.operation', ['payment', 'deduct'])
        ->whereBetween('nexopos_customers_account_history.created_at', [$startDate, $endDate])
        ->where(function ($query) use ($lastAddMap) {
            foreach ($lastAddMap as $customerId => $lastAddDate) {
                $query->orWhere(function ($q) use ($customerId, $lastAddDate) {
                    $q->where('nexopos_customers_account_history.customer_id', $customerId)
                      ->where('nexopos_customers_account_history.created_at', '>', $lastAddDate);
                });
            }
        })
        ->groupBy('nexopos_customers_account_history.customer_id', 'nexopos_users.first_name', 'nexopos_users.last_name')
        ->select([
            'nexopos_customers_account_history.customer_id',
            DB::raw("CONCAT(ns_nexopos_users.first_name, ' ', ns_nexopos_users.last_name) as customer"),
            DB::raw('SUM(ns_nexopos_customers_account_history.amount) as total_credit'),
        ])
        ->get();

    // Mapping hasil, tambahkan credit dari entri 'add' terakhir jika ada
    $result = $data->map(function ($item) use ($lastAddEntries) {
        $lastAddAmount = 0;
        if (isset($lastAddEntries[$item->customer_id])) {
            $lastAddAmount = $lastAddEntries[$item->customer_id]->amount;
        }

        return [
            'customer' => $item->customer,
            'credit'   => $item->total_credit,
            'last_add_amount' => $lastAddAmount,
        ];
    });

    // Hitung total credit keseluruhan, gabungan payment/deduct + add terakhir
    $totalCredit = $result->sum('credit');

    return response()->json([
        'error' => false,
        'result' => $result,
        'summary' => [
            'total_credit' => $totalCredit,
        ],
    ]);
}
}
