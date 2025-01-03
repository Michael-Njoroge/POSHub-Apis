<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductResource;
use App\Models\Blog;
use App\Models\Media;
use App\Models\Products;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'model_type' => 'required|string',
            'model_id' => 'nullable|string',
        ]);

        $modelType = $request->input('model_type');
        $modelId = $request->input('model_id');
        $folder = $modelType === 'product' ? 'products' : 'blogs';
        $mediaEntries = [];

        foreach ($request->file('files') as $file) {

            $file_type = $file->getMimeType();
            $file_size = $file->getSize();
            $file_name = $file->getClientOriginalName();
            $file_extension = time() . '.' . $file->getClientOriginalExtension();
            $tempPath = storage_path('app/public/temp/' . $file_extension);
            $file->move(storage_path('app/public/temp'), $file_extension);

            $file_manager = new ImageManager(new Driver());
            $thumbImage = $file_manager->read($tempPath);
            if ($modelType === 'blog') {
                $thumbImage->resize(800, 600);
            } else {
                $thumbImage->resize(300, 300);
            }

            $thumbImage->save($tempPath);

            $uniqueId = uniqid();
            $publicId = $folder . '/' . $uniqueId . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // Upload to Cloudinary
            $uploadedFile = Cloudinary::upload($tempPath, [
                'folder' => $folder,
                'public_id' => $publicId,
            ]);

            // Get the full response
            $uploadedFileUrl = $uploadedFile->getSecurePath();
            $assetId = $uploadedFile->getAssetId();

            // Delete the temporary file
            Storage::delete('public/temp/' . $file_extension);

            $media = new Media();
            $media->file_url = $uploadedFileUrl;
            $media->file_name = $file_name;
            $media->file_type = $file_type;
            $media->size = $file_size;
            $media->medially_id = $modelId;
            $media->medially_type = $modelType === 'product' ? Products::class : Blog::class;
            $media->asset_id = $assetId;
            $media->public_id = $publicId;
            $media->save();

            $mediaEntries[] = $media;
        }

        if ($modelId) {
            if ($modelType === 'product') {
                $product = Products::findOrFail($modelId);
                $product->media()->saveMany($mediaEntries);
                $product->load('media');

                return $this->sendResponse(ProductResource::make($product)
                    ->response()
                    ->getData(true), 'Product image uploaded successfully');
            } elseif ($modelType === 'blog') {
                $blog = Blog::findOrFail($modelId);
                $blog->media()->saveMany($mediaEntries);
                $blog->load('media');
                $blog->loadCount('likedBy');
                $blog->loadCount('dislikedBy');

                return $this->sendResponse(BlogResource::make($blog)
                    ->response()
                    ->getData(true), 'Blog image uploaded successfully');
            } else {
                return $this->sendError($error = 'Invalid model type');
            }
        } else {
            return $this->sendResponse(MediaResource::collection($mediaEntries)
                ->response()
                ->getData(true), 'Images uploaded successfully');
        }
    }
    public function deleteFromCloudinary(Request $request)
    {
        $request->validate([
            'public_ids' => 'required|array|exists:pos_media,public_id',
        ]);

        $publicIds = $request->input('public_ids');

        try {
            foreach ($publicIds as $publicId) {
                Cloudinary::destroy($publicId);

                $media = Media::where('public_id', $publicId)->first();
                if ($media) {
                    $media->delete();
                }
            }

            return $this->sendResponse([], 'Images deleted successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete images'], 500);
        }
    }
}
