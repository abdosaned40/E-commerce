<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::instance('cart')->content();

        return view('cart', compact('cartItems'));
    }

    public function addToCart(Request $request)
{
    Cart::instance('cart')->add($request->id,$request->name,$request->quantity,$request->price)->associate('App\Models\Product');
    session()->flash('success', 'Product is Added to Cart Successfully !');
    return response()->json(['status'=>200,'message'=>'Success ! Item Successfully added to your cart.']);
}


   // add + less

   public function increase_item_quantity($rowId)
   {
       DB::transaction(function () use ($rowId) {
           $product = Cart::instance('cart')->get($rowId);

           DB::table('products')
               ->where('id', $product->id)
               ->lockForUpdate()
               ->first();

           $qty = $product->qty + 1;

           Cart::instance('cart')->update($rowId, $qty);
       });

       return redirect()->back();
   }

   public function reduce_item_quantity($rowId)
   {
       DB::transaction(function () use ($rowId) {
           $product = Cart::instance('cart')->get($rowId);

           DB::table('products')
               ->where('id', operator: $product->id)
               ->lockForUpdate()
               ->first();

           $qty = max($product->qty - 1, 1);

           Cart::instance('cart')->update($rowId, $qty);
       });

       return redirect()->back();
   }


public function remove_item_from_cart($rowId)
{
    DB::transaction(function () use ($rowId) {
        $product = Cart::instance('cart')->get($rowId);

        DB::table('products')
            ->where('id', $product->id)
            ->lockForUpdate()
            ->first();

        Cart::instance('cart')->remove($rowId);
    });

    return redirect()->back();
}


public function empty_cart()
{
    Cart::instance('cart')->destroy();
    return redirect()->back();
}

}
