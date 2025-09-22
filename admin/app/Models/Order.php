<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    
    protected $fillable = [
        'order_number',
        'order_date',
        'customer_id',
        'subtotal',
        'vat_amount',
        'total_amount',
        'wallet_credit_used',
        'units_count',
        'skus_count',
        'items_count',
        'payment_terms',
        'payment_status',
        'outstanding_amount',
        'estimated_delivery_date',
        'status'
    ];
}
