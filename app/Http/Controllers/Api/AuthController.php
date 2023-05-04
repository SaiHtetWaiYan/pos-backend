<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\Userinfo;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator =$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised(),
            ],
        ]);

        $user = User::create([
            'name' => $validator['name'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $userInfo = Userinfo::create([
            'user_id' => $user->id
        ]);
        return response()->json(['token' => $token,'user' => $user,'userInfo' => $userInfo , 'message' => 'Successfully registered'],200);
    }

    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $token=  $user->createToken('auth_token')->plainTextToken;

            $userInfo = Userinfo::where('user_id', $user->id)->first();
            return response()->json(['token' => $token,'user' => $user,'userInfo' => $userInfo , 'message' => 'Login Successfully'],200);
        }
        else{
            return response()->json(['error'=>'Ooops! Something Wrong.'], 401);
        }
    }

    public function profileUpdate(Request $request)
    {
        $user = User::where('id',$request->id)->first(); // Find the user by their ID.

        // Validate the name field from the request.
        $validator= $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $user->update([
            'name' => $validator['name']    // Update the user's name with the validated name value.
        ]);

        if (Auth::user()->email == $request->email) // Check if the email in the request is the same as the current user's email.
        {
            $validator =$request->validate([
                'email' => 'required|string|email|max:255', // Validate the email field from the request.
            ]);
            $user->update([
                'email' => $validator['email']  // Update the user's email with the validated email value.
            ]);
        }
        else{
            $validator = $request->validate([
                'email' => 'required|string|email|max:255|unique:users',    // Validate the email field from the request, ensuring it is unique.
            ]);
            $user->update([
                'email' => $validator['email']  // Update the user's email with the validated email value.
            ]);
        }

        if ($request->hasFile('photo')) // Check if the request has a photo file.
        {
            $request->validate([
                'photo' => 'mimes:jpg,jpeg,png|max:2048' // Validate that the photo file is of an allowed mime type.
            ]);
            $imageName = time().'.'.$request->photo->getClientOriginalExtension();  // Generate a unique name for the photo file.
            $request->photo->move(public_path('users'), $imageName);    // Move the photo file to the public/users directory.
            $user->update([
                'photo' => $imageName   // Update the user's photo with the new file name.
            ]);
        }

        if($request->current_password || $request->new_password )
        {
            $request->validate([
                'current_password' => 'required',   // Validate that the request includes the user's current password.
            ]);
            if(!Hash::check($request->current_password, Auth::user()->password)){   // Check if the user's current password matches the stored hash.
                return response()->json(["passwordError"=>"Old Password Doesn't match!" ],401); // If not, return an error response.
            }
            $request->validate([
                'new_password' => ['required', 'confirmed', Password::min(8)    // Validate that the new password meets the specified requirements.
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                ],
            ]);

            $user->update(['password'=> Hash::make($request->new_password)]); // Update the user's password with the new password hash.
        }
        return response()->json(['message'=> 'Profile Update Successfully' , 'user' => $user], 200); // Return a success response with the updated user object.


    }

    public function personalInfoUpdate(Request $request)
    {
        $userInfo = Userinfo::where('user_id', $request->user_id)->first();
        $userInfo->update([
            'name' => $request->name,
            'shop_name' => $request->shop_name,
            'phone_number' => $request->phone_number,
            'street_address' => $request->street_address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country
        ]);

        return response()->json(['message' => 'Personal Info update successfully' , 'userInfo' => $userInfo], 200);
    }

    public function AccountDelete(Request $request)
    {
        // Check if the provided password matches the user's current password
        if(!Hash::check($request->password, auth()->user()->password)){
            return response()->json(["passwordError"=>"Password Doesn't match!" ],401);
        }

        // Delete user-related data from other tables
        Userinfo::where('user_id', $request->id)->delete();
        Brand::where('user_id',$request->id)->forceDelete();
        Category::where('user_id',$request->id)->forceDelete();
        Supplier::where('user_id',$request->id)->forceDelete();

        // Revoke all tokens associated with the user
        auth()->user()->tokens()->delete();

        // Delete the user account
        User::find($request->id)->forceDelete();

        return response()->json(['message' => 'Successfully account deleted'],200);

    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out'],200);
    }




}
