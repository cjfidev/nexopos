<?php

namespace App\Services;

use App\Models\CustomerDebtPayment;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerDebt;
use App\Models\CustomerDebtSummary;
use App\Exceptions\NotAllowedException;

class CustomerDebtPaymentService
{
    public function processPayment($customerId, $paymentAmount)
    {
        return DB::transaction(function() use ($customerId, $paymentAmount) {
            // Lock dan dapatkan summary hutang
            $summary = CustomerDebtSummary::where('customer_id', $customerId)
                ->lockForUpdate()
                ->first();
            
            if (!$summary) {
                throw new NotAllowedException(__('Customer has no debt record.'));
            }
            
            if ($paymentAmount <= 0) {
                throw new NotAllowedException(__('Payment amount must be positive.'));
            }
            
            // if ($paymentAmount > $summary->total_debt) {
            //     throw new NotAllowedException(__('Payment exceeds total debt.'));
            // }
            
            // Dapatkan semua hutang yang belum lunas (status 0)
            $debts = CustomerDebt::where('customer_id', $customerId)
                ->where('status', 0)
                ->orderBy('due_date', 'asc') // Bayar yang paling tua dulu
                ->lockForUpdate()
                ->get();

            $paymentAllocation = [];
            $remainingPayment = $paymentAmount;

            foreach ($debts as $debt) {
                if ($remainingPayment <= 0) break;
                
                $amountToPay = min($remainingPayment, $debt->remaining_debt);
                $remainingBefore = $debt->remaining_debt;
                
                $debt->amount_paid += $amountToPay;
                $debt->remaining_debt -= $amountToPay;
                
                if ($debt->remaining_debt == 0) {
                    $debt->status = 1;
                    $debt->paid_date = now();
                }
                
                $debt->save();
                
                $paymentAllocation[] = [
                    'debt_id' => $debt->id,
                    'amount_allocated' => $amountToPay,
                    'remaining_before' => $remainingBefore,
                    'remaining_after' => $debt->remaining_debt,
                    'order_id' => $debt->order_id
                ];
                
                $remainingPayment -= $amountToPay;
            }
            
            return [
                'summary' => $summary,                
                'remaining_payment' => $remainingPayment
            ];
        });
    }
}