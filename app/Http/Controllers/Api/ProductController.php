<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockPrice;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    //


    public function index(Request $request)
    {

        $user_id = Auth::user()->id;
        $brands = Brand::where('user_id', $user_id)->where('is_show', 1)->get();
        $categories = Category::where('user_id', $user_id)->get();
        $suppliers = Supplier::where('user_id', $user_id)->get();
        switch ($request->trashed) {
            case "with":
                $products = Product::with(['latestStockRecord','brand','category','supplier'])
                    ->withTrashed()
                    ->where('user_id', $user_id)
                    ->where(function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('code', 'LIKE', '%' . $request->search . '%');
                    })
                    ->when($request->brand, function ($query) use ($request) {
                        $query->where('brand_id', $request->brand);
                    })
                    ->when($request->category, function ($query) use ($request) {
                        $query->where('category_id', $request->category);
                    })
                    ->when($request->supplier, function ($query) use ($request) {
                        $query->where('supplier_id', $request->supplier);
                    })
                    ->when($request->stock, function ($query, $stock) {
                        if ($stock === 'out of stock' ) {
                            $query->where('current_stock' ,'=',0);
                        }
                        elseif ($stock === '1 to 10' ){
                            $query->whereBetween('current_stock',[1,10]);
                        }
                        elseif ($stock === '11 to 20' ){
                            $query->whereBetween('current_stock',[11,20]);
                        }
                        elseif ($stock === 'over 20'){
                            $query->where('current_stock','>' , 20);
                        }

                    })
                    ->orderBy('id', 'DESC')
                    ->paginate($request->perpage);
                break;
            case "only":
                $products = Product::with(['latestStockRecord','brand','category','supplier'])
                    ->onlyTrashed()
                    ->where('user_id', $user_id)
                    ->where(function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('code', 'LIKE', '%' . $request->search . '%');
                    })
                    ->when($request->brand, function ($query) use ($request) {
                        $query->where('brand_id', $request->brand);
                    })
                    ->when($request->category, function ($query) use ($request) {
                        $query->where('category_id', $request->category);
                    })
                    ->when($request->supplier, function ($query) use ($request) {
                        $query->where('supplier_id', $request->supplier);
                    })
                    ->when($request->stock, function ($query, $stock) {
                        if ($stock === 'out of stock' ) {
                            $query->where('current_stock' ,'=',0);
                        }
                        elseif ($stock === '1 to 10' ){
                            $query->whereBetween('current_stock',[1,10]);
                        }
                        elseif ($stock === '11 to 20' ){
                            $query->whereBetween('current_stock',[11,20]);
                        }
                        elseif ($stock === 'over 20'){
                            $query->where('current_stock','>' , 20);
                        }

                    })
                    ->orderBy('id', 'DESC')
                    ->paginate($request->perpage);
                break;
            default:
                $products = Product::with(['latestStockRecord','brand','category','supplier'])->where('user_id', $user_id)
                    ->where(function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('code', 'LIKE', '%' . $request->search . '%');
                    })
                    ->when($request->brand, function ($query) use ($request) {
                        $query->where('brand_id', $request->brand);
                    })
                    ->when($request->category, function ($query) use ($request) {
                        $query->where('category_id', $request->category);
                    })
                    ->when($request->supplier, function ($query) use ($request) {
                        $query->where('supplier_id', $request->supplier);
                    })
                    ->when($request->stock, function ($query, $stock) {
                        if ($stock === 'out of stock' ) {
                            $query->where('current_stock' ,'=',0);
                        }
                        elseif ($stock === '1 to 10' ){
                            $query->whereBetween('current_stock',[1,10]);
                        }
                        elseif ($stock === '11 to 20' ){
                            $query->whereBetween('current_stock',[11,20]);
                        }
                        elseif ($stock === 'over 20'){
                            $query->where('current_stock','>' , 20);
                        }

                    })
                    ->orderBy('id', 'DESC')
                    ->paginate($request->perpage);
        }

        return response()->json(['products' => $products, 'brands' => $brands, 'categories' => $categories, 'suppliers' => $suppliers], 200);
    }

    public function create(Request $request)
    {
        if (!empty(Product::where('user_id', Auth::user()->id)->where('code', $request->code)->first())) {
            return response()->json(['codeError' => 'Product code already exits!'], 401);
        }
        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'mimes:jpg,jpeg,png|max:2048'
            ]);
            $imageName = time() . '.' . $request->photo->getClientOriginalExtension();
            $request->photo->move(public_path('products'), $imageName);
        } else {
            $imageName = 'default.jpg';
        }

        $product = Product::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'code' => $request->code,
            'variant' => $request->variant,
            'description' => $request->description,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'price' => $request->selling_price,
            'is_show' => $request->is_show,
            'current_stock' => $request->stock,
            'photo' => $imageName,
        ]);


        StockPrice::create([
            'product_id' => $product->id,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
        ]);

        return response()->json(['message' => 'Product successfully created'], 200);
    }

    public function update(Request $request)
    {
        $check = Product::where('id',$request->id)->first();
        $StockPrice = StockPrice::find($request->last_stock_id)->first();
        if($check->code !== $request->code){
            if (!empty(Product::where('user_id', Auth::user()->id)->where('code', $request->code)->first())) {
                return response()->json(['codeError' => 'Product code already exits!'], 401);
            }
        }
        if($StockPrice->buying_price != $request->buying_price){

            $request->validate([
                'reason' => 'required|string|max:255',
            ]);

        }
        if($StockPrice->selling_price != $request->selling_price){

            $request->validate([
                'reason' => 'required|string|max:255',
            ]);

        }
        if($check->current_stock != $request->stock){

            $request->validate([
                'reason' => 'required|string|max:255',
            ]);

        }

        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'mimes:jpg,jpeg,png|max:2048'
            ]);
            $imageName = time() . '.' . $request->photo->getClientOriginalExtension();
            $request->photo->move(public_path('products'), $imageName);

            Product::find($request->id)->update([
                'photo' => $imageName
            ]);
        }

        Product::find($request->id)->update([
            'name' => $request->name,
            'code' => $request->code,
            'variant' => $request->variant,
            'description' => $request->description,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'is_show' => $request->is_show,
            'price' => $request->selling_price,
            'current_stock' => $request->stock,
        ]);

        $diff = $request->stock - $check->current_stock ;

        $newstock = $StockPrice->stock + $diff;
        $StockPrice->update([
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'reason' => $request->reason,
            'stock' => $newstock,
        ]);


        return response()->json(['message' => 'Product successfully updated'],200);

    }

    public function stock(Request $request)
    {
        $stocks = StockPrice::orderBy('id', 'DESC')->where('product_id',$request->product_id)->paginate(10);

        return response()->json(['stocks'=> $stocks] ,200);
    }

    public function addStock(Request $request)
    {
        Product::find($request->product_id)->update([
            'price' => $request->selling_price,
            'current_stock' => $request->current_stock,
        ]);

        StockPrice::create([
            'product_id' => $request->product_id,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
        ]);

        return response()->json(['message' => 'Product Stock successfully added'],200);
    }

    public function delete(Request $request)
    {
        Product::find($request->id)->delete();

        return response()->json(['message' => 'Product successfully deleted'],200);
    }

    public function restore(Request $request)
    {
        Product::withTrashed()->find($request->id)->restore();

        return response()->json(['message' => 'Product successfully restored'],200);
    }
}
