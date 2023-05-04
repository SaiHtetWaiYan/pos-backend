<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function order(Request $request){


        $order = Order::create([
            'user_id' => $request->user_id,
            'invoice_no' => $request->invoice_no,
            'subtotal' => $request->subtotal,
            'discount' => $request->discount,
            'total' => $request->total,
            'payment_type' => $request->payment,
        ]);

        foreach($request->products as $item){
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['id'];
            $orderItem->product_name = $item['name'];
            $orderItem->product_photo = $item['photo'];
            $orderItem->variant = $item['variant'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->save();

            $product = Product::find($item['id']);
            $new_quantity = $product->current_stock - $item['quantity'];
            $product->update(['current_stock' => $new_quantity]);

        }

        return response()->json(['message'=>'Order successfully created'],200);
    }

    public function orderHistory(Request $request)
    {
        $user_id = Auth::user()->id;

        $orders = Order::with('orderItems')
            ->orderBy('id', 'DESC')
            ->where('user_id', $user_id)
            ->where('invoice_no', 'LIKE', '%' . $request->search . '%')
            ->when($request->date, function ($query, $date) {
                $start = date('Y-m-d', strtotime($date[0]));
                $query->whereDate('created_at', $start);
                if ($date[1]) {
                    $end = date('Y-m-d', strtotime($date[1]));
                    $query->whereBetween('created_at', [$start, $end]);
                }
            })
            ->when($request->discount, function ($query, $discount) {
                if ($discount === "discount") {
                    $query->whereNot('discount' ,'=', 0);
                }
                if($discount === "no discount"){
                    $query->where('discount' ,'=', 0);
                }
            })
            ->when($request->payment, function ($query, $payment) {
                $query->where('payment_type', $payment);
            })
            ->paginate($request->perpage);

        return response()->json(['orders' => $orders], 200);
    }
}
