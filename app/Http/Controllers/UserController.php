<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Template;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Newsletter;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['sub_emails','cms_page', 'forgot_password_vendor','forgot_password','enquiry','change_password','update_account','login','register','addtocart','addtowishlist','get_cart','get_wishlist','view_order','delete_wishlist','delete_cart','wishlisttocart','update_download']]);
    }

    public function login(Request $request){
        $data = $request->all();
        if(isset($data['google_id'])){
            $userGCnt = User::where('google_id',$data['google_id'])->count();
            $userCnt = User::where('email',$data['email'])->where('google_id',null)->count();
            if($userGCnt > 0){

                $token_validaty = 24 * 60;

                $this->guard()->factory()->setTTL($token_validaty);

                $userCnt = User::where('google_id',$data['google_id'])->count();

                if(!$token = $this->guard()->attempt(["email"=>$data['google_email'],"password"=>$data['google_email']])){
                    return response()->json(['error'=> 'unauthorised','status'=> 401]);
                }
                return $this->respondWithToken($token);
            }else if($userCnt <= 0){
                $user = new User;
                $user->name = $data['google_name'];
                $user->email = $data['google_email'];
                $user->google_id = $data['google_id'];
                $user->profession = 'User';
                $user->password = bcrypt($data['google_email']);
                $user->save();
                //send confirmation mail
                $token_validaty = 24 * 60;

                $this->guard()->factory()->setTTL($token_validaty);

                $userCnt = User::where('google_id',$data['google_id'])->count();

                if(!$token = $this->guard()->attempt(["email"=>$data['google_email'],"password"=>$data['google_email']])){
                    return response()->json(['error'=> 'unauthorised','status'=> 401]);
                }

                $email = $data['google_email'];
                $messageData = ['email'=>$data['google_email'],'name'=>$data['google_name']];
                 Mail::send('emails.welcome',$messageData,function($message) use($email){
                        $message->to($email)->subject('Welcome to the Artaux family!- Artaux');
                });

                return $this->respondWithToken($token);
            }else{
                return response()->json(['message'=>'Your email is already exist please sign in using email and password.','status'=> 404]);
            }
        }else{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            }

            $token_validaty = 24 * 60;

            $this->guard()->factory()->setTTL($token_validaty);

            if(!$token = $this->guard()->attempt($validator->validated())){
                return response()->json(['error'=> 'unauthorised','status'=> 401]);
            }
            if($this->guard()->user()->status == 0 ){
                return response()->json(['message'=>'Your email is not verified please verify email first.','status'=> 404]);
            }
            return $this->respondWithToken($token);
        }

    }

    public function register(Request $request){
            $data = $request->all();
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|between:2,100',
                'email' => 'required|email|unique:users',
                'mobile' => 'required',
                'profession' => 'string|between:2,100',
                'password' => 'required|confirmed|min:6'
            ]);

            if($validator->fails()){
                return response()->json(['message'=>$validator->errors(),'status'=>422]);
            }

            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->mobile = $data['mobile'];
            $user->profession = $data['profession'];
            $user->password = bcrypt($data['password']);
            $user->save();
            //send confirmation mail
            $email = $data['email'];
            $messageData = ['email'=>$data['email'],'name'=>$data['name'],'code'=>base64_encode($data['email'])];
            Mail::send('emails.confirmation',$messageData,function($message) use($email){
                $message->to($email)->subject('Verify Email - Artaux');
            });
            return response()->json(['message'=>'Account created successfully.Thank you for signing up  on Artaux. A verification link has been sent to your email address. Please click on the link to verify your email address and activate the account', 'user' => $user,'status'=>200]);
    }

    public function change_password(Request $request){
        $data = $request->all();
        $user = User::where('id',$data['user_id'])->first();
        $credintials = array("email"=>$user->email,"password"=>$data['old_pwd']);
        // $credintials = $request->only('email','old_pwd');
        // dd($credintials);
    	if(Auth::guard('api')->attempt($credintials, $request->remember))
    	{
            if($data['comfirm_pwd'] == $data['new_pwd']){
                $passwords = bcrypt($data['new_pwd']);
            }else{
                return response()->json(['message'=> 'Comfirm and new Password are not same','status' => 402]);
            }
            User::where('id',$data['user_id'])->update(['password'=>$passwords]);
            return response()->json(['message'=>'Password Updated Successfully.', 'status' => 200]);
        }else{
            return response()->json(['message'=> 'Enter password is wrong','status' => 401]);
        }
    }

    public function cms_page($name){
        if($name == "privacy"){
        $page = DB::table('pages')->where('id',1)->first();
        }else if($name == "cookie"){
            $page = DB::table('pages')->where('id',2)->first();
        }else if($name == "refund"){
            $page = DB::table('pages')->where('id',3)->first();
        }else if($name == "agreement"){
            $page = DB::table('pages')->where('id',4)->first();
        }else if($name == "license"){
            $page = DB::table('pages')->where('id',5)->first();
        }else if($name == "intellectual"){
            $page = DB::table('pages')->where('id',6)->first();
        }else if($name == "tems_of_use"){
            $page = DB::table('pages')->where('id',7)->first();
        }
        return response()->json(['page'=>$page, 'status' => 200]);
    }


    public function forgot_password(Request $request){
        $data = $request->all();                            //getting all data
        $userCount = User::where('email',$data['email'])->count();
        $userdata = User::where('email',$data['email'])->first();
        if($userCount <= 0){
            return response()->json(['message'=> 'There is no user found','status' => 401]);
        }
		if($userdata['google_id'] != null)
		{
			return response()->json(['message'=> 'Google sign in users are not allowed to reset password','status' => 401]);
		}

        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $ran_pwd = substr($random, 0, 10);
        $password = bcrypt($ran_pwd);

        if($userCount > 0){
        User::where('email',$data['email'])->update(['password'=>$password]);
        $email = $data['email'];
        $messageData = ['email'=>$data['email'],'code'=>$ran_pwd];
        Mail::send('emails.forgot',$messageData,function($message) use($email){
            $message->to($email)->subject('Reset User Account Password Email - Artaux');
        });
        }
		
        return response()->json(['message'=>'An email has been sent to your email address with instructions to reset the password.', 'status' => 200]);
    }
	
	
	public function forgot_password_vendor(Request $request){
       $data = $request->all();
	   $vendorCount = Vendor::where('email',$data['email'])->count();
	   
	   if($vendorCount <= 0){
            return response()->json(['message'=> 'There is no user found','status' => 401]);
        }
	   
	    $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $ran_pwd = substr($random, 0, 10);
        $password = bcrypt($ran_pwd);
	   
	   if($vendorCount > 0){
            Vendor::where('email',$data['email'])->update(['password'=>$password]);
            $email = $data['email'];
            $messageData = ['email'=>$data['email'],'code'=>$ran_pwd];
            Mail::send('emails.forgot',$messageData,function($message) use($email){
                $message->to($email)->subject('Contributor Account Password Reset Email - Artaux');
            });
        }
		
		return response()->json(['message'=>'An email has been sent to your email address with instructions to reset the password.', 'status' => 200]);
	   
	}
	

    public function wishlisttocart($id){
        $wishlist = DB::table('wishlist')->where('id',$id)->first();

            $countProducts = DB::table('cart')->where(['product_id' => $wishlist->product_id,'user_email' => $wishlist->user_email])->count();
            if($countProducts>0){
                return response()->json(['message'=>'Product already added to cart', 'status' => 423]);
            }

           //  $INR_rate = 1;
           //  $GBP_rate = 102.93;
           //  $USD_rate = 72.85;
           //  $EURO_rate = 88.53;
           //  $DIRHAM_rate = 19.83;

           // if($data['currency'] == 'INR'){
           //      $data['price'] =  round($data['price']/$INR_rate, 2);
           //  }elseif($data['currency'] == 'USD'){
           //      $data['price'] =  round($data['price']/$USD_rate, 2);
           //  }elseif($data['currency'] == 'GBP'){
           //      $data['price'] =  round($data['price']/$GBP_rate, 2);
           //  }elseif($data['currency'] == 'EURO'){
           //      $data['price'] =  round($data['price']/$EURO_rate, 2);
           //  }elseif($data['currency'] == 'DIRHAM'){
           //      $data['price'] =  round($data['price']/$DIRHAM_rate, 2);
           //  }

            DB::table('cart')->insert(['product_id' => $wishlist->product_id,'product_name' => $wishlist->product_name,'price' => $wishlist->price,'currency' => $wishlist->currency,'user_email' => $wishlist->user_email,'vendor_id' => $wishlist->vendor_id,'discount' => $wishlist->discount,'image' => $wishlist->image]);

            return response()->json(['message'=>'Product  added to cart', 'status' => 200]);

    }

    public function addtocart(Request $request){
        $data = $request->all();

            $countProducts = DB::table('cart')->where(['product_id' => $data['product_id'],'user_email' => $data['user_email']])->count();
            if($countProducts>0){
                return response()->json(['message'=>'Product already added to cart', 'status' => 423]);
            }

           //  $INR_rate = 1;
           //  $GBP_rate = 102.93;
           //  $USD_rate = 72.85;
           //  $EURO_rate = 88.53;
           //  $DIRHAM_rate = 19.83;

           // if($data['currency'] == 'INR'){
           //      $data['price'] =  round($data['price']/$INR_rate, 2);
           //  }elseif($data['currency'] == 'USD'){
           //      $data['price'] =  round($data['price']/$USD_rate, 2);
           //  }elseif($data['currency'] == 'GBP'){
           //      $data['price'] =  round($data['price']/$GBP_rate, 2);
           //  }elseif($data['currency'] == 'EURO'){
           //      $data['price'] =  round($data['price']/$EURO_rate, 2);
           //  }elseif($data['currency'] == 'DIRHAM'){
           //      $data['price'] =  round($data['price']/$DIRHAM_rate, 2);
           //  }

            DB::table('cart')->insert(['product_id' => $data['product_id'],'product_name' => $data['product_name'],'price' => $data['price'],'currency' => $data['currency'],'user_email' => $data['user_email'],'vendor_id' => $data['vendor_id'],'discount' => $data['discount'],'image' => $data['image']]);

            return response()->json(['message'=>'Product  added to cart', 'status' => 200]);

    }

    public function addtowishlist(Request $request){
        $data = $request->all();

            $countProducts = DB::table('wishlist')->where(['product_id' => $data['product_id'],'user_email' => $data['user_email']])->count();
            if($countProducts>0){
                return response()->json(['message'=>'Product already added to wishlist', 'status' => 423]);
            }

           //  $INR_rate = 1;
           //  $GBP_rate = 102.93;
           //  $USD_rate = 72.85;
           //  $EURO_rate = 88.53;
           //  $DIRHAM_rate = 19.83;

           // if($data['currency'] == 'INR'){
           //      $data['price'] =  round($data['price']/$INR_rate, 2);
           //  }elseif($data['currency'] == 'USD'){
           //      $data['price'] =  round($data['price']/$USD_rate, 2);
           //  }elseif($data['currency'] == 'GBP'){
           //      $data['price'] =  round($data['price']/$GBP_rate, 2);
           //  }elseif($data['currency'] == 'EURO'){
           //      $data['price'] =  round($data['price']/$EURO_rate, 2);
           //  }elseif($data['currency'] == 'DIRHAM'){
           //      $data['price'] =  round($data['price']/$DIRHAM_rate, 2);
           //  }

            DB::table('wishlist')->insert(['product_id' => $data['product_id'],'product_name' => $data['product_name'],'price' => $data['price'],'currency' => $data['currency'],'user_email' => $data['user_email'],'vendor_id' => $data['vendor_id'],'discount' => $data['discount'],'image' => $data['image']]);

            return response()->json(['message'=>'Product  added to wishlist', 'status' => 200]);

    }

    public function get_wishlist(Request $request,$email){
            $cart = DB::table('wishlist')->where('user_email',$email)->get();
            $total_price = 0;
            if(!empty($cart) || $cart != null ){
                foreach($cart as $template){
                    $total_price=$total_price+$template->price;
                }
            }
            $cartcount = DB::table('wishlist')->where('user_email',$email)->count();
            return response()->json(['cart'=> $cart,'cartCount'=>$cartcount,'total_price'=>$total_price, 'status' => 200]);

    }

    public function delete_wishlist(Request $request,$id){
        DB::table('wishlist')->where('id',$id)->delete();
        return response()->json(['message'=>'Product  removed from wishlist', 'status' => 200]);
    }



    public function get_cart(Request $request,$email){
            $cart = DB::table('cart')->where('user_email',$email)->get();
            $total_price = 0;
            if(!empty($cart) || $cart != null ){
                foreach($cart as $template){
                    $total_price=$total_price+$template->price;
                }
            }
            $cartcount = DB::table('cart')->where('user_email',$email)->count();
            $wishlistCount = DB::table('wishlist')->where('user_email',$email)->count();
            return response()->json(['cart'=> $cart,'cartCount'=>$cartcount,'wishlistCount'=>$wishlistCount,'total_price'=>$total_price, 'status' => 200]);

    }

    public function delete_cart(Request $request,$id){
            $cart = DB::table('cart')->where('id',$id)->delete();
            return response()->json(['message'=>'Product  removed from cart', 'status' => 200]);
    }



    public function place_order(Request $request){
            $data = $request->all();
            $data['state'] = strtolower( $data['state']);

            User::where('email',$data['user_email'])->update(['city' => $data['city'],'country' => $data['country'],'state' => $data['state']]);
            $current_month = date('m');
            $current_year = date('y');
            $last_order = DB::table('orders')->orderBy('id', 'desc')->first();
            if(isset($last_order)){
                if(date('m',strtotime($last_order->created_at)) == $current_month){
                    $inv_id = substr($last_order->invoice_id, 3);
                    $inv_id = (int)$inv_id + 1;
                $invoiceId =  'A'.$current_year.$current_month.sprintf("%04d",$inv_id);
                // dd($invoiceId);
                }else{
                    $invoiceId = 'A'.$current_year.$current_month.'0001';
                }
            }else{
                $invoiceId = 'A'.$current_year.$current_month.'0001';
            }

            $order = DB::table('orders')->insert(['created_at'=>date('Y-m-d H:i:s'),'invoice_id'=>$invoiceId,'user_id' => $data['user_id'],'user_email' => $data['user_email'],'name' => $data['name'],'currency' => $data['currency'],'email' => $data['email'],'city' => $data['city'],'country' => $data['country'],'state' => $data['state'],'grand_total' => $data['totalPrice'],'payment_method' => $data['payment_method'],'payment_id' => $data['payment_id']]);

            $last_id = DB::table('orders')->orderByDesc('id')->first();

            $cart = DB::table('cart')->where(['user_email' => $data['user_email']])->get();

            foreach($cart as $template){
                DB::table('orders_products')->insert(['invoice_id'=>$invoiceId,'user_id' => $data['user_id'],'order_id'=> $last_id->id,'product_id' => $template->product_id,'product_name' => $template->product_name,'discount' => $template->discount,'product_price' => $template->price,'currency' => $data['currency'],'email' => $data['user_email'],'vendor_id' => $template->vendor_id]);
                DB::table('cart')->where(['id' => $template->id])->delete();
                $vendor_id[] = $template->vendor_id;
            }

            $vendor_id = array_unique($vendor_id);

            foreach($vendor_id as $vendor){
                $order_price = DB::table('orders_products')->where('order_id',$last_id->id)->where('vendor_id',$vendor)->get();
                $vendor_price=0;
                foreach($order_price as $price){
                    $vendor_price = $price->product_price + $vendor_price;
                }
                 $test = DB::table('contributors_orders')->insert(['created_at'=>date('Y-m-d H:i:s'),'order_id'=> $last_id->id,'user_email' => $data['user_email'],'name' => $data['name'],'vendor_id'=>$vendor,'order_status'=>'unpaid','currency' => $data['currency'],'grand_total'=>$vendor_price]);

                $vendor_detail = DB::table('vendors')->where('id',$vendor)->first();
                $email =  $vendor_detail->email;
                $messageData = ['name'=>$vendor_detail->name,'order_id'=>$last_id->id];
                Mail::send('emails.order_placed_contributor',$messageData,function($message) use($email){
                    $message->to($email)->subject(' Hurray! Another one shipped! - Artaux');
                });
            }

            // dd($test);

            // send confirmation mail
            $email =  $data['user_email'];
            $messageData = ['name'=>$data['name'],'order_id'=>$last_id->id];
            Mail::send('emails.order_comfirmation',$messageData,function($message) use($email){
                $message->to($email)->subject('Order Confirmation - Artaux');
            });


            $admin_email = 'support@attaux.io';
            Mail::send('emails.order_status',$messageData,function($message) use($admin_email){
                $message->to($admin_email)->subject('Get New Order - Artaux');
            });


            return response()->json(['message'=>'Product  added to cart', 'status' => 200]);

    }

    public function view_order(Request $request,$id){
            $data = $request->all();

            // $orders = DB::table('orders')->where('id',$id)->get;
            $orders = DB::table('orders_products')->where('user_id',$id)->where('download','!=',0)->orderByDesc('id')->get();

            foreach($orders as $key => $val){
                $template = Template::where('id',$val->product_id)->first();
                $orders[$key]->product_file = $template->zip;
                $orders[$key]->product_image = $template->image;
                $orders[$key]->product_price = $template->price;
                $orders[$key]->product_filetype = $template->file_type;
            }

            // $orders = Template::join('orders_products','orders_products.product_id','=', 'templates.id')->where('orders_products.user_id', $id)->where('templates.id', 'orders_products.product_id')->get(['templates.*', 'orders_products.order_id']);
            return response()->json(['orders'=>$orders, 'status' => 200]);

    }

    public function view_user_orders(Request $request,$email){
            $data = $request->all();
            $orders = DB::table('orders')->where('user_email',$email)->orderByDesc('id')->get();
            foreach($orders as $key => $val){
                $templates = DB::table('orders_products')->where('order_id',$val->id)->get();
                $template_names = '';
                foreach($templates as $template){
                    if ($template_names) $template_names .= ', ';
                    $template_names .= $template->product_name;
                }
                $orders[$key]->template_names = $template_names;
            }
            return response()->json(['orders'=>$orders, 'status' => 200]);

    }

    public function update_download(Request $request){
            $data = $request->all();
            $template = DB::table('orders_products')->where('id',$data['id'])->first();
            if($template->download == 2){
                $download_count = 1;
            }elseif($template->download == 1){
                $download_count = 0;
            }else{
                $download_count = 0;
            }
            DB::table('orders_products')->where('id',$data['id'])->update(['download'=>$download_count,'updated_at'=>$template->updated_at]);
            return response()->json(['message'=>'', 'status' => 200]);

    }

    public function logout(Request $request){
        $this->guard()->logout();
        return response()->json(['message' => 'User logged out successfully.']);
    }

    public function user(){
        return response()->json(Auth::user());
    }

    public function update_account(Request $request){
        $data = $request->all();
        if($data['profession'] == null){
            $data['profession']="";
        }
        DB::table('users')->where('id',$data['user_id'])->update(['name'=>$data['name'],'email'=>$data['email'],'profession'=>$data['profession']]);
        return response()->json(['message'=>'Your account is updated', 'status' => 200]);
    }

    public function enquiry(Request $request){
        $data = $request->all();
        DB::table('enquiries')->insert(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone'],'subject'=>$data['subject'],'message'=>$data['message']]);
        $email = 'info@artaux.io';
        $messageData = ['email'=>$data['email'],'name'=>$data['name'],'messsage'=>$data['message'],'subject'=>$data['subject']];
         Mail::send('emails.contact',$messageData,function($message) use($email){
                $message->to($email)->subject('Artaux Contact Enquiry');
        });
        return response()->json(['message'=>'Enquiry added successfully.', 'status' => 200]);
    }


    public function sub_emails(Request $request){
        $data = $request->all();
        if(!Newsletter::isSubscribed($data['email'])){
	        Newsletter::subscribe($data['email']);
            DB::table('sub_emails')->insert(['email'=>$data['email']]);
            return response()->json(['message'=>'Subscribe newsletter successfully.', 'status' => 200]);
	    }else{
	    	return response()->json(['message'=>'Email already exist in newsletter', 'status' => 401]);
	    }

    }

    protected function respondWithToken($token){
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token_validaty' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    public function guard(){
        return Auth::guard();
    }
}
