<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationRedemption extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'invitation_id',
        'event_name',
        'event_date',
        'sector',
        'guest_count',
        'tickets_generated',
        'redeemed_at',
        'redeemed_ip',
        'user_agent',
        'metadata'

    ];
    protected $casts = [
        'redeemed_at' => 'datetime',
        'metadata' => 'array',
        'event_date' => 'datetime',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'invitation_id', 'invitation_id');
    }
}
