<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        switch ($request->trashed){
            case "with":
                $categories = Category::withTrashed()->orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
                break;
            case "only":
                $categories = Category::onlyTrashed()->orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
                break;
            default:
                $categories = Category::orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
        }
        return response()->json(['categories' => $categories ], 200);
    }

    public function create(Request $request)
    {
        Category::create([
            'user_id' => $request->id,
            'name' => $request->name,
            'is_show' => $request->is_show,
        ]);

        return response()->json(['message' => 'Category successfully created'],200);
    }

    public function update(Request $request)
    {
        Category::find($request->id)->update([
            'name' => $request->name,
            'is_show' => $request->is_show
        ]);
        return response()->json(['message' => 'Category successfully updated'],200);

    }

    public function delete(Request $request)
    {
        Category::find($request->id)->delete();

        return response()->json(['message' => 'Category successfully deleted'],200);
    }

    public function restore(Request $request)
    {
        Category::withTrashed()->find($request->id)->restore();

        return response()->json(['message' => 'Category successfully restored'],200);
    }
}
