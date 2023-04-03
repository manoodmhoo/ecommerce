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
use Illuminate\Support\Facades\Mail;

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

            return response()->json([
                'cart' =>  $shoppingCart,
                'items' =>  isset($cartItems) ? $cartItems : [],
            ], 200);
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'cart not found.'
                 ]
            ], 422);
        }

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
        $product = Product::where('id', $request->product_id)->first();

        if($product != null) {
            if($product->stock >= $request->qty) {
                if($shoppingCart != null) {
                    $shoppingCart->amount = ($shoppingCart->amount + $request->qty);
                    $shoppingCart->save();

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
            } else {
                return response()->json([
                    'errors' => 'Out of Stock.',
                ], 422);
            }
        } else {
            return response()->json([
                'errors' => 'Product not found.',
            ], 422);
        }
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


}
