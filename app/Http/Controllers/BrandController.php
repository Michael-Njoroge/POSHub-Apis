<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get_brands()
    {
        $brands = Brand::paginate(25);
        return $this->sendResponse(BrandResource::collection($brands)->response()->getData(true), 'Brands retrieved successfully');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create_brand(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        $data['slug'] = Str::slug($data['title']);
        $brand = Brand::create($data);
        $created_brand = Brand::find($brand->id);
        return $this->sendResponse(BrandResource::make($created_brand)->response()->getData(true), 'Brand created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function get_brand(Brand $brand)
    {
        return $this->sendResponse(BrandResource::make($brand)->response()->getData(true), 'Brand retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_brand(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        $data['slug'] = Str::slug($data['title']);
        $brand->update($data);
        $updated_brand = Brand::find($brand->id);
        return $this->sendResponse(BrandResource::make($updated_brand)->response()->getData(true), 'Brand updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete_brand(Brand $brand)
    {
        $brand->delete();
        return $this->sendResponse([], 'Brand deleted successfully');
    }
}
