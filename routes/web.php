<?php

use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductsController;


// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// require __DIR__.'/auth.php';

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home.index');

Route::get('/shop',[ShopController::class,'index'])->name('shop.index');
Route::get('/shop/{product_slug}',[ShopController::class,'product_details'])->name("shop.product.details");

Route::get('/cart',[CartController::class,'index'])->name('cart.index');

Route::post('/cart/store', [CartController::class, 'addToCart'])->name('cart.add');




Route::middleware(['auth'])->group(function(){
    Route::get('/account-dashboard', [UserController::class,'index'])->name('user.index');
});


Route::middleware(['auth', AuthAdmin::class])->group(function(){
    Route::prefix('admin')->group(function() {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/brands', [AdminController::class, 'brands'])->name('admin.brands');
        Route::get('/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
        Route::post('/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
        Route::get('/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
        Route::put('/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
        Route::delete('/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');
        // categories
        Route::get('/categories', [CategoryController::class, 'categories'])->name('admin.categories');
        Route::get('/category/add', [CategoryController::class, 'category_add'])->name('admin.category.add');
        Route::post('/category/store', [CategoryController::class, 'category_store'])->name('admin.category.store');
        Route::get('/category/{id}/edit', [CategoryController::class, 'category_edit'])->name('admin.category.edit');
        Route::put('/category/update', [CategoryController::class, 'category_update'])->name('admin.category.update');
        Route::delete('/admin/category/{id}/delete',[CategoryController::class,'category_delete'])->name('admin.category.delete');
        //products
        Route::get('/admin/products',[ProductsController::class,'products'])->name('admin.products');
        Route::get('/admin/product/add',[ProductsController::class,'product_add'])->name('admin.product.add');
        Route::post('/admin/product/store',[ProductsController::class,'product_store'])->name('admin.product.store');
        Route::get('/admin/product/{id}/edit',[ProductsController::class,'product_edit'])->name('admin.product.edit');
        Route::put('/admin/product/update',[ProductsController::class,'product_update'])->name('admin.product.update');
        Route::delete('/admin/product/{id}/delete',[ProductsController::class,'product_delete'])->name('admin.product.delete');
        //cart


    });
});



