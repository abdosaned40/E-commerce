<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
   
public function categories()
{
       $categories = Category::orderBy('id','DESC')->paginate(10);
       return view("admin.categories",compact('categories'));
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
        $file_extention = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;

     
        $this->GenerateCategoryThumbailImage($image, $file_name);

      
        $category->image = $file_name;
    }

    $category->save();

    return redirect()->route('admin.categories')->with('status', 'Record has been added successfully!');
}

}
