<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class EventRepository implements EventRepositoryInterface
{
    public function getAllWithFilters($filters)
    {
        $cacheKey = 'events_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Event::with(['organizer', 'tickets']);
            
            if (!empty($filters['search'])) {
                $query->searchByTitle($filters['search']);
            }
            
            if (!empty($filters['date'])) {
                $query->filterByDate($filters['date']);
            }
            
            if (!empty($filters['location'])) {
                $query->where('location', 'LIKE', '%' . $filters['location'] . '%');
            }
            
            return $query->paginate(10);
        });
    }

    public function findById($id)
    {
        return Cache::remember('event_' . $id, 3600, function () use ($id) {
            return Event::with(['organizer', 'tickets'])->findOrFail($id);
        });
    }

    public function create(array $data)
    {
        $event = Event::create($data);
        Cache::forget('events_list');
        return $event;
    }

    public function update($id, array $data)
    {
        $event = $this->findById($id);
        $event->update($data);
        Cache::forget('event_' . $id);
        Cache::forget('events_list');
        return $event;
    }

    public function delete($id)
    {
        $event = $this->findById($id);
        Cache::forget('event_' . $id);
        Cache::forget('events_list');
        return $event->delete();
    }

    public function getEventsByOrganizer($organizerId)
    {
        return Event::where('created_by', $organizerId)
            ->with('tickets')
            ->paginate(10);
    }
}