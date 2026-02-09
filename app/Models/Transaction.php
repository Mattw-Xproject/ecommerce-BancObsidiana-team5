<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'account_id',
        'merchant_name',
        'reference',
        'type',
        'amount',
        'fee',        // Nueva columna
        'currency',
        'status',
        'response_message'
    ];

    // Relación inversa
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    // Helper para ver el total descontado (Monto + Comisión)
    public function getTotalAttribute()
    {
        return $this->amount + $this->fee;
    }
}
