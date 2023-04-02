<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;

class CartController extends Controller
{
    //
    public function store()
    {

        if(auth('sanctum')->check()) {
            $userId = auth('sanctum')->user()->id;
        }

        $cart = Cart::create([
            'key' => Str::uuid()->toString(),
            'user_id' => isset($userId) ? $userId : null,
        ]);

        return response()->json([
            'message' => 'A new cart have been created.',
            'key' =>  $cart->key
        ], 200);
    }

    public function show($cartKey)
    {
        if(empty($cartKey)) {
            return response()->json([
                'errors' => 'cart key is required.'
            ], 422);
        }

        $shoppingCart = Cart::where('key', $cartKey)->first();
        if($shoppingCart != null) {
            $cartItems = CartItem::where('cart_id', $shoppingCart->id)->get();
        }

        return response()->json([
            'cart' =>  $shoppingCart,
            'items' =>  isset($cartItems) ? $cartItems : [],
        ], 200);

    }

    public function addToCart(Request $request, $cartKey) {

        if(empty($cartKey)) {
            return response()->json([
                'errors' => 'cart key is required.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'qty' => 'required|min:1|max:99',
        ]);

        if($validator->fails()) {
            return response()->json([
                'errors' => [
                    'message' => $validator->errors()
                 ]
            ], 422);
        }

        $shoppingCart = Cart::where('key', $cartKey)->first();
        if($shoppingCart != null) {
            $cartItems = CartItem::where('cart_id', $shoppingCart->id)->where('product_id', $request->product_id)->first();
            if($cartItems != null) {
                $cartItems->qty = $request->qty;
                $cartItems->save();
            } else {
                $newCartItems = CartItem::create([
                    'cart_id' => $shoppingCart->id,
                    'product_id' => $request->product_id,
                    'qty' => $request->qty
                ]);
                $newCartItems->save();
            }
        }

        return response()->json([
            'message' => 'cart items added successfully.',
        ], 200);
    }

    public function removeCartItem($cartKey, $itemId) {

        if(empty($cartKey)) {
            return response()->json([
                'errors' => 'cart key is required.'
            ], 422);
        }

        if(empty($itemId)) {
            return response()->json([
                'errors' => 'product id is required.'
            ], 422);
        }

        $shoppingCart = Cart::where('key', $cartKey)->first();
        if($shoppingCart != null) {
            $cartItems = CartItem::where('cart_id', $shoppingCart->id)->where('product_id', $itemId)->first();
            if($cartItems != null) {
                CartItem::where('cart_id', $shoppingCart->id)->where('product_id', $itemId)->delete();
            } else {
                return response()->json([
                    'errors' => 'product not found.'
                ], 422);
            }
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'cart not found.'
                 ]
            ], 422);
        }

        return response()->json([
            'message' => 'deleted items successfully.',
        ], 200);
    }

    public function removeCart(Request $request, $cartKey)
    {
        if(empty($cartKey)) {
            return response()->json([
                'errors' => 'cart key is required.'
            ], 422);
        }

        $shoppingCart = Cart::where('key', $cartKey)->first();
        if($shoppingCart != null) {
            $cartItems = CartItem::where('cart_id', $shoppingCart->id)->get();
            if($cartItems != null) {
                CartItem::where('cart_id', $shoppingCart->id)->delete();
            }
            $shoppingCart->delete();
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'cart not found.'
                 ]
            ], 422);
        }

        return response()->json([
            'message' => 'deleted cart successfully.',
        ], 200);
    }

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
                    }
                }
            }

            $transactionId = Str::uuid()->toString();
            if($amount > 0 && $summaryPrice > 0) {
                $shoppingCart->amount = $amount;
                $shoppingCart->user_id = auth('sanctum')->user()->id;
                $shoppingCart->save();

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
                return response()->json([
                    'message' => 'order check out successfully.',
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }
    }
}
