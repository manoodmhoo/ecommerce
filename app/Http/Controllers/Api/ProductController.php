<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- use DB
use Illuminate\Support\Facades\Storage; // <-- use Storage
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Contracts\Cache\Store;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        if(auth()->user()->can('list-products')) {
            $products = Product::with('category')->get();
            return response()->json([
                'products' => $products,
            ], 200);
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Access denied.'
                 ]
            ], 401);
        }
    }

    public function productCanBuy()
    {
        if(auth()->user()->can('list-products')) {
            $products = Product::with('category')->where('stock', '>', 0)->get();
            return response()->json([
                'products' => $products,
            ], 200);
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Access denied.'
                 ]
            ], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if(auth()->user()->can('create-products')) {
            try {
                DB::beginTransaction();

                $validator = Validator::make($request->all(), [
                    'sku' => 'required',
                    'name' => 'required',
                    'description' => 'nullable',
                    'price' => 'required|numeric|regex:/^(\d+(,\d{1,2})?)?$/',
                    'stock' => 'required|numeric',
                    'category_id' => 'required'
                ]);

                if($validator->fails()) {
                    return response()->json([
                        'errors' => [
                            'message' => $validator->errors()
                         ]
                    ], 422);
                }

                $product = new Product();
                $product->sku = $request->sku;
                $product->name = $request->name;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->stock = $request->stock;
                $product->category_id = $request->category_id;

                if($request->has('image')) {
                    $base64_image = $request->image;
                    @list($type, $file_data) = explode(';', $base64_image);
                    @list(, $file_data) = explode(',', $file_data);

                    $new_filename = uniqid().".png";
                    if($file_data != ''){
                        Storage::disk('public')->put('upload/'. $new_filename, base64_decode($file_data));
                    }

                    $product->image = $new_filename;
                }
                $product->save();
                DB::commit();

                return response()->json([
                    'messages' => 'Product Created.'
                ], 200);
            } catch (\Throwable $th ) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Product Create Failed.',
                    'system message' => $th->getMessage()
                ], 400);
            }
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Access denied.'
                 ]
            ], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        if(auth()->user()->can('view-products')) {
            $product = Product::find($id);

            if(empty($product)) {
                return response()->json([
                    'errors' => [
                        'message' => 'Product Not Found.'
                     ]
                ], 404);
            }
            return response()->json([
                'product' => $product
            ], 200);


        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Access denied.'
                 ]
            ], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        if(auth()->user()->can('edit-products')) {

            try {
                DB::beginTransaction();
                $validator = Validator::make($request->all(), [
                    'sku' => 'required',
                    'name' => 'required',
                    'description' => 'nullable',
                    'price' => 'required|numeric|regex:/^(\d+(,\d{1,2})?)?$/',
                    'stock' => 'required|numeric|min:1',
                    'image' => 'required',
                    'category_id' => 'required'
                ]);

                if($validator->fails()) {
                    return response()->json([
                        'errors' => [
                            'message' => $validator->errors()
                        ]
                    ], 422);
                }

                DB::commit();
                $product = Product::find($id);

                if(empty($product)) {
                    return response()->json([
                        'errors' => [
                            'message' => 'Product Not Found.'
                        ]
                    ], 404);
                }

                $product->sku = $request->sku;
                $product->name = $request->name;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->stock = $request->stock;
                $product->category_id = $request->category_id;

                if(Storage::disk('public')->exists('upload/'. $product->image)) {
                    Storage::disk('public')->delete('upload/'. $product->image);
                }

                if($request->has('image')) {
                    $base64_image = $request->image;
                    @list($type, $file_data) = explode(';', $base64_image);
                    @list(, $file_data) = explode(',', $file_data);

                    $new_filename = uniqid().".png";
                    if($file_data != ''){
                        Storage::disk('public')->put('upload/'. $new_filename, base64_decode($file_data));
                    }

                    $product->image = $new_filename;
                } else {
                    $product->image = 'nopic.png';
                }

                $product->save();
                return response()->json([
                    'messages' => 'Product Updated.',
                    'product' => trim($request->name)
                ], 200);

            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Product Update Failed.',
                    'system message' => $th->getMessage()
                ], 400);
            }
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Access denied.'
                 ]
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if(auth()->user()->can('delete-categories')) {
            $product = Product::find($id);

            if(empty($product)) {
                return response()->json([
                    'errors' => [
                        'message' => 'Product Not Found.'
                     ]
                ], 404);
            }

            if(Storage::disk('public')->exists('upload/'. $product->image)) {
                Storage::disk('public')->delete('upload/'. $product->image);
            }

            $product->delete();

            return response()->json([
                'messages' => 'Product Deleted.',
            ], 200);
        } else {
            return response()->json([
                'errors' => [
                    'message' => 'Access denied.'
                 ]
            ], 401);
        }
    }
}
