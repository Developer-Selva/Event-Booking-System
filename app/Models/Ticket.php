<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'price', 'quantity', 'available_quantity', 'event_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable($requestedQuantity)
    {
        return $this->available_quantity >= $requestedQuantity;
    }

    public function decreaseAvailability($quantity)
    {
        $this->decrement('available_quantity', $quantity);
    }

    public function increaseAvailability($quantity)
    {
        $this->increment('available_quantity', $quantity);
    }
}