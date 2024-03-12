<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Vendor;
use App\Models\Template;
use App\Models\Category;
use App\Models\Multiimage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('vendor_auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request){
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
            return response()->json(['error'=> 'unauthorised','message'=>'Please check email and password', "status"=>422]);
        }

        return $this->respondWithToken($token);


    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|unique:vendors',
            'username' => 'required|string|unique:vendors',
            'nationality' => 'required|string',
            'mobile' => 'required|min:11|numeric',
            'profession' => 'string|between:2,100',
            // 'password' => 'required|confirmed|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $user = new Vendor;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->country_code = $data['country_code'];
        $user->mobile = $data['mobile'];
        $user->profession = $data['profession'];
        $user->nationality = $data['nationality'];
        $user->username = $data['username'];

        // Upload Images
        $file = $data['photo_id_image'];
        $extension = $file->getClientOriginalExtension();
        $fileName = rand(111,99999).'.'.$extension;
        $path  = 'images/photo_id/';
        $file->move($path,$fileName);
        $user->photo_image=$fileName;

        // Upload File
        if(!empty($data['photo_id_file'])){
            $file = $data['photo_id_file'];
            $extension = $file->getClientOriginalExtension();
            $fileName1 = rand(111,99999).'.'.$extension;
            $path1  = 'images/photo_id/';
            $file->move($path1,$fileName1);
            $user->photo_file =$fileName1;
        }



        $user->password = bcrypt($data['password']);
        $user->save();

        //send confirmation mail
        $email = $data['email'];
        $messageData = ['email'=>$data['email'],'name'=>$data['name'],'email'=>$data['email']];
        Mail::send('emails.welcome_contributor',$messageData,function($message) use($email){
            $message->to($email)->subject('Preparing for take off - Artaux')->attach('images/EULA_Artaux.docx');
        });

        $admin_email = 'support@attaux.io';
        Mail::send('emails.new_contributor',$messageData,function($message) use($admin_email){
            $message->to($admin_email)->subject('New Contributor Register - Artaux');
        });

        return response()->json(['message'=>'Account created successfully.Thank you for signing up  on Artaux. Your profile is under review. Once your profile has been approved you can start uploading your products on to the website', 'user' => $user,'status'=>200]);
    }

    public function change_password(Request $request){
        $data = $request->all();
        $credintials = $request->only('email','password');
    	if(Auth::guard('vendor_api')->attempt($credintials, $request->remember))
    	{
            if($data['comfirm_pwd'] == $data['new_pwd']){
                $passwords = bcrypt($data['new_pwd']);
            }else{
                return response()->json(['message'=> 'Comfirm & New Password Are Not Same','status' => 402]);
            }
            Vendor::where('id',$data['id'])->update(['password'=>$passwords]);
            return response()->json(['message'=>'Password Updated Successfully.', 'status' => 200]);
        }else{
            return response()->json(['message'=> 'Enter Password Is Wrong','status' => 401]);
        }
    }

    public function edit_profile(Request $request){
        $data = $request->all();

        if(empty($data['email'])){
            $data['email'] = '';
        }

        if(empty($data['name'])){
            $data['name'] = '';
        }
        // dd($data['name']);
        Vendor::where('id',$data['id'])->update(['email'=>$data['email'],'name'=>$data['name']]);
        return response()->json(['message'=>'Profile Updated Successfully.', 'status' => 200]);
    }

    public function logout(Request $request){
        $this->guard()->logout();
        return response()->json(['message' => 'User logged out successfully.']);
    }

    public function user(){
        return response()->json(Auth::user());
    }

    public function view_category(){
        $category = Category::get();
        $subcategory = Category::where('main_cat','!=',0)->get();
        $extension = DB::table('file_extensions')->get();
        return response()->json(['category'=>$category,'subcategory'=>$subcategory,'extension'=>$extension]);
    }


    public function add_template(Request $request){
        // return response()->json(['message' => 'Template add successfully.','status'=>200]);
        
		
		if(!$request->hasFile('image')) {
            return response()->json(['message'=>'upload_file_not_found'], 400);
        }
	
        
		$data = $request->all();
        $template = new Template;
            if(empty($data['currency'])){
                $data['currency'] == '';
            }
            if(empty($data['price'])){
                $data['price'] == '';
            }
			 if(($data['desc']) == null){
                $data['desc'] = "<p>Description content not added</p>";
            }
            if(empty($data['add_desc'])){
                $data['add_desc'] = "<p>Compatiblity content not added</p>";
            }
			
            // Upload zip file Snippet
            $file = $data['zip'];
            $extension = $file->getClientOriginalExtension();
            $fileName = rand(111,99999).'.'.$extension;
            $path  = 'images/template/';
            $file->move($path,$fileName);
            $template->zip=$fileName;
             /*End for Zip Upload*/
			 
            // Upload Images file snippet
            $file = $data['image'];
            $extension = $file->getClientOriginalExtension();
            $fileName1 = rand(111,99999).'.'.$extension;
            $path1  = 'images/template/';
            $file->move($path1,$fileName1);
            $template->image =$fileName1;
             
			 //Multiple Image Upload 
			 
			 
			 $files = $data['multimage'];
                foreach($files as $file){
                    // Upload Images after Resize
                    //$image = new ProductsImage;
                    $extension = $file->getClientOriginalExtension();
                    $fileName = rand(11111,99999999999).'.'.$extension;                  

                    $large_image_path  = 'images/multiple/'.$fileName;
                    //$medium_image_path = 'images/backend_images/products/medium/'.$fileName;
                    //$small_image_path  = 'images/backend_images/products/small/'.$fileName;  

                    Image::make($file)->save($large_image_path);
                    //Image::make($file)->resize(500, 500)->save($medium_image_path);
                    //Image::make($file)->resize(300, 300)->save($small_image_path);

                   // $image->images = $fileName;
                   // $image->product_id = $product_id;
                   // $image->save();
                }
			 
			 
			 /*Multiple File Image Ends*/


            //    return response()->json(['message' => 'Template add successfully.','status'=>200]);
            $template->category= $data['cat_id'];
            $template->title= $data['title'];
            $template->description= $data['desc'];
            $template->add_desc= $data['add_desc'];
            $template->vendor_id= $data['vendor_id'];
            $template->tags= $data['tags'];
            $template->sg_price= $data['price'];
            $template->currency= $data['currency'];
            $template->file_type= $data['file_type'];
			$template->youtube = $data['youtube'];
            //    return response()->json(['message' => 'Template add successfully.','status'=>200]);
            $template->save();

            $vendor = Vendor::where('id',$data['vendor_id'])->first();
            $email = $vendor->email;
            $messageData = ['email'=>$vendor->email,'name'=>$vendor->name];
            Mail::send('emails.under_review',$messageData,function($message) use($email){
                $message->to($email)->subject('Product Under Review - Artaux');
            });


        return response()->json(['message' => 'Digital assets add successfully.','status'=>200]);
    }


    public function edit_template(Request $request){
            $data = $request->all();
            // dd( $data);
            if(empty($data['cat_id'])){
                $data['cat_id'] = null;
            }
            if(empty($data['title'])){
                $data['title'] = null;
            }
            if(empty($data['desc'])){
                $data['desc'] = null;
            }
            if(empty($data['add_desc'])){
                $data['add_desc'] = null;
            }
            if(empty($data['file_type'])){
                $data['file_type'] = null;
            }
            if(empty($data['tags'])){
                $data['tags'] = '';
            }
            if(empty($data['currency'])){
                $data['currency'] = '';
            }
            if(empty($data['price'])){
                $data['price'] = '';
            }
            if($data['zip'] == "null" || empty($data['zip'])){
             $template=Template::where('id',$data['id'])->first();
            $zip=$template->zip;
            }else{
            $file = $data['zip'];
            $extension = $file->getClientOriginalExtension();
            $fileName = rand(111,99999).'.'.$extension;
            $path  = 'images/template/';
            $file->move($path,$fileName);
            $zip=$fileName;
            }

            if($data['image'] == "null" || empty($data['image'])){
             $template=Template::where('id',$data['id'])->first();
            $image=$template->image;
            }else{
            $file1 = $data['image'];
            $extension1 = $file1->getClientOriginalExtension();
            $fileName1 = rand(111,99999).'.'.$extension1;
            $path  = 'images/template/';
            $file1->move($path,$fileName1);
            $image=$fileName1;
            }

            Template::where('id',$data['id'])->update(['sg_price'=>$data['price'],'currency'=>$data['currency'],'category'=>$data['cat_id'],'title'=>$data['title'],'description'=>$data['desc'],'add_desc'=>$data['add_desc'],'file_type'=>$data['file_type'],'image'=>$image,'zip'=>$zip,'youtube'=>$data['youtube'],'tags'=>$data['tags'],'approve'=>0,'status'=>0]);
            $vendor = Vendor::where('id',$data['vendor_id'])->first();
            $email = $vendor->email;
            $messageData = ['email'=>$vendor->email,'name'=>$vendor->name];
            Mail::send('emails.under_review',$messageData,function($message) use($email){
                $message->to($email)->subject('Product Under Review - Artaux');
            });

        return response()->json(['message' => 'Digital assets edit successfully.','status'=>200]);
    }

    public function edit_view_template(Request $request,$id){

/*
        $templates = Template::where('id',$id)->first();

        $category = Category::get();
        $extension = DB::table('file_extensions')->get();
        return response()->json(['templates'=>$templates,'category'=>$category,'extension'=>$extension]);
  */
   $templates = Template::where('id',$id)->first();
        $category = Category::get();
		$subcategory = Category::where('main_cat','!=',0)->get();
        $extension = DB::table('file_extensions')->get();
        return response()->json(['subcategory'=>$subcategory,'templates'=>$templates,'category'=>$category,'extension'=>$extension]);
     

   }

    public function view_template(){
        $templates = Template::where('vendor_id',Auth::user()->id)->where('approve',0)->get();
        foreach($templates as $key => $val){
        $category = Category::where(['id'=>$val->category])->first();
        if(isset($category)){
            $templates[$key]->category_name = $category->name;
        }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function view_orders(){
        $templates = DB::table('orders_products')->where('vendor_id',Auth::user()->id)->get();
        if(count($templates)){
            foreach($templates as $template){
                $ids[]= $template->order_id;
            }
        }else{
            $ids = array();
        }
        // dd($ids);
        $orders = DB::table('orders')->where('order_status','unpaid')->whereIn('id', $ids)->get();

        foreach($orders as $key => $val){
            $vendor_id =  Auth::user()->id;
            $price = DB::table('contributors_orders')->where(['vendor_id'=>$vendor_id,'order_id'=>$val->id])->first();
            // dd($price);
            $product_detail = DB::table('orders_products')->where(['vendor_id'=>$vendor_id,'order_id'=>$val->id])->first();
            $orders[$key]->final_price = $price->grand_total;
            $orders[$key]->product_name = $product_detail->product_name;
            $orders[$key]->product_id = $product_detail->product_id;
        }
        // dd($orders);

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }

    public function view_paid_orders(){
        $templates = DB::table('orders_products')->where('vendor_id',Auth::user()->id)->get();

        if(count($templates)){
            foreach($templates as $template){
                $ids[]= $template->order_id;
            }
        }else{
            $ids = array();
        }
        $orders = DB::table('orders')->where('order_status','paid')->whereIn('id', $ids)->get();

        foreach($orders as $key => $val){
            $price = DB::table('contributors_orders')->where('order_id',$val->id)->where('vendor_id',Auth::user()->id)->first();
            $orders[$key]->final_price = $price->grand_total;
            
            $product_detail = DB::table('orders_products')->where(['vendor_id'=>Auth::user()->id,'order_id'=>$val->id])->first();
            $orders[$key]->product_id = $product_detail->product_id;
            $orders[$key]->product_name = $product_detail->product_name;
        }
        // dd($orders);

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }

     public function view_order($id){
        $templates = DB::table('orders_products')->where('order_id',$id)->where('vendor_id',Auth::user()->id)->get();
        $final_total = 0;
        foreach($templates as $key => $val){
            $template = Template::where(['id'=>$val->product_id])->first();
            $vendor = DB::table('vendors')->where('id',$val->vendor_id)->first();
            if(isset($template)){
                $templates[$key]->file_type = $template->file_type;
                $templates[$key]->vendor_name = $vendor->name;
                $final_total = $val->product_price + $final_total;
            }
        }
        $gst_amount = ($final_total*18)/100;

        $orders = DB::table('orders')->where('id', $id)->first();
        // if($orders->currency == "INR"){
        //     $final_total = $gst_amount + $final_total;
        //  }
        // dd($orders);

        return response()->json(['templates'=>$templates,'final_total'=>$final_total,'orders'=>$orders,'status'=>200]);
    }

     public function purchase_report(){
       $orders = DB::table('orders_products')->where('vendor_id',Auth::user()->id)->get();
       foreach($orders as $key => $val){
        $template_names = DB::table('templates')->where('id',$val->product_id)->first();
            $orders[$key]->template_name = $template_names->title;
        }
        return response()->json(['orders'=>$orders]);
    }

     public function purchase_filter_report($fromdate,$todate){
       $orders = DB::table('orders_products')->whereDate('updated_at','>=',$fromdate)->whereDate('updated_at','<=',$todate)->where('vendor_id',Auth::user()->id)->get();
       foreach($orders as $key => $val){
        $template_names = DB::table('templates')->where('id',$val->product_id)->first();
            $orders[$key]->template_name = $template_names->title;
        }
        // dd($orders);
        return response()->json(['orders'=>$orders]);
    }

    public function view_approve_template(){
        $templates = Template::where('vendor_id',Auth::user()->id)->where('approve',1)->get();
        foreach($templates as $key => $val){
        $category = Category::where(['id'=>$val->category])->first();
        if(isset($category)){
            $templates[$key]->category_name = $category->name;
        }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function delete_template(Request $request){
        $data = $request->all();
        Template::where('id',$data['id'])->delete();
        return response()->json(['message' => 'Digital assets deleted successfully.','status'=>200]);
    }

    public function delete_template_request(Request $request){
        $data = $request->all();
        Template::where('id',$data['id'])->update(['delete_request'=>1,'reason'=>$data['reason']]);
        return response()->json(['message' => 'Digital assets deleted request send successfully.','status'=>200]);
    }

    public function view_bank_details($id){
        $details = DB::table('bank_details')->where('vendor_id',$id)->first();
        return response()->json(['details' => $details,'status'=>200]);
    }

    public function account_details(Request $request){
        $data = $request->all();
        $detail_cnt = DB::table('bank_details')->where('vendor_id',$data['id'])->count();
        if(empty($data['bank_name'])){
            $data['bank_name'] = '';
        }
        if(empty($data['branch_name'])){
            $data['branch_name'] = '';
        }
        if(empty($data['ifsc'])){
            $data['ifsc'] = '';
        }
        if(empty($data['acc_no'])){
            $data['acc_no'] = '';
        }
        if(empty($data['name'])){
            $data['name'] = '';
        }
        if(empty($data['email'])){
            $data['email'] = '';
        }
        if(empty($data['upi_id'])){
            $data['upi_id'] = '';
        }
        if($detail_cnt > 0){

            DB::table('bank_details')->where('vendor_id',$data['id'])->update(['bank_name'=>$data['bank_name'],'branch'=>$data['branch_name'],'ifsc'=>$data['ifsc'],'acc_no'=>$data['acc_no'],'name'=>$data['name'],'email'=>$data['email'],'upi_id'=>$data['upi_id']]);
        }else{
            DB::table('bank_details')->insert(['vendor_id'=>$data['id'],'bank_name'=>$data['bank_name'],'branch'=>$data['branch_name'],'ifsc'=>$data['ifsc'],'acc_no'=>$data['acc_no'],'name'=>$data['name'],'email'=>$data['email'],'upi_id'=>$data['upi_id']]);
        }
        return response()->json(['message' => 'Account details added successfully.','status'=>200]);
    }

    protected function respondWithToken($token){
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token_validaty' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    public function guard(){
        return Auth::guard('vendor_api');
    }
}
