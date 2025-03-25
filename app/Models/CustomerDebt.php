<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDebt extends Model
{
    protected $table = 'nexopos_customers_debts';

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount_due',
        'amount_paid',
        'remaining_debt',
        'due_date',
        'paid_date',
        'author',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\User::class, 'customer_id');
    }
}