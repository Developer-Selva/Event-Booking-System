<?php

namespace App\Repositories\Contracts;

interface EventRepositoryInterface
{
    public function getAllWithFilters($filters);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getEventsByOrganizer($organizerId);
}