<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Notifications\EventParticipationNotification;
use App\Events\EventUpdated;
use DB;

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


    public function participate(Event $event)
    {
        try {
            Log::info('Processing participation request', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'seats_available' => $event->seats_available,
                'user_id' => Auth::id()
            ]);

            $user = Auth::user();

            // Early returns for validation checks
            if (!$user) {
                return $this->error('User not authenticated.', 401);
            }

            if ($event->seats_available <= 0) {
                return $this->error('No available seats for this event.', 400);
            }


            // in case the user click on participated before it disabled cuz no time to perfect the app :(
            if ($event->participants()->where('user_id', $user->id)->exists()) {
                return $this->error('You are already participating in this event.', 400);
            }

            // to ensure data consistency
            DB::beginTransaction();
            try {

                $event->participants()->attach($user->id, [
                    'participated_at' => now(),
                ]);


                $event->update([
                    'booked_seats' => DB::raw('booked_seats + 1'),
                    'seats_available' => DB::raw('seats_available - 1')
                ]);

                // Refresh the event to get updated counts
                $event->refresh();

                // Send notification outside transaction
                DB::commit();

                // Dispatch notification to creators
                if ($event->creator) {
                    $event->creator->notify(new EventParticipationNotification($event, $user));
                }

                Log::info('Participation successful', [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'remaining_seats' => $event->seats_available
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully joined the event.',
                    'data' => [
                        'seats_available' => $event->seats_available,
                        'booked_seats' => $event->booked_seats
                    ]
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to process participation', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error('Failed to process your participation request. Please try again.', 500);
        }
    }

    // front-end starter's orders
    private function error(string $message, int $statusCode)
    {
        return response()->json([
            'success' => false,
            'errors' => [$message]
        ], $statusCode);
    }


    public function afterCreateOne($event, $request)
    {
        try {

            Log::info("Event created: " . $event->id);
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

            // call the parent method to handle validation and update
            return parent::updateOne($id, $request);
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.updateOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterUpdateOne($event)
    {
        try {
            Log::info("Event updated: " . $event->id);
            // brodcasting
            broadcast(new EventUpdated($event))->toOthers();

            Log::info("Updates broadcasted to participant: " . $event->id);
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
            $event = $model::findOrFail($id);
            $event->delete();
            return response()->json(['success' => true, 'message' => __('common.success_deleted')]);
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.deleteOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}
