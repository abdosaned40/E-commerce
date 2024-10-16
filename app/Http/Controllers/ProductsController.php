<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductsController extends Controller
{
    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view("admin.products", compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }

    public function product_store(ProductRequest $request)
    {
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        // معالجة الصورة الرئيسية
        if ($request->hasFile('image')) {
            $this->deleteOldImage($product->image); // حذف الصورة القديمة إذا وجدت
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->generateThumbnailImage($image, $imageName); // إنشاء مصغرات
            $product->image = $imageName;
        }

        // معالجة معرض الصور
        $gallery_images = $this->processGalleryImages($request, $current_timestamp);
        $product->images = $gallery_images;

        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();

        return redirect()->route('admin.products')->with('status', 'Record has been added successfully!');
    }

    private function deleteOldImage($image)
    {
        if ($image && Storage::disk('public')->exists('categories/' . $image)) {
            Storage::disk('public')->delete('categories/' . $image);
        }
    }

    private function processGalleryImages(Request $request, $current_timestamp)
    {
        $gallery_arr = [];
        $counter = 1;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $gextension = $file->getClientOriginalExtension();
                if (in_array($gextension, ['jpg', 'png', 'jpeg'])) {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->generateThumbnailImage($file, $gfilename);
                    $gallery_arr[] = $gfilename;
                    $counter++;
                }
            }
        }

        return implode(',', $gallery_arr);
    }

    public function product_edit($id)
    {
        $product = Product::findOrFail($id); // استخدم findOrFail لتجنب الأخطاء في حالة عدم وجود المنتج
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

    public function product_update(Request $request, $id)
    {
        $product = Product::findOrFail($id); // استخدم findOrFail بدلاً من find فقط
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $id,
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required|boolean',
            'quantity' => 'required|integer',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ]);

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        // تحديث الصورة الرئيسية إذا تم تحميل صورة جديدة
        if ($request->hasFile('image')) {
            $this->deleteOldImage($product->image);

            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->generateThumbnailImage($image, $imageName);

            $product->image = $imageName;
        }

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Record has been updated successfully!');
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
    
}
