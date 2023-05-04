<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;

        $orders = Order::with('orderItems')
            ->where('user_id', $user_id)
            ->when($request->range, function ($query, $date) {
                $start = date('Y-m-d', strtotime($date['start']));
                $end = date('Y-m-d', strtotime($date['end']));
                if ($start == $end) {
                    $query->whereDate('created_at', $start);
                }
                else{
                    $query->whereBetween('created_at', [$start,$end]);
                }
            })
            ->get();

        $totalUnit = 0;
        $totalAmount = 0;
        $totalOrder = $orders->count();
        foreach ($orders as $order) {
            $quantity = $order->orderItems->sum('quantity');
            $amount = $order->total;
            $totalUnit += $quantity;
            $totalAmount += $amount;
        }

        $outOfStock = Product::where('user_id', $user_id)->where('current_stock',0)->get()->count();
        return response()->json(['order'=>$totalOrder,'unit' => $totalUnit , 'amount' => $totalAmount , 'stock' => $outOfStock ], 200);

    }
}
