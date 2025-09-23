<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'email',
        'password',
        'company_name',
        'contact_person',
        'phone',
        'vat_number',
        'business_reg_number',
        'address_line1',
        'address_line2',
        'city',
        'postal_code',
        'country',
        'approved_at',
        'approved_by',
        'credit_balance',
        'email_verified_at',
        'is_active',
        'remember_token',
        'last_login',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'credit_balance' => 'decimal:2',
    ];
}


