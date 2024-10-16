<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CategoryController extends Controller
{
    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view("admin.categories", compact('categories'));
    }

    public function category_add()
    {
        return view("admin.category-add");
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            $path = Storage::disk('public')->putFileAs('categories', $image, $file_name);

            $category->image = $file_name;

            $this->GenerateCategoryThumbnailImage($image, $file_name);
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Record has been added successfully!');
    }

    public function category_edit(Category $category)
    {
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = $request->slug;

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists('categories/' . $category->image)) {
                Storage::disk('public')->delete('categories/' . $category->image);
            }

            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            Storage::disk('public')->putFileAs('categories', $image, $file_name);

            $this->GenerateCategoryThumbnailImage($image, $file_name);

            $category->image = $file_name;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Record has been updated successfully!');
    }

    private function generateCategoryThumbnailImage($image, $file_name)
    {
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
    
    public function category_delete($id)
    {
        $category = Category::find($id);
    
        if (Storage::disk('public')->exists('categories/' . $category->image)) {
            Storage::disk('public')->delete('categories/' . $category->image);
        }
    
        if (Storage::disk('public')->exists('categories/thumbnails/' . $category->image)) {
            Storage::disk('public')->delete('categories/thumbnails/' . $category->image);
        }
    
        $category->delete();
    
        return redirect()->route('admin.categories')->with('status', 'Record has been deleted successfully!');
    }
}
