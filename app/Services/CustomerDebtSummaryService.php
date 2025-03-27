<?php

namespace App\Services;

use App\Models\CustomerDebtSummary;

class CustomerDebtSummaryService
{
    public function updateOrCreateSummary($customerId, $amountToAdd)
    {
        return CustomerDebtSummary::updateOrCreate(
            ['customer_id' => $customerId],
            [
                'total_debt' => \DB::raw("COALESCE(total_debt, 0) + $amountToAdd"),
                'author' => auth()->id()
            ]
        );
    }

    public function reduceDebt($customerId, $amountToReduce)
    {
        $summary = CustomerDebtSummary::where('customer_id', $customerId)->first();
        
        if ($summary) {
            $summary->total_debt = max(0, $summary->total_debt - $amountToReduce);
            $summary->author = auth()->id();
            $summary->save();
        }
        
        return $summary;
    }
}