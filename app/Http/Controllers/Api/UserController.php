<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\User;
use Auth;

class UserController extends Controller
{
    public function login(Request $request){
    	// $username = $request->email;
    	// $password = bcrypt($request->password);
    	// $user = User::where('email',$username)->where('password',$password)->first();
    	$credentials = $request->only('email','password');
    	if(Auth::attempt($credentials)){
    		$token = Str::random(80);
    		Auth::user()->api_token = $token;
    		Auth::user()->save();
    		return response()->json(['token'=>$token],200); 
    		// $token = Hash::make($request->password);
    		// $user->api_token = $token;
    		// $user->save();
    		// return response()->json(['status',$token],200);
    	}else{
    		return response()->json(['status'=>'Email or Password is Wrong'], 403);
    	}
    }

    public function verify(Request $request){
    	echo $request->user();
    }
}
