<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'invitation_id',     
        'event_name',
        'event_date',
        'sector',           
        'is_validated',
        'validated_at',
        'validated_by',     
        'validation_ip',    
        'redeemed_ip'       
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
    public function invitationRedemption()
    {
        return $this->belongsTo(InvitationRedemption::class, 'invitation_id', 'invitation_id');
    }
}
