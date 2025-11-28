<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'invitation_id',     // AGREGAR: estabas usando este campo
        'event_name',
        'event_date',
        'sector',           // AGREGAR: estabas usando este campo
        'is_validated',
        'validated_at',
        'validated_by',     // AGREGAR: para tracking
        'validation_ip',    // AGREGAR: para tracking
        'redeemed_ip'       // AGREGAR: para tracking
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime'
    ];

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
