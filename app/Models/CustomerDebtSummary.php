<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDebtSummary extends Model
{
    protected $table = 'nexopos_customers_debt_summaries';
    
    protected $fillable = [
        'customer_id',
        'total_debt',
        'author'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}