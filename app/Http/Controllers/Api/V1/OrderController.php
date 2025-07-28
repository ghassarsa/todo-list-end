<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index() {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('plan')->get();

        return response()->json($orders);
    }

    public function store(Request $request) {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $plan = Plan::findOrFail($request->plan_id);
        $currentPlan = Plan::find($user->plan_id);
        if ($plan->id == $user->plan_id) {
            return response()->json(['massage' => 'You are already on this plan']);
        }

        if ($currentPlan && $plan->task_limit < $currentPlan->task_limit) {
            return response()->json(['massage' => 'You cannot downgrade your plan']);
        }

        $order = Order::where('user_id', $user->id)->where('plan_id', $plan->id)->first();

        if ($order) {
            return response()->json(['massage' => 'You already have an order for this plan'], 403);
        }

        $newOrder = Order::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'amount' => $plan->price,
        ]);

        return response()->json([
            'massage' => 'Order created successfully',
            'order' => $newOrder,
        ], 201);
    }

    public function show(string $id) {
        $order = Order::with('plan')->findOrFail($id);
        $invoice = Invoice::where('order_id', $order->id)->first();

        $data = [$order];

        if ($invoice) {
            $data['invoice'] = $invoice;
        }

        return response()->json($data);
    }

    public function destroy(string $id) {
        $user = Auth::user();
        $order = Order::findOrFail($id);

        if ($user->id != $order->user_id) {
            return response()->json(['massage' => 'You are not authorized to use this action'], 403);
        }

        $order->delete();

        return response()->json([
            'massage' => 'Order deleted successfully',
        ]);
    }
}
