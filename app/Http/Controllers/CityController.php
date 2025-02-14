<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\City;

class CityController extends Controller
{

    public function index(): JsonResponse
    {
        $cities = City::all();

        return response()->json(['success' => true, 'cities' => $cities]);
    }
}
