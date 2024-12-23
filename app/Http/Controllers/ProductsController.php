<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductStatusResource;
use App\Http\Resources\UsersResource;
use App\Http\Resources\WarehousesResource;
use App\Models\Media;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\ProductStatus;
use App\Models\Rating;
use App\Models\User;
use App\Models\Warehouse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    //Create product category
    public function create_category(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string',
        ]);
        $category = ProductCategory::create($data);
        $created_category = ProductCategory::find($category->id);
        return $this->sendResponse(ProductCategoryResource::make($created_category)->response()->getData(true), 'Category created successfully');
    }

    //Create product status
    public function create_product_status(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uuid = Str::uuid();
            $imageName = $uuid . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image_path = $image->storeAs('product_status', $imageName, 'public');
            $data['image'] = asset('storage/' . $image_path);
        }
        $status = ProductStatus::create($data);
        $created_status = ProductStatus::find($status->id);
        return $this->sendResponse(ProductStatusResource::make($created_status)->response()->getData(true), 'Status created successfully');
    }

    //Get all product statuses
    public function get_product_statuses(Request $request)
    {
        $statuses = ProductStatus::paginate(25);
        return $this->sendResponse(ProductStatusResource::collection($statuses)->response()->getData(true), 'Statuses retrieved successfully');
    }

    //Get all product categories
    public function get_categories(Request $request)
    {
        $categories = ProductCategory::paginate(25);
        return $this->sendResponse(ProductCategoryResource::collection($categories)->response()->getData(true), 'Categories retrieved successfully');
    }

    //Create product
    public function create_product(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'unit' => 'required|string',
            'cost' => 'required|string',
            'price' => 'required|string',
            'book_status' => 'required|uuid|exists:pos_book_status,id',
            'category_id' => 'required|uuid|exists:pos_products_category,id',
            'alert_quantity' => 'nullable|string',
            'track_quantity' => 'nullable|string',
            'barcode_symbol' => 'nullable|string',
            'type' => 'nullable|string',
            'author' => 'nullable|string',
            'description' => 'nullable|string',
            'warehouse_quantities' => 'nullable|array',
            'warehouse_quantities.*.warehouse_id' => 'required|uuid|exists:pos_warehouses,id|distinct',
            'warehouse_quantities.*.quantity' => 'required|integer|min:0',
            'color' => 'nullable|array',
            'color.*' => 'uuid|exists:colors,id',
            'tags' => 'required|string',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'uuid|exists:media,id',
        ]);

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $uuid = Str::uuid();
        //     $image_name = $uuid . '-' . time() . '.' . $image->getClientOriginalExtension();
        //     $image_path = $image->storeAs('product_images', $image_name, 'public');
        //     $data['image'] = asset('storage/' . $image_path);
        // }

        $slug = Str::slug($data['title']);
        $data['slug'] = $slug;
        // dd($slug);
        $product = Products::where('slug', $slug)->first();

        if ($product) {
            return $this->sendError($error = 'Product with this slug already exists', $code = 403);
        }

        $mediaData = $request->input('media_ids');
        unset($data['media_ids']);

        $warehouse_quantites = $request->input('warehouse_quantities', []);
        unset($data['warehouse_quantities']);

        $product = Products::create($data);

        if (!empty($mediaData)) {
            Media::whereIn('id', $mediaData)
                ->update(['medially_id' => $product->id, 'medially_type' => Products::class]);
        }

        $warehouses = Warehouse::all();

        $total_quantity = 0;

        if (empty($warehouse_quantites)) {
            foreach ($warehouses as $warehouse) {
                DB::table('product_warehouse')->insert([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            $added_warehouse = [];

            foreach ($warehouse_quantites as $warehouse_data) {
                DB::table('product_warehouse')->insert([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse_data['warehouse_id'],
                    'quantity' => $warehouse_data['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $total_quantity += $warehouse_data['quantity'];

                $added_warehouse[] = $warehouse_data['warehouse_id'];
            }

            foreach ($warehouses as $warehouse) {
                if (!in_array($warehouse->id, $added_warehouse)) {
                    DB::table('product_warehouse')->insert([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $product->update(['quantity' => $total_quantity]);

        $created_product = Products::with('category', 'status', 'warehouse_quantities', 'media', 'ratings.user')->find($product->id);
        return $this->sendResponse(ProductResource::make($created_product)->response()->getData(true), 'Product created successfully');
    }

    //Get all products
    public function get_products(Request $request)
    {
        try {
            $query = Products::with(['category', 'status', 'warehouse_quantities', 'media', 'ratings.user', 'brand']);

            //Filtering
            if ($request->has('brand')) {
                $query->whereHas('brand', function ($q) use ($request) {
                    $q->where('title', $request->query('brand'));
                });
            }

            if ($request->has('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('title', $request->query('category'));
                });
            }

            if ($request->has('tag')) {
                $query->whereJsonContains('tags', $request->query('tag'));
            }

            if ($request->has('color')) {
                $query->whereJsonContains('color', $request->query('color'));
            }

            if ($request->has('minPrice')) {
                $query->where('price', '>=', $request->query('minPrice'));
            }

            if ($request->has('maxPrice')) {
                $query->where('price', '<=', $request->query('maxPrice'));
            }

            //Sorting
            if ($request->has('sort')) {
                $sortFields = explode(',', $request->query('sort'));
                foreach ($sortFields as $sortField) {
                    $direction = 'asc';
                    if (substr($sortField, 0, 1) === '-') {
                        $direction = 'desc';
                        $sortField = substr($sortField, 1);
                    }
                    $query->orderBy($sortField, $direction);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            //Limiting Fields
            if ($request->has('fields')) {
                $fields = explode(',', $request->query('fields'));
                $query->select($fields);
            }

            // Pagination
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 20);
            $products = $query->paginate($limit, ['*'], 'page', $page);

            return $this->sendResponse(ProductResource::collection($products)->response()->getData(true), 'Products retrieved successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get single product
    public function get_product(Products $product)
    {
        $product->load('media', 'ratings.user');
        return $this->sendResponse(ProductResource::make($product)->response()->getData(true), 'Product retrieved successfully');
    }

    //Update product
    public function update_product(Request $request, Products $product)
    {
        $mediaData = $request->input('media_ids');
        if (!empty($mediaData)) {
            Media::whereIn('id', $mediaData)->update(['medially_id' => $product->id, 'medially_type' => Products::class]);
        }
        $requestData = $request->except(['media_ids']);
        $product->update($requestData);
        $updatedProduct = Products::with('media', 'ratings.user')->find($product->id);
        return $this->sendResponse(ProductResource::make($updatedProduct)->response()->getData(true), 'Product updated successfully');
    }

    //Delete product
    public function delete_product(Request $request, Products $product)
    {
        $mediaItems = Media::where('medially_id', $product->id)->where('medially_type', Products::class)->get();
        foreach ($mediaItems as $mediaItem) {
            try {
                Cloudinary::destroy($mediaItem->public_id);
                $mediaItem->delete();
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
        $product->delete();
        return $this->sendResponse([], 'Product deleted successfully');
    }

    //Add to wishlist
    public function add_to_wishlist(Request $request, Products $product)
    {
        $user = auth()->user();
        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            $user->detach('wishlist', $product->id);
            $user->load('wishlist');
            return $this->sendResponse([], 'Product removed from wishlist');
        } else {
            $user->attach('wishlist', $product->id);
            $user->load('wishlist');
            return $this->sendResponse(UsersResource::make($user)->response()->getData(true), 'Product added to wishlist');
        }
    }

    //Get user wishlist
    public function get_wishlist()
    {
        $user = auth()->user();
        $wishlist = $user->wishlist()->paginate(25);
        $user->load(['wishlist', 'ratings.user']);
        return $this->sendResponse(ProductResource::make($wishlist)->response()->getData(true), 'User wishlist retrieved successfully');
    }

    //Rate a product
    public function rate_product(Request $request, Products $product)
    {
        $user = auth()->user();
        $data = $request->validate([
            'star' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'product' => 'required|exists:products,id',
        ]);
        $existingRating = $product->ratings()->where('user_id', $user->id)->first();
        if ($existingRating) {
            $existingRating->update([
                'star' => $data['star'],
                'comment' => $data['comment'],
            ]);
            $message = 'Rating updated successfully';
        } else {
            Rating::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'star' => $data['star'],
                'comment' => $data['comment'],
            ]);
            $message = 'Rating created successfully';
        }
        $totalRating = $product->ratings()->avg('star');
        $product->update(['total_ratings' => $totalRating]);
        $product->load('ratings.user', 'media');
        return $this->sendResponse(ProductResource::make($product)->response()->getData(true), $message);
    }

    //Create warehouse
    public function create_warehouse(Request $request)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'address' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $warehouse = Warehouse::create($data);
        $created_warehouse = Warehouse::find($warehouse->id);
        return $this->sendResponse(WarehousesResource::make($created_warehouse)->response()->getData(true), 'Warehouse created successfully');
    }

    //Update warehouse
    public function update_warehouse(Request $request, Warehouse $warehouse)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string',
            'address' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $warehouse->update($data);
        $updated_warehouse = Warehouse::find($warehouse->id);
        return $this->sendResponse(WarehousesResource::make($updated_warehouse)->response()->getData(true), 'Warehouse updated successfully');
    }

    //Get all warehouses
    public function get_warehouses()
    {
        $warehouses = Warehouse::all();
        return $this->sendResponse(WarehousesResource::collection($warehouses)->response()->getData(true), 'Warehouses retrieved successfully');
    }

    //Get warehouse
    public function get_warehouse(Warehouse $warehouse)
    {
        return $this->sendResponse(WarehousesResource::make($warehouse)->response()->getData(true), 'Warehouse retrieved successfully');
    }

    //Delete warehouse
    public function delete_warehouse(Warehouse $warehouse)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }
        $warehouse->delete();
        return $this->sendResponse([], 'Warehouse deleted successfully');
    }

    //Update warehouse status
    public function update_warehouse_status(Request $request, Warehouse $warehouse)
    {
        if (!auth()->user()->inGroup('Owner')) {
            return response()->json(['message' => 'Access denied for this user group'], 403);
        }
        $data = $request->validate([
            'active' => 'boolean',
        ]);
        $warehouse->update($data);

        return $this->sendResponse(WarehousesResource::make($warehouse)->response()->getData(true), 'Warehouse status updated successfully');
    }

    //Get warehouse products
    public function get_warehouse_products(Warehouse $warehouse)
    {
        $products = $warehouse->products()->paginate(25);
        return $this->sendResponse(ProductResource::collection($products)->response()->getData(true), 'Products retrieved successfully');
    }
}
