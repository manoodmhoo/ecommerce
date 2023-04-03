<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Events\OrderCreated;

class OrderController extends Controller
{
    //
    public function checkout(Request $request, $cartKey) {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'telephone' => 'required',
            'tax_number' => 'nullable',
            'invoice_address' => 'required',
            'shipping_address' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'errors' => [
                    'message' => $validator->errors()
                 ]
            ], 422);
        }

        if(auth('sanctum')->check()) {
            if(empty($cartKey)) {
                return response()->json([
                    'errors' => 'cart key is required.'
                ], 422);
            }

            $summaryPrice = 0;
            $amount = 0;

            $shoppingCart = Cart::where('key', $cartKey)->first();
            if($shoppingCart != null) {
                $cartItems = CartItem::where('cart_id', $shoppingCart->id)->get();
                if($cartItems != null) {
                    foreach($cartItems as $cartItem) {
                        $product = Product::find($cartItem->product_id)->first();
                        $product_price = ($cartItem->qty * $product->price);
                        $amount += $cartItem->qty;
                        $summaryPrice += $product_price;

                        // update stock
                        if($product->stock >= $amount) {
                            $product->stock = ($product->stock - $amount);
                            $product->save();
                        }
                    }
                }


                if($amount > 0 && $summaryPrice > 0) {
                    $shoppingCart->user_id = auth('sanctum')->user()->id;
                    $shoppingCart->save();

                    $findOrder = Order::where('cart_id', $shoppingCart->id)->first();
                    if($findOrder === null) {
                        $transactionId = Str::uuid()->toString();
                        $order = Order::create([
                            'summary_price' => $summaryPrice,
                            'transaction_id' => $transactionId,
                            'cart_id' => $shoppingCart->id,
                            'name' => $request->name,
                            'email' => $request->email,
                            'telephone' => $request->telephone,
                            'tax_number' => isset($request->tax_number) ? $request->tax_number : null,
                            'invoice_address' => $request->invoice_address,
                            'shipping_address' => $request->shipping_address,
                            'user_id' => auth('sanctum')->user()->id
                        ]);
                        $order->save();
                        event(new OrderCreated($order));

                        return response()->json([
                            'message' => 'order check out successfully.',
                        ], 200);
                    } else {
                            return response()->json([
                                'errors' => 'This order is checked out.'
                            ], 422);
                    }
                }
            } else {
                return response()->json([
                    'errors' => [
                        'message' => 'cart not found.'
                     ]
                ], 422);
            }


        } else {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }
    }
}
