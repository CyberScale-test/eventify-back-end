<?php

namespace App\Http\Controllers;

use App\Enums\ROLE;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Log;

class UserController extends CrudController
{
    protected $table = 'users';

    protected $modelClass = User::class;

    protected function getTable()
    {
        return $this->table;
    }

    protected function getModelClass()
    {
        return $this->modelClass;
    }

    public function createOne(Request $request)
    {
        try {
            $request->merge(['password' => Hash::make($request->password)]);

            return parent::createOne($request);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.createOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterCreateOne($item, $request)
    {
        try {
            $roleEnum = ROLE::from($request->role);
            $item->syncRoles([$roleEnum]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.afterCreateOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            if (isset($request->password) && ! empty($request->password)) {
                $request->merge(['password' => Hash::make($request->password)]);
            } else {
                $request->request->remove('password');
            }

            return parent::updateOne($id, $request);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterUpdateOne($item, $request)
    {
        try {
            $roleEnum = ROLE::from($request->role);
            $item->syncRoles([$roleEnum]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.afterUpdateOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function getParticipatedEvents(User $user)
    {
        try {
            Log::info('Fetching events participated-in by user:', ['user_id' => $user->id]);

            // Fetch participated events with pagination and include the 'city' relationship
            $participatedEvents = $user->participatedEvents()->with('city')->paginate(request('perPage', 20));

            Log::info('Fetched participated events:', ['events' => $participatedEvents->pluck('id')->toArray()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $participatedEvents->items(), // Corrected line
                    'meta' => [
                        'currentPage' => $participatedEvents->currentPage(),
                        'lastPage' => $participatedEvents->lastPage(),
                        'totalItems' => $participatedEvents->total(),
                        'perPage' => $participatedEvents->perPage(), // Added perPage to match the other function
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching participated events:', [
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error while fetching participated events',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function getCreatedEventsByUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $events = $user->createdEvents()->paginate(request('perPage', 20)); // paginate results like in the front 

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $events->items(),
                    'meta' => [
                        'currentPage' => $events->currentPage(),
                        'lastPage' => $events->lastPage(),
                        'totalItems' => $events->total(),
                        'perPage' => $events->perPage(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching created events by user: ' . $e->getMessage());
            return response()->json(['success' => false, 'errors' => ['An error occurred while fetching events.']], 500);
        }
    }
}
