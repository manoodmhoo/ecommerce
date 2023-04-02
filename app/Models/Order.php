<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary_price',
        'name',
        'email',
        'telephone',
        'tax_number',
        'invoice_address',
        'shipping_address',
        'user_id',
        'transaction_id',
        'cart_id'
    ];

}
