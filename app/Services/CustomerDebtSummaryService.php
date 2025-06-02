<?php

namespace App\Services;

use App\Models\CustomerDebtSummary;
use Illuminate\Support\Facades\DB;

class CustomerDebtSummaryService
{
    // public function updateOrCreateSummary($customerId, $amountToAdd)
    // {
    //     return CustomerDebtSummary::updateOrCreate(
    //         ['customer_id' => $customerId],
    //         [
    //             'total_debt' => \DB::raw("COALESCE(total_debt, 0) + $amountToAdd"),
    //             'author' => auth()->id()
    //         ]
    //     );
    // }

    public function reduceDebt($customerId, $amountToReduce)
    {
        return DB::transaction(function() use ($customerId, $amountToReduce) {
            $summary = CustomerDebtSummary::where('customer_id', $customerId)
                ->lockForUpdate()
                ->first();
            
            if (!$summary) {
                throw new \Exception("Customer debt summary not found");
            }
            
            if ($amountToReduce > $summary->total_debt) {
                throw new \Exception("Reduction amount exceeds total debt");
            }
            
            $summary->total_debt = max(0, $summary->total_debt - $amountToReduce);
            $summary->author = auth()->id();
            $summary->save();
            
            return $summary;
        });
    }
}