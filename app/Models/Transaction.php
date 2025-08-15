<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'payment_status',
        'total_price',
        'amount_paid',
        'change',
        'invoice_number',
        'due_date',
        'user_id',
        'customer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    protected static function booted()
    {
        static::deleting(function ($transaction) {
            $transaction->transactionDetails()->each(function ($detail) {
                $detail->delete();
            });
        });
    }
}