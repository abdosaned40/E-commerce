<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use function Pest\Laravel\get;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductsController extends Controller
{ 

        
public function products()
{
    $products = Product::OrderBy('created_at','DESC')->paginate(10);        
    return view("admin.products",compact('products'));
}

public function product_add() {
    $categories = Category::select('id', 'name')->orderBy('name')->get();
    $brands = Brand::select('id', 'name')->orderBy('name')->get();
    
    return view('admin.product-add', compact('categories', 'brands'));
}

  public function product_stor(ProductRequest $request, Product $product) {
    Product::create($request->validated());
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
     
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

            // حذف الصورة القديمة إن وجدت
           

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $file_extension = $image->getClientOriginalExtension();
                $file_name = Carbon::now()->timestamp . '.' . $file_extension;
    
                $this->generateBrandThumbnailsImage($image, $file_name);
    
                $product->image = $file_name;
            }
    
            $product->save();
    
            return redirect()->route('admin.product')->with('status', 'product has been added successfully!');
        }
   
        public function generateBrandThumbnailsImage($image, $imageName) {
            $img = Image::make($image->getRealPath());
            $img->resize(124, 124, function ($constraint) {
                $constraint->aspectRatio();
            });
    
            $img->stream(); 
    
            Storage::put('public/product/' . $imageName, $img);  
        }
  

}
