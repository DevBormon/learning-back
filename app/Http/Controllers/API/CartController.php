<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Course;
use App\Cart;

class CartController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user= Auth::user();
    
            return $next($request);
        });
    }
    
    public function index()
    {
        $carts = Cart::where('user_id', $this->user->id)->first();
        $data = [];
        if($carts != null){
            $data = $carts->cart_data;
        }
        
        return response()->json(['msg' => '', 'data' => $data, 'error' => false],200);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'You have some error', 'data' => $validator->errors(), 'error' => true], 400);
        }

        $course = Course::where('id', $request->id)->first();
        
        if($course != null){
            $carts = Cart::where('user_id', $this->user->id)->first();
            if($carts != null){
                $array_data = $carts->cart_data; 
                $array_data[] = $course;
                $cart = Cart::where('user_id', $this->user->id)->update([
                    'cart_data' => $array_data
                ]);
            }else{
                $cart_id = sha1(Str::random(15).time());
                $data = [];
                $data [] = $course;

                $cart = Cart::create([
                    'id' => $cart_id,
                    'user_id' => $this->user->id,
                    'cart_data' => $data
                ]);
            }


            return response()->json(['msg' => 'Cart added successfully!', 'data' => $course, 'error' => false], 200);
        }else{
            return response()->json(['msg' => 'This course is not available', 'data' => [], 'error' => true], 400);
        }

    }
    
    public function destroy($id)
    {
        $cart = Cart::where('user_id', $this->user->id)->first();
        $array_data = $cart->cart_data;
        
        if(count($array_data) > 1){
            foreach ($array_data as $key => $value) {
                if($value['id'] == $id){
                    unset($array_data[$key]);
                }
            }
            $cart = Cart::where('user_id', $this->user->id)->update([
                'cart_data' => $array_data
            ]);
        }else{
            Cart::where('user_id', $this->user->id)->delete(); 
        }

        return response()->json(['msg' => 'Cart remove successfully!', 'data' => $id, 'error' => false], 200);
    }
}
