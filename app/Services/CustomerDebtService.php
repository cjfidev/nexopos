<?php

namespace App\Services;

use App\Models\CustomerDebt;
use App\Models\CustomerDebtSummary;
use Illuminate\Support\Facades\DB;

class CustomerDebtService
{
    public function createDebt(array $data): CustomerDebt
    {
        return DB::transaction(function () use ($data) {
            $debt = CustomerDebt::create($data);
            $this->recalculateCustomerDebtSummary($data['customer_id']);
            return $debt;
        });
    }

    public function recordPayment(int $debtId, float $amount): CustomerDebt
    {
        return DB::transaction(function () use ($debtId, $amount) {
            $debt = CustomerDebt::findOrFail($debtId);
            
            $debt->update([
                'amount_paid' => $debt->amount_paid + $amount,
                'remaining_debt' => max(0, $debt->remaining_debt - $amount)
            ]);
            
            $this->recalculateCustomerDebtSummary($debt->customer_id);
            return $debt;
        });
    }

    public function recalculateCustomerDebtSummary(int $customerId): CustomerDebtSummary
    {
        $totalDebt = CustomerDebt::where('customer_id', $customerId)
            ->sum('remaining_debt');
            
        return CustomerDebtSummary::updateOrCreate(
            ['customer_id' => $customerId],
            ['total_debt' => $totalDebt, 'author' => auth()->id()]
        );
    }
}