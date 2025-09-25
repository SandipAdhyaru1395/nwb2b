<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    
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
        'status',

        'b_address_type',
        'b_country',
        'b_address_line1',
        'b_address_line2',
        'b_landmark',
        'b_city',
        'b_state',
        'b_zip_code',


        's_address_type',
        's_country',
        's_address_line1',
        's_address_line2',
        's_landmark',
        's_city',
        's_state',
        's_zip_code',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->orderBy('created_at');
    }
}
