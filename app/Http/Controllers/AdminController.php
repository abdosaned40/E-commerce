<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // Dashboard view
    public function index() {
        return view('admin.index');
    }

    // Display all brands
    public function brands() {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    // View to add a new brand
    public function add_brand() {
        return view('admin.brand-add');
    }

    // Store new brand in the database
    public function brand_store(Request $request) {
        // Validate incoming request
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        // Create new brand instance
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        // Handle image upload and resizing
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extension = $image->getClientOriginalExtension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            // Generate thumbnail image for the brand
            $this->generateCategoryThumbnailImage($image, $file_name);

            // Save image filename to the brand record
            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully!');
    }

    public function brand_edit($id) {
        $brand = Brand::findOrFail($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request) {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = Brand::findOrFail($request->id);
        $brand->name = $request->name;
        $brand->slug = $request->slug;

        if ($request->hasFile('image')) {
            if ($brand->image && Storage::exists('public/brands/' . $brand->image)) {
                Storage::delete('public/brands/' . $brand->image);
            }

            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            $this->generateCategoryThumbnailImage($image, $file_name);

            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Record has been updated successfully!');
    }

    public function brand_delete($id) {
        $brand = Brand::findOrFail($id);

        if ($brand->image && Storage::exists('app/public/categories/' . $brand->image)) {
            Storage::delete('app/public/categories/' . $brand->image);
        }

        $brand->delete();

        return redirect()->route('admin.brands')->with('status', 'Record has been deleted successfully!');
    }

    private function generateCategoryThumbnailImage($image, $file_name) {
        $image_info = getimagesize($image);
        $image_type = $image_info[2];

        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $src_image = imagecreatefromjpeg($image);
                break;
            case IMAGETYPE_PNG:
                $src_image = imagecreatefrompng($image);
                break;
            case IMAGETYPE_GIF:
                $src_image = imagecreatefromgif($image);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        $thumbnail_width = 150;
        $thumbnail_height = 150;
        $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

        list($src_width, $src_height) = $image_info;

        imagecopyresampled($thumbnail, $src_image, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $src_width, $src_height);

        $thumbnail_path = storage_path('app/public/categories/' . $file_name);

        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $thumbnail_path);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $thumbnail_path);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $thumbnail_path);
                break;
        }

        imagedestroy($thumbnail);
        imagedestroy($src_image);
    }
}
