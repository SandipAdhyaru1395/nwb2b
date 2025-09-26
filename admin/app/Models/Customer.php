<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;

    protected $table = 'customers';

    protected $fillable = [
        'email',
        'password',
        'company_name',
        'name',
        'phone',
        'vat_number',
        'business_reg_number',
        'approved_at',
        'approved_by',
        'credit_balance',
        'email_verified_at',
        'is_active',
        'remember_token',
        'last_login',
        'address_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'credit_balance' => 'decimal:2',
        'password' => 'hashed',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the default address for the customer.
     */
    public function defaultAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get all addresses for the customer.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}


