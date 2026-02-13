<?php

namespace App\Repositories\Contracts;

interface BookingRepositoryInterface
{
    public function create(array $data);
    
    public function findById($id);
    
    public function getUserBookings($userId);
    
    public function updateStatus($id, $status);
    
    public function cancelBooking($id);
}