<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        if(auth()->user()->can('list-categories')) {
            $currentPage = isset($request->page) ? (int)$request->page : 1;
            $categories = Cache::remember('product-' . $currentPage, 10, function(){
                return  DB::table('categories')->orderBy('updated_at', 'desc')->paginate(10);
            });
            return response()->json([
                'categories' => $categories
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
        if(auth()->user()->can('create-categories')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if($validator->fails()) {
                return response()->json([
                    'errors' => [
                        'message' => $validator->errors()
                     ]
                ], 422);
            }

            $category = new Category();
            $category->name = trim($request->name);
            $category->save();

            return response()->json([
                'messages' => 'Category Created.',
                'category' => trim($request->name)
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        if(auth()->user()->can('view-categories')) {
            $category = Category::find($id);

            if(empty($category)) {
                return response()->json([
                    'errors' => [
                        'message' => 'Category Not Found.'
                     ]
                ], 404);
            }
            return response()->json([
                'category' => $category
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
        if(auth()->user()->can('edit-categories')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if($validator->fails()) {
                return response()->json([
                    'errors' => [
                        'message' => $validator->errors()
                     ]
                ], 422);
            }

            $category = Category::find($id);
            $category->name = trim($request->name);
            $category->save();

            return response()->json([
                'messages' => 'Category Updated.',
                'category' => trim($request->name)
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if(auth()->user()->can('delete-categories')) {
            $category = Category::find($id);

            if(empty($category)) {
                return response()->json([
                    'errors' => [
                        'message' => 'Category Not Found.'
                     ]
                ], 404);
            }

            $category->delete();

            return response()->json([
                'messages' => 'Category Deleted.',
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
