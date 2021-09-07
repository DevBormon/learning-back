<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Stripe;
use App\Cart;
use App\Payment;
use App\Course;

class PaymentController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user= Auth::user();
    
            return $next($request);
        });
    }

    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'You have some error', 'data' => $validator->errors(), 'error' => true], 400);
        }
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $description = 'Cart Payment';
        $carts = Cart::where('user_id', $this->user->id)->first();
        

        if($carts != null){
            $amount = 0;
            $courses = [];
            foreach ($carts->cart_data as $item) {
                array_push($courses, $item['id']);
                $amount = $amount+$item['price'];
            }
            
            try
            {
                $charge = \Stripe\PaymentIntent::create([
                    'amount' => round($amount, 2) * 100,
                    'currency' => 'INR',
                    'description' => $description,
                    'payment_method' => $request->token,
                    'off_session' => true,
                    'confirm' => true,
                ]);
            } catch (\Stripe\Exception\CardException $e) {
                // Since it's a decline, \Stripe\Exception\CardException will be caught
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            } catch (\Stripe\Exception\RateLimitException $e) {
                // Too many requests made to the API too quickly
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Invalid parameters were supplied to Stripe's API
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            } catch (\Stripe\Exception\AuthenticationException $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                // Network communication with Stripe failed
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            } catch (Exception $e) {
                // Something else happened, completely unrelated to Stripe
                return response()->json(['msg' => $e->getError(), 'data' => [], 'error' => true],402);
            }
            
            if(!empty($charge)){
                if($charge['status'] == 'succeeded') {
                    $payment = Payment::insertGetId([
                        'transaction_id' =>  $charge->id,
                        'user_id' =>  $this->user->id,
                        'courses' => $courses,
                        'status' =>  'Success'
                    ]);
                                    
                    if($payment->id){
                        Cart::where('user_id', $this->user->id)->delete();
                    }
                }

                return response()->json(['msg' => 'Your Order is placed', 'data' => $payment, 'error' => false],200);
            } else {
                return response()->json(['msg' => 'Something went to wrong.', 'data' => [], 'error' => true],400);
            }           
        }

        return response()->json(['msg' => 'Cart is not available', 'data' => [], 'error' => true],402);
    }

    public function payStripe(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:paid,free',
            'name' => 'required_if:type,paid',
            'card_no' => 'required_if:type,paid',
            'expiry_month_year' => 'required_if:type,paid',
            'cvv' => 'required_if:type,paid',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'You have some error', 'data' => $validator->errors(), 'error' => true], 400);
        }

        
        
        $stripe = Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $carts = Cart::where('user_id', $this->user->id)->first();

        if($carts != null){
            $amount = 0;
            $courses = [];
            foreach ($carts->cart_data as $item) {
                array_push($courses, $item['id']);
                $amount = $amount + $item['price'];
            }

            if($request->type == 'free'){
                $payment = Payment::create([
                    'user_id' =>  $this->user->id,
                    'courses' => $courses,
                    'status' =>  'Success'
                ]);
                
                
                
                if($payment->id){
                    Cart::where('user_id', $this->user->id)->delete();
                }

                return response()->json(['msg' => 'Your Order is placed', 'data' => $payment, 'error' => false],200);
            }

            
            $expiry = explode ("/", $request->expiry_month_year); 
            $expiry_month = $expiry[0];
            $expiry_year = $expiry[1];

            
            try {
                $response = \Stripe\Token::create([
                    "card" => [
                        "number"    => $request->card_no,
                        "exp_month" => $expiry_month,
                        "exp_year"  => $expiry_year,
                        "cvc"       => $request->cvv
                    ]
                ]);
                $charge = \Stripe\Charge::create([
                    'card' => $response['id'],
                    'currency' => 'INR',
                    'amount' =>  round($amount, 2) * 100,
                    'description' => 'Cart Payment',
                ]);
                    
                if(!empty($charge)){
                    if($charge['status'] == 'succeeded') {                    
                        $payment = Payment::create([
                            'transaction_id' =>  $charge->id,
                            'user_id' =>  $this->user->id,
                            'courses' => $courses,
                            'status' =>  'Success'
                        ]);
                        
                        
                        
                        if($payment->id){
                            Cart::where('user_id', $this->user->id)->delete();
                        }                                    
                    }
                    return response()->json(['msg' => 'Your Order is placed', 'data' => $payment, 'error' => false],200);
     
                } else {
                    return response()->json(['msg' => 'Something went to wrong.', 'data' => [], 'error' => true],400);
                }
     
            }
            catch (Stripe\Exception\CardException $e) {
                return response()->json(['msg' => 'You have some error', 'data' => $e->getError()->message, 'error' => true],400);
            }
        }else{
            return response()->json(['msg' => 'Cart is not available', 'data' => [], 'error' => true],402);
        } 
    }

    public function myCourses()
    {
        $data = [];
        $payments = Payment::where('user_id', $this->user->id)->get();
        foreach ($payments as $payment) {
            foreach ($payment->courses as $course) {
                array_push($data, $course);
            }
        }

        $courses = Course::whereIn('id', $data)->get();
        $courses->makeVisible(['video','pdf']);
        
        return response()->json(['msg' => '', 'data' => $courses, 'error' => false],200);
    }

    public function getDownload(Request $request)
    {
        $course = Course::where('id', $request->id)->first();

        $file_name = '';
        $path = '';
        $type = '';
        if($request->type == 'pdf'){
            $path = 'public/pdfs/'. $course->pdf;
            $type = 'application/pdf';
            $file_name = $course->pdf;
        }else{
            $path = 'public/videos/'. $course->video;
            $type = 'video/mp4';
            $file_name = $course->video;
        }

        $headers = [
            "Content-disposition: attachment; filename=\"".$file_name."\"",
            "Content-Type: application/octet-stream"
        ];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        $new_file_name = strtolower(str_replace(' ', '_', $course->name)).'.'.$ext;
        
        
        return Storage::download($path, $new_file_name, $headers);


        
    } 

    
}
