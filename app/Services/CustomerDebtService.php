<?php

namespace App\Services;

use App\Models\CustomerDebt;

class CustomerDebtService
{
    public function createDebt($data)
    {
        // Logika untuk membuat utang pelanggan baru
        $customerDebt = new CustomerDebt();
        $customerDebt->customer_id = $data['customer_id'];
        $customerDebt->order_id = $data['order_id'];
        $customerDebt->amount_due = $data['amount_due'];
        $customerDebt->amount_paid = $data['amount_paid'];
        $customerDebt->remaining_debt = $data['remaining_debt'];
        $customerDebt->due_date = $data['due_date'];
        $customerDebt->author = $data['author'];
        $customerDebt->save();
    }

    public function updateDebt($id, $data)
    {
        // Logika untuk mengupdate utang pelanggan, termasuk menghitung bunga utang, mengirim notifikasi, dll.
        $customerDebt = CustomerDebt::find($id);
        $customerDebt->updateDebt($data);
    }

    // Method-method lainnya
}