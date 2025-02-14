<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventController extends CrudController

{

    protected $table = 'events';
    protected $modelClass = Event::class;

    protected function getTable()
    {
        return $this->table;
    }

    protected function getModelClass()
    {
        return   $this->modelClass;
    }

    public function getReadAllQuery(): builder
    {
        return (Event::query()->with('city'));
    }


    public function createOne(Request $request)
    {
        try {
            Log::info("Attempting to create a new event");

            // set it programatically
            $request->merge(['creator_id' => Auth::id()]);

            // the parent is doing most of the job
            $response = parent::createOne($request);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.createOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }


    public function index()
    {
        // get pagination parameters from the request.  Use defaults if not provided. check me later
        $perPage = request('perPage', 10); // default to 10 items per page
        $page = request('page', 1);       // default to page 1 


        $events = Event::paginate($perPage, ['*'], 'page', $page);


        // build the response in the format expected by frontend starter.
        return response()->json([
            'success' => true,
            'data' => [  //  wrap the events and meta in a 'data' object
                'items' => $events->items(),     // The actual event data for the current page
                'meta'  => [
                    'currentPage' => $events->currentPage(),
                    'lastPage'    => $events->lastPage(),
                    'totalItems'  => $events->total(),
                    'perPage'     => $events->perPage(),
                ],
            ],
        ]);
    }


    public function participate(Request $request, Event $event)
    {
        try {

            Log::info('Participate: Resolved event:', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'seats_available' => $event->seats_available,
            ]);

            // log incoming request data
            Log::info('Participate: Incoming request data:', $request->all());

            // check if the user is authenticated
            $user = Auth::user();
            if (!$user) {
                Log::error('Participate: No authenticated user found.');
                return response()->json([
                    'success' => false,
                    'errors' => ['User not authenticated.']
                ], 401);
            }

            // log authenticated user details
            Log::info('Participate: Authenticated user:', ['user_id' => $user->id]);

            // check if the event has available seats
            if ($event->seats_available <= 0) {
                Log::warning('Participate: No available seats for event:', ['event_id' => $event->id]);
                return response()->json([
                    'success' => false,
                    'errors' => ['No available seats.']
                ], 400);
            }

            // check if the user is already participating in the event
            if ($event->participants()->where('user_id', $user->id)->exists()) {
                Log::warning('Participate: User already participating in event:', [
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => ['You are already participating in this event.']
                ], 400);
            }

            // attach the user to the event
            $event->participants()->attach($user->id, [
                'participated_at' => now(),
            ]);

            // update seat counts
            $event->increment('booked_seats');
            $event->decrement('seats_available');

            Log::info('Participate: Seats updated for event:', [
                'event_id' => $event->id,
                'booked_seats' => $event->booked_seats,
                'seats_available' => $event->seats_available,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully participated.'
            ], 200);
        } catch (\Exception $e) {

            Log::error('Participate: Error during participation:', [
                'event_id' => $event->id,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'An error occurred while participating.'], 500);
        }
    }




    public function afterCreateOne($item, $request)
    {
        try {

            Log::info("Event created: " . $item->id);
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.afterCreateOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            Log::info("Attempting to update event with ID: $id");

            // remove the creator_id from the request to prevent modification check me later
            $request->request->remove('creator_id');

            // call the parent method to handle validation and update
            return parent::updateOne($id, $request);
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.updateOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterUpdateOne($item, $request)
    {
        try {
            Log::info("Event updated: " . $item->id);
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.afterUpdateOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    // try the child delete check later
    public function deleteOne($id, Request $request)
    {
        try {
            $model = $this->getModelClass();
            $item = $model::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => __('common.success_deleted')]);
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.deleteOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}
