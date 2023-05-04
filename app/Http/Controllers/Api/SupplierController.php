<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        switch ($request->trashed){
            case "with":
                $suppliers = Supplier::withTrashed()->orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
                break;
            case "only":
                $suppliers = Supplier::onlyTrashed()->orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
                break;
            default:
                $suppliers = Supplier::orderBy('id', 'DESC')->where('user_id',$user_id)->where('name', 'LIKE','%'.$request->search.'%')->paginate($request->perpage);
        }
        return response()->json(['suppliers' => $suppliers ], 200);
    }

    public function create(Request $request)
    {
        Supplier::create([
            'user_id' => $request->id,
            'name' => $request->name,
            'contact' => $request->contact,
        ]);

        return response()->json(['message' => 'Supplier successfully created']);
    }

    public function update(Request $request)
    {
        Supplier::find($request->id)->update([
            'name' => $request->name,
            'contact' => $request->contact
        ]);
        return response()->json(['message' => 'Supplier successfully updated']);

    }

    public function delete(Request $request)
    {
        Supplier::find($request->id)->delete();

        return response()->json(['message' => 'Supplier successfully deleted']);
    }

    public function restore(Request $request)
    {
        Supplier::withTrashed()->find($request->id)->restore();

        return response()->json(['message' => 'Supplier successfully restored']);
    }
}
