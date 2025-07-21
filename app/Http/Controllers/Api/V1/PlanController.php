<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function index(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|title',
            'description' => 'required|string|description',
            'price' => 'required|integer',
            'task_limit' => 'required|string|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json([
                'massage' => 'Invalid login details'
            ], 401);
        }

        Plan::create([
            'name' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'task_limit' => 2,
        ]);
    }
}
