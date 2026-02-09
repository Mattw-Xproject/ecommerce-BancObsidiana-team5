<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $guarded = [];

    // ENCRIPTACIÓN AUTOMÁTICA
    protected $fillable = [
        'account_id',
        'card_number',
        'cvv',
        'expiration_date',
        'brand',
        'credit_limit',
        'status'
    ];
    // Al guardar, Laravel encripta. Al acceder ($card->card_number), desencripta.
    protected $casts = [
        //'card_number' => 'encrypted',
        'cvv' => 'encrypted',
        'expiration_date' => 'date',
    ];

    // Ocultar datos sensibles por defecto en respuestas JSON
    protected $hidden = [
        'card_number',
        'cvv',
        'created_at',
        'updated_at'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }

    // RELACIÓN: Una tarjeta tiene muchas transacciones
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Indicamos explícitamente que la llave foránea es card_id
        return $this->hasMany(Transaction::class, 'card_id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->transactions();
    }


}
