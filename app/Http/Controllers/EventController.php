<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'date', 'location']);
        $events = $this->eventRepository->getAllWithFilters($filters);
        
        return response()->json($events);
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'tickets']);
        
        return response()->json([
            'event' => $event,
            'tickets' => $event->tickets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:now',
            'location' => 'required|string|max:255',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $event = $this->eventRepository->create($data);

        return response()->json([
            'message' => 'Event created successfully.',
            'event' => $event->load('organizer')
        ], 201);
    }

    public function update(Request $request, Event $event)
    {
        // Check authorization
        if (Auth::id() !== $event->created_by && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date|after:now',
            'location' => 'sometimes|string|max:255',
        ]);

        $event = $this->eventRepository->update($event->id, $request->all());

        return response()->json([
            'message' => 'Event updated successfully.',
            'event' => $event->load('organizer')
        ]);
    }

    public function destroy(Event $event)
    {
        // Check authorization
        if (Auth::id() !== $event->created_by && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->eventRepository->delete($event->id);

        return response()->json([
            'message' => 'Event deleted successfully.'
        ]);
    }
}