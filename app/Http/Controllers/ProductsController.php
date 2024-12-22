<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductStatusResource;
use App\Http\Resources\WarehouseResource;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\ProductStatus;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsController extends Controller
{

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

    public function get_product_statuses(Request $request)
    {
        $statuses = ProductStatus::paginate(25);
        return $this->sendResponse(ProductStatusResource::collection($statuses)->response()->getData(true), 'Statuses retrieved successfully');
    }

    public function get_categories(Request $request)
    {
        $categories = ProductCategory::paginate(25);
        return $this->sendResponse(ProductCategoryResource::collection($categories)->response()->getData(true), 'Categories retrieved successfully');
    }
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'warehouse_quantities' => 'nullable|array',
            'warehouse_quantities.*.warehouse_id' => 'required|uuid|exists:pos_warehouses,id|distinct',
            'warehouse_quantities.*.quantity' => 'required|integer|min:0'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uuid = Str::uuid();
            $image_name = $uuid . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image_path = $image->storeAs('product_images', $image_name, 'public');
            $data['image'] = asset('storage/' . $image_path);
        }

        $warehouse_quantites = $request->input('warehouse_quantities', []);
        unset($data['warehouse_quantities']);

        $product = Products::create($data);
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

        $created_product = Products::with('category', 'status', 'warehouse_quantities')->find($product->id);
        return $this->sendResponse(ProductResource::make($created_product)->response()->getData(true), 'Product created successfully');
    }


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
        ]);
        $warehouse = Warehouse::create($data);
        $created_warehouse = Warehouse::find($warehouse->id);
        return $this->sendResponse(WarehouseResource::make($created_warehouse)->response()->getData(true), 'Warehouse created successfully');
    }

    public function get_warehouses(Request $request)
    {
        $warehouses = Warehouse::all();
        return $this->sendResponse(WarehouseResource::collection($warehouses)->response()->getData(true), 'Warehouses retrieved successfully');
    }

    public function get_warehouse_products(Request $request, Warehouse $warehouse)
    {
        $products = $warehouse->products()->paginate(25);
        return $this->sendResponse(ProductResource::collection($products)->response()->getData(true), 'Products retrieved successfully');
    }
}
