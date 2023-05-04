<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
         switch ($request->trashed){
                case "with":
                    $brands = Brand::withTrashed()->orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
                    break;
                case "only":
                    $brands = Brand::onlyTrashed()->orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
                    break;
                default:
                    $brands = Brand::orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
            }
        return response()->json(['brands' => $brands ], 200);
    }

    public function create(Request $request)
    {
        Brand::create([
            'user_id' => $request->id,
            'name' => $request->name,
            'is_show' => $request->is_show,
        ]);

        return response()->json(['message' => 'Brand successfully created'],200);
    }

    public function update(Request $request)
    {
        Brand::find($request->id)->update([
            'name' => $request->name,
            'is_show' => $request->is_show
        ]);
        return response()->json(['message' => 'Brand successfully updated'],200);

    }

    public function delete(Request $request)
    {
        Brand::find($request->id)->delete();

        return response()->json(['message' => 'Brand successfully deleted'],200);
    }

    public function restore(Request $request)
    {
        Brand::withTrashed()->find($request->id)->restore();

        return response()->json(['message' => 'Brand successfully restored'],200);
    }
}
