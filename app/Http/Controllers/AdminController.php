<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Template;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Page;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function __construct(){
        $this->middleware('admin_auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json(['message'=>$validator->errors(), 'status' =>422]);
        }

        $token_validaty = 24 * 60;

        $this->guard()->factory()->setTTL($token_validaty);

        if(!$token = $this->guard()->attempt($validator->validated())){
            return response()->json(['error'=> 'unauthorised','message'=>'Please check email and password','status' => 401]);
        }
        return $this->respondWithToken($token);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6'
        ]);

        if($validator->fails()){
            return response()->json(['message'=>$validator->errors(), 'status' =>422]);
        }

        $user = Admin::create(
            array_merge(
                $validator->validate(),
                ['password' => bcrypt($request->password)]
            )
        );

        return response()->json(['message'=>'User created Successfully.', 'user' => $user]);
    }

    public function change_password(Request $request){
        $data = $request->all();
        $credintials = $request->only('email','password');
    	if(Auth::guard('admin_api')->attempt($credintials, $request->remember))
    	{




        if($data['comfirm_pwd'] == $data['new_pwd']){
            $passwords = bcrypt($data['new_pwd']);
        }else{
            return response()->json(['message'=> 'Comfirm and new Password are not same','status' => 402]);
        }
        Admin::where('id',$data['id'])->update(['password'=>$passwords]);
        return response()->json(['message'=>'Password Updated Successfully.', 'status' => 200]);
        }else{
            return response()->json(['message'=> 'Enter password is wrong','status' => 401]);
        }
    }

    public function logout(Request $request){
        $this->guard()->logout();
        return response()->json(['message' => 'User logged out successfully.']);
    }

    public function user(){
        return response()->json(Auth::user());
    }

    public function add_category(Request $request){
        $validator = Validator::make($request->all(),[
            'main_cat' => 'required|string',
            'name' => 'required|string|between:2,100'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $user = Category::create(
            array_merge(
                $validator->validate()
            )
        );
        return response()->json(['message' => 'Category add successfully.','status'=>200]);
    }

    public function view_main_category(){
        $category =  Category::where('main_cat',0)->get();
        return response()->json(['category'=>$category]);
    }
    public function view_category(){
        $category =  Category::get();
        return response()->json(['category'=>$category]);
    }

    public function edit_view_category(Request $request,$id){
        $categories = Category::where('id',$id)->first();
        $category = Category::where('main_cat',0)->get();
        return response()->json(['category'=>$category,'categories'=>$categories]);
    }

    public function edit_category(Request $request){
        $data = $request->all();
            // dd( $data);
            if(empty($data['main_cat'])){
                $data['main_cat'] == null;
            }

            if(empty($data['name'])){
                $data['name'] == null;
            }
            Category::where('id',$data['id'])->update(['name'=>$data['name'],'main_cat'=>$data['main_cat']]);
            return response()->json(['message' => 'Category edited successfully.','status'=>200]);
    }

    public function view_users(){
        $users = User::get();
        foreach($users as $key => $val){
            $vendor = Vendor::where(['id'=>$val->vendor_id])->first();
            if($val->status == 1){
                $users[$key]->active = 'checked';
                $users[$key]->status_value = 0;
            }else{
                $users[$key]->active = '';
                $users[$key]->status_value = 1;
            }
            }
        return response()->json(['users'=>$users]);
    }

/*the function will activate user and deactivate user*/
    public function users_status(Request $request){
        $data = $request->all();
		$UserData = User::where('id',$data['id'])->first();
		
		if($data['status'] == 'true')
		{
			User::where('id',$data['id'])->update(['status'=>$data['status']]);
            $email =  $UserData['email'];
            $messageData = ['name'=>$UserData->name];
            Mail::send('emails.active_account',$messageData,function($message) use($email){
                $message->to($email)->subject(' Its on!- Artaux Account Activated');
            });		
			return response()->json(['message' => 'User status update successfully.','status'=>200]);
			
		}elseif($data['status'] == 1)
		{
			
			User::where('id',$data['id'])->update(['status'=>$data['status']]);
            $email =  $UserData['email'];
            $messageData = ['name'=>$UserData->name];
            Mail::send('emails.active_account',$messageData,function($message) use($email){
                $message->to($email)->subject(' Its on!- Artaux Account Activated');
            });		
			return response()->json(['message' => 'User status update successfully.','status'=>200]);
			
			
		}
		elseif($data['status'] == 'false')
		{
            User::where('id',$data['id'])->update(['status'=>$data['status']]);
            $email =  $UserData['email'];
            $messageData = ['name'=>$UserData->name];
            Mail::send('emails.emailreject',$messageData,function($message) use($email){
                $message->to($email)->subject(' Alert.!- Artaux Account Deactivated.!');
            });		
			return response()->json(['message' => 'User status update successfully.','status'=>200]);		
		
		}
		elseif($data['status'] == 0)
		{
			 User::where('id',$data['id'])->update(['status'=>$data['status']]);
             $email =  $UserData['email'];
             $messageData = ['name'=>$UserData->name];
             Mail::send('emails.emailreject',$messageData,function($message) use($email){
                $message->to($email)->subject(' Alert.!- Artaux Account Deactivated.!');
             });		
			 return response()->json(['message' => 'User status update successfully.','status'=>200]);
		}
		/*
		emailreject
        User::where('id',$data['id'])->update(['status'=>$data['status']]);
        return response()->json(['message' => 'User status update successfully.','status'=>200]);
		*/
    }

    public function delete_users(Request $request){
        $data = $request->all();
        User::where('id',$data['id'])->delete();
        return response()->json(['message' => 'User deleted successfully.','status'=>200]);
    }

    public function delete_category(Request $request){
        $data = $request->all();
        Category::where('id',$data['id'])->delete();
        return response()->json(['message' => 'Category deleted successfully.','status'=>200]);
    }

    public function view_new_template(){
        $templates = Template::where('status',0)->get();
        foreach($templates as $key => $val){
            $category = Category::where(['id'=>$val->category])->first();
            $contributor = Vendor::where(['id'=>$val->vendor_id])->first();
            if(isset($category)){
                $templates[$key]->category_name = $category->name;
            }
            if(isset($contributor)){
                $templates[$key]->vendor_name = $contributor->name;
            }
        }
        return response()->json(['templates'=>$templates]);
    }
    public function new_filter_template($fromdate,$todate){
        $templates = Template::whereDate('created_at','>=',$fromdate)->whereDate('created_at','<=',$todate)->where('status',0)->get();
        foreach($templates as $key => $val){
            $category = Category::where(['id'=>$val->category])->first();
            $contributor = Vendor::where(['id'=>$val->vendor_id])->first();
            if(isset($category)){
                $templates[$key]->category_name = $category->name;
            }
            if(isset($contributor)){
                $templates[$key]->vendor_name = $contributor->name;
            }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function view_template(){
        $templates = Template::where('status',1)->orwhere('status',2)->get();
        foreach($templates as $key => $val){
            $category = Category::where(['id'=>$val->category])->first();
            $contributor = Vendor::where(['id'=>$val->vendor_id])->first();
            if(isset($category)){
                $templates[$key]->category_name = $category->name;
            }
            if(isset($contributor)){
                $templates[$key]->vendor_name = $contributor->name;
            }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function view_filter_template($fromdate,$todate){
        $templates = Template::whereDate('created_at','>=',$fromdate)->whereDate('created_at','<=',$todate)->where('status',1)->orwhere('status',2)->get();
        foreach($templates as $key => $val){
            $category = Category::where(['id'=>$val->category])->first();
            $contributor = Vendor::where(['id'=>$val->vendor_id])->first();
            if(isset($category)){
                $templates[$key]->category_name = $category->name;
            }
            if(isset($contributor)){
                $templates[$key]->vendor_name = $contributor->name;
            }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function edit_template(Request $request){
        $data = $request->all();
        // dd( $data);
        if($data['status'] == 'true'){
            $status = 1;
        }elseif($data['status'] == 'false'){
			$status = 0;
		}elseif($data['status'] == 1){
            $status = 1;
        }else{
            $status = 0;
        }
        if($data['trending'] == 'true'){
            $trending = 1;
        }elseif($data['trending'] == 'false'){
			$trending = 0;
		}elseif($data['trending'] == 1){
            $trending = 1;
        }else{
            $trending = 0;
        }
        if($data['hot'] == 'true'){
            $hot = 1;
        }elseif($data['hot'] == 'false'){
		    $hot = 0;  	
		}elseif($data['hot'] == 1){
            $hot = 1;
        }else{
            $hot = 0;
        }
        // dd($status);
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
        if(empty($data['discount'])){
            $data['discount'] = 0;
        }
        if(empty($data['tags'])){
            $tags = null;
        }

        $tags = $data['tags'];

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

        Template::where('id',$data['id'])->update(['category'=>$data['cat_id'],'title'=>$data['title'],'description'=>$data['desc'],'add_desc'=>$data['add_desc'],'file_type'=>$data['file_type'],'image'=>$image,'zip'=>$zip,'status'=>$status,'trending'=>$trending,'hot'=>$hot,'price'=>$data['price'],'discount'=>$data['discount'],'tags'=>$data['tags'],'approve'=>1]);
       if($status == 1){
           $template = Template::where('id',$data['id'])->first();
           $vendor = Vendor::where('id',$template->vendor_id)->first();
            $email = $template->email;
            $messageData = ['email'=>$vendor->email,'name'=>$vendor->name];
           
		   Mail::send('emails.product_upload',$messageData,function($message) use($email){
                $message->to($email)->subject('Your Product is now live! - Artaux');
            });
       }
        return response()->json(['message' => 'Template edit successfully.','status'=>200]);
    }

    public function edit_view_template(Request $request,$id){
        $templates = Template::where('id',$id)->first();
        $category = Category::get();
		$subcategory = Category::where('main_cat','!=',0)->get();
        $extension = DB::table('file_extensions')->get();
        return response()->json(['subcategory'=>$subcategory,'templates'=>$templates,'category'=>$category,'extension'=>$extension]);
    }

    public function delete_template($id){
        // $data = $request->all();
        Template::where('id',$id)->delete();
        return response()->json(['message' => 'Template deleted successfully.','status'=>200]);
    } 
    
    public function delete_template1(Request $request){
        $data = $request->all();
        Template::where('id',$data['id'])->delete();
        return response()->json(['message' => 'Template deleted successfully.','status'=>200]);
    }

    public function add_pages(Request $request){
        $data = $request->all();
            $pages = new Page;
            $pages->title= $data['title'];
            $pages->content= $data['content'];
            $pages->save();

        return response()->json(['message' => 'Cms Page added successfully.','status'=>200]);
    }

    public function view_pages(Request $request){
        $pages = Page::get();
        return response()->json(['pages'=>$pages]);
    }

    public function edit_view_pages(Request $request,$id){
        $pages = Page::where('id',$id)->first();

        return response()->json(['pages'=>$pages]);
    }

    public function edit_pages(Request $request){
        $data = $request->all();
        if(empty($data['title'])){
            $data['title'] == null;
        }

        if(empty($data['content'])){
            $data['content'] == null;
        }

        Page::where('id',$data['id'])->update(['title'=>$data['title'],'content'=>$data['content']]);

        return response()->json(['message' => 'Cms Page added successfully.','status'=>200]);
    }

    public function delete_pages(Request $request){
        $data = $request->all();
        Page::where('id',$data['id'])->delete();
        return response()->json(['message' => 'Cms page deleted successfully.','status'=>200]);
    }

    public function addBanner(Request $request){
        $data = $request->all();
        $banner = new Banner;
		
		
        // Upload Images after Resize
        $file = $data['image'];
        $extension = $file->getClientOriginalExtension();
        $fileName = rand(111,9999999).'.'.$extension;
        $path1  = 'images/banner/';
        $file->move($path1,$fileName);
        $banner->image =$fileName;
        $banner->title= $data['title'];
        $banner->url= $data['url'];
		
        if($data['status'] == 1){
            $banner->status = '1';
        }
		elseif($data['status'] == 'true')
		{
			$banner->status = '1';
		}
		elseif($data['status'] == 0)
		{         
		 $banner->status = '0';
        }
		elseif($data['status'] == "false")
		{         
		 $banner->status = '0';
        }
		
		
		$banner->save();
        // if(empty($data['status'])){
        //     $status = '0';
        // }else{
        //     $status = '1';
        // }
        // $banner->status = $status;
        $banner->save();
        return response()->json(['message' => 'Banner added successfully.','status'=>200]);
    }

    public function viewBanners(){
        $banners = Banner::where(['collection'=>0,'mid_section'=>0])->orderBy('id','DESC')->get();
        return response()->json(['banners'=>$banners]);
    }

    public function viewMidSectionBanners(){
        $banners = Banner::where('mid_section',1)->orderBy('id','ASC')->get();
        return response()->json(['banners'=>$banners]);
    }

    public function viewCollectionBanners(){
        $banners = Banner::where('collection',1)->orderBy('id','ASC')->get();
        return response()->json(['banners'=>$banners]);
    }

    public function deleteBanner(Request $request){
        $data = $request->all();
        $banners = Banner::where('id',$data['id'])->delete();
        return response()->json(['message' => 'Banner deleted successfully.','status'=>200]);
    }

    public function editViewBanner(Request $request, $id){
        $banners = Banner::where('id',$id)->first();
        return response()->json(['banners'=>$banners]);
    }

    public function editBanner(Request $request){
        $data = $request->all();
        if($data['status'] == 'true'){
            $status = 1;
        }elseif($data['status'] == 1){
            $status = 1;
        }else{
            $status = 0;
        }

        if(empty($data['title'])){
            $data['title'] = '';
        }

        if(empty($data['url'])){
            $data['url'] = '';
        }

        if($data['image'] == "null" || empty($data['image'])){
            $banner=Banner::where('id',$data['id'])->first();
           $fileName=$banner->image;
           }else{
            $file = $data['image'];
            $extension = $file->getClientOriginalExtension();
            $fileName = rand(111,9999999).'.'.$extension;
            $path1  = 'images/banner/';
            $file->move($path1,$fileName);
           }

           Banner::where('id',$data['id'])->update(['title'=>$data['title'],'image'=>$fileName,'url'=>$data['url'],'status'=>$status]);

           return response()->json(['message' => 'Banner update successfully.','status'=>200]);
    }

    public function viewContributors(){
        $contributors = Vendor::where('acc_status','active')->orderBy('id','DESC')->get();
        $account =  DB::table('bank_details')->get();
        return response()->json(['contributors'=>$contributors,'account'=>$account]);
    }

    public function delete_request(Request $request){
        $data = $request->all();
        $template = Template::where('id',$data['id'])->first();
        $templatename = $template->title;
        $vendor = Vendor::where('id',$template->vendor_id)->first();
        DB::table('templates')->where('id',$data['id'])->update(['delete_request'=>0,'reason'=>null]);
        $email =  $vendor->email;
        $messageData = ['name'=>$vendor->name,'template'=>$templatename,'message'=>'Your delection request will be rejected by admin.'];
        Mail::send('emails.delete_template',$messageData,function($message) use($email){
            $message->to($email)->subject(' Its on!- Artaux');
        });
        return response()->json(['message' => 'Request cancelled successfully.','status'=>200]);
    }

    public function accept_request(Request $request){
        $data = $request->all();
        $template = Template::where('id',$data['id'])->first();
        $templatename = $template->title;
        $vendor = Vendor::where('id',$template->vendor_id)->first();
        DB::table('templates')->where('id',$data['id'])->delete();
        $email =  $vendor->email;
        $messageData = ['name'=>$vendor->name,'template'=>$templatename,'message'=>'Your delection request will be accepted by admin and your digital asset deleted successfully'];
        Mail::send('emails.accept_delete_request',$messageData,function($message) use($email){
            $message->to($email)->subject(' Its on!- Artaux');
        });
        return response()->json(['message' => 'Request accepted successfully.','status'=>200]);
    }

    public function delete_user_search(){
        DB::table('search')->truncate();
        return response()->json(['message' => 'Search history deleted successfully.','status'=>200]);
    }

    public function newContributors(){
        $contributors = Vendor::where('acc_status','new')->where('ratio',0)->orderBy('id','DESC')->get();
        $account =  DB::table('bank_details')->get();
        return response()->json(['contributors'=>$contributors, 'account'=>$account]);
    }

    public function editNewContributors($id){
        $contributors = Vendor::where('id',$id)->first();
        return response()->json(['contributor'=>$contributors]);
    }

    public function editContributor(Request $request){
        $data = $request->all();
		$contributors = Vendor::where('id',$data['id'])->first();
		 
        if($data['status'] == 'true'){
            
			$status = 1;
			Vendor::where('id',$data['id'])->update(['ratio'=>$data['ratio'],'name'=>$contributors->name,'email'=>$contributors->email,'mobile'=>$contributors->mobile,'profession'=>$contributors->profession,'nationality'=>$contributors->nationality,'acc_status'=>'active','status'=>$status]);
            $email =  $contributors->email;
            $messageData = ['name'=>$contributors->name];
            Mail::send('emails.active_account',$messageData,function($message) use($email){
                $message->to($email)->subject(' Its on!- Artaux Account Activated');
            });
			
			return response()->json(['message' => 'Status Received true.','status'=>200]);
        
		}elseif($data['status'] == 1){
			
            $status = 1;
			Vendor::where('id',$data['id'])->update(['ratio'=>$data['ratio'],'name'=>$contributors->name,'email'=>$contributors->email,'mobile'=>$contributors->mobile,'profession'=>$contributors->profession,'nationality'=>$contributors->nationality,'acc_status'=>'active','status'=>$status]);
            $email =  $contributors->email;
            $messageData = ['name'=>$contributors->name];
            Mail::send('emails.active_account',$messageData,function($message) use($email){
                $message->to($email)->subject(' Its on!- Artaux Account Activated');
            });
			return response()->json(['message' => 'Status Flag 1 Received.','status'=>200]);
			
		}
        elseif($data['status'] == 'false'){
            $status = 0;
			Vendor::where('id',$data['id'])->update(['ratio'=>$data['ratio'],'name'=>$contributors->name,'email'=>$contributors->email,'mobile'=>$contributors->mobile,'profession'=>$contributors->profession,'nationality'=>$contributors->nationality,'acc_status'=>'active','status'=>$status]);
            $email =  $contributors->email;
            $messageData = ['name'=>$contributors->name];
            Mail::send('emails.emailreject',$messageData,function($message) use($email){
                $message->to($email)->subject(' Alert!- Artaux Account DeActivated..!');
            });
			return response()->json(['message' => 'Status Flag 1 Received.','status'=>200]);
        }
		elseif($data['status' == 'false']){
            $status = 0;
			Vendor::where('id',$data['id'])->update(['ratio'=>$data['ratio'],'name'=>$contributors->name,'email'=>$contributors->email,'mobile'=>$contributors->mobile,'profession'=>$contributors->profession,'nationality'=>$contributors->nationality,'acc_status'=>'active','status'=>$status]);
            $email =  $contributors->email;
            $messageData = ['name'=>$contributors->name];
            Mail::send('emails.emailreject',$messageData,function($message) use($email){
                $message->to($email)->subject(' Alert!- Artaux Account DeActivated..!');
            });
			return response()->json(['message' => 'Status Flag 1 Received.','status'=>200]);
        }
		/*
        $contributors = Vendor::where('id',$data['id'])->first();
        Vendor::where('id',$data['id'])->update(['ratio'=>$data['ratio'],'name'=>$contributors->name,'email'=>$contributors->email,'mobile'=>$contributors->mobile,'profession'=>$contributors->profession,'nationality'=>$contributors->nationality,'acc_status'=>'active','status'=>$status]);
         // send confirmation mail
            $email =  $contributors->email;
            $messageData = ['name'=>$contributors->name];
            Mail::send('emails.active_account',$messageData,function($message) use($email){
                $message->to($email)->subject(' Its on!- Artaux');
            });
        return response()->json(['message' => 'contributor ratio set successfully.','status'=>200]);
        */  
  }

    public function viewContributorTemplates(Request $request, $id){
        $templates = Template::where('vendor_id',$id)->get();
        foreach($templates as $key => $val){
            $category = Category::where(['id'=>$val->category])->first();
            $contributor = Vendor::where(['id'=>$val->vendor_id])->first();
            if(isset($category)){
                $templates[$key]->category_name = $category->name;
            }
            if(isset($contributor)){
                $templates[$key]->vendor_name = $contributor->name;
            }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function view_delete_request_templates(){
        $templates = Template::where('delete_request',1)->get();
        foreach($templates as $key => $val){
            $category = Category::where(['id'=>$val->category])->first();
            $contributor = Vendor::where(['id'=>$val->vendor_id])->first();
            if(isset($category)){
                $templates[$key]->category_name = $category->name;
            }
            if(isset($contributor)){
                $templates[$key]->vendor_name = $contributor->name;
            }
        }
        return response()->json(['templates'=>$templates]);
    }

    public function viewContactDetails(){
        $contact = Contact::get();
        return response()->json(['contact'=>$contact]);
    }

    public function viewAboutDetails(){
        $about = DB::table('abouts')->first();
        return response()->json(['about'=>$about]);
    }
    public function editAbout(Request $request){
        $data = $request->all();
        if(empty($data['content1'])){
            $data['content1'] = '';
        }
        if(empty($data['content2'])){
            $data['content2'] = '';
        }

        if(empty($data['title1'])){
            $data['title1'] = '';
        }
        if(empty($data['title2'])){
            $data['title2'] = '';
        }

        if(empty($data['image_title1'])){
            $data['image_title1'] = '';
        }
        if(empty($data['image_title2'])){
            $data['image_title2'] = '';
        }
        if(empty($data['image_title3'])){
            $data['image_title3'] = '';
        }

        if($data['image1'] == "null" || empty($data['image1'])){
        $about=DB::table('abouts')->where('id',1)->first();
        // dd($about);
        $fileName1=$about->image1;
        }else{
            $file = $data['image1'];
            $extension = $file->getClientOriginalExtension();
            $fileName1 = rand(111,9999999).'.'.$extension;
            $path1  = 'images/about/';
            $file->move($path1,$fileName1);
        }
        if($data['image2'] == "null" || empty($data['image2'])){
        $about=DB::table('abouts')->where('id',1)->first();
        // dd($about);
        $fileName2=$about->image2;
        }else{
            $file = $data['image2'];
            $extension = $file->getClientOriginalExtension();
            $fileName2 = rand(111,9999999).'.'.$extension;
            $path1  = 'images/about/';
            $file->move($path1,$fileName2);
        }
        if($data['image3'] == "null" || empty($data['image3'])){
        $about=DB::table('abouts')->where('id',1)->first();
        // dd($about);
        $fileName3=$about->image3;
        }else{
            $file = $data['image3'];
            $extension = $file->getClientOriginalExtension();
            $fileName3 = rand(111,9999999).'.'.$extension;
            $path1  = 'images/about/';
            $file->move($path1,$fileName3);
        }
        DB::table('abouts')->where('id',1)->update(['image_content1'=>$data['image_title1'],'image_content2'=>$data['image_title2'],'image_content3'=>$data['image_title3'],'title1'=>$data['title1'],'title2'=>$data['title2'],'content1'=>$data['content1'],'content2'=>$data['content2'],'image1'=>$fileName1,'image2'=>$fileName2,'image3'=>$fileName3]);
        return response()->json(['message' => 'About update successfully.','status'=>200]);
    }

    public function editContactDetails(Request $request, $id){
        $contact = Contact::where('id',$id)->first();
        return response()->json(['contact'=>$contact]);
    }

    public function update_order_status($id){
        DB::table('orders')->where('id',$id)->update(['order_status'=>'paid']);
        DB::table('contributors_orders')->where('order_id',$id)->update(['order_status'=>'paid']);
        return response()->json(['message' => 'Order status update successfully.','status'=>200]);
    }

     public function view_orders(){
        $templates = DB::table('orders_products')->get();
        if(count($templates)>0){
        foreach($templates as $template){
            $ids[]= $template->order_id;
        }
        }else{
            $ids = array();
        }
        // dd($ids);
        $orders = DB::table('orders')->whereIn('id', $ids)->get();

        foreach($orders as $key => $val){

            $orders[$key]->final_price = $val->grand_total;
        }

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }
     public function view_user_orders($id){
        $templates = DB::table('orders_products')->get();
        // dd($ids);
        $orders = DB::table('orders')->where('user_id', $id)->get();

        foreach($orders as $key => $val){

            $orders[$key]->final_price = $val->grand_total;
        }

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }

     public function view_contributors_orders($id){
        $templates = DB::table('orders_products')->where('vendor_id',$id)->get();
        // dd($templates);
        if($templates->isNotEmpty() || count($templates) > 0){
            // dd('hiii');
            foreach($templates as $template){
                $ids[]= $template->order_id;
            }
        }else{
            $ids[] = '';
        }
        // dd($ids);
        $orders = DB::table('orders')->whereIn('id', $ids)->get();

        foreach($orders as $key => $val){
            $orders[$key]->final_price = $val->grand_total;
        }

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }

    public function view_filter_contributor_orders($id,$fromdate,$todate){
        $templates = DB::table('orders_products')->where('vendor_id',$id)->get();
        if($templates->isNotEmpty() || count($templates) > 0){
            // dd('hiii');
            foreach($templates as $template){
                $ids[]= $template->order_id;
            }
        }else{
            $ids[] = '';
        }
        // dd($ids);
        $orders = DB::table('orders')->whereIn('id', $ids)->whereDate('updated_at','>=',$fromdate)->whereDate('updated_at','<=',$todate)->get();

        foreach($orders as $key => $val){

            $orders[$key]->final_price = $val->grand_total;
        }

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }

     public function view_filter_orders($fromdate,$todate){
        $templates = DB::table('orders_products')->get();

        foreach($templates as $template){
            $ids[]= $template->order_id;
        }
        // dd($ids);
        $orders = DB::table('orders')->whereIn('id', $ids)->whereDate('updated_at','>=',$fromdate)->whereDate('updated_at','<=',$todate)->get();

        foreach($orders as $key => $val){

            $orders[$key]->final_price = $val->grand_total;
        }

        return response()->json(['templates'=>$templates,'orders'=>$orders,'status'=>200]);
    }

    public function view_order($id){
        $templates = DB::table('orders_products')->where('order_id',$id)->get();
        $final_total = 0;
        foreach($templates as $key => $val){
            $template = Template::where(['id'=>$val->product_id])->first();
            $vendor = DB::table('vendors')->where('id',$val->vendor_id)->first();
            if(isset($template)){
                $templates[$key]->file_type = $template->file_type;
                $templates[$key]->vendor_name = $vendor->name;
                $final_total = $val->product_price + $final_total;
            }
            $vendor_ids[]= $val->vendor_id;
        }

            $vendor_ids = array_unique($vendor_ids);

        $vendors =  DB::table('vendors')->whereIn('id',$vendor_ids)->get();
        foreach($vendors as $key => $val){
           $price = DB::table('contributors_orders')->where('order_id',$id)->where('vendor_id',$val->id)->first();
            $vendors[$key]->final_price = $price->grand_total;
        }

        // $gst_amount = ($final_total*18)/100;
        $gst_amount = ($final_total*0)/100;

        $orders = DB::table('orders')->where('id', $id)->first();
        if($orders->currency == "INR"){
            $final_total = $gst_amount + $final_total;
         }
        $currency = $orders->currency;

        return response()->json(['templates'=>$templates,'vendors'=>$vendors,'final_total'=>$final_total,'orders'=>$orders,'currency'=>$currency,'status'=>200]);
    }

    public function purchase_report(){
       $orders = DB::table('orders_products')->get();
       foreach($orders as $key => $val){
            $vendor = DB::table('vendors')->where('id',$val->vendor_id)->first();
            $orders[$key]->vendor_share = $vendor->ratio;
            if($val->currency == "INR"){
                $gst_price = ($val->product_price/100)*18;
                $price = $val->product_price + $gst_price;
                $orders[$key]->gst_price = $gst_price;
            }else{
                $orders[$key]->gst_price = 0;
                $price = $val->product_price;
            }
            $templates = DB::table('templates')->where('id',$val->product_id)->first();
            $orders[$key]->template_name = $templates->title;
            $orders[$key]->price = $price;
       }
        return response()->json(['orders'=>$orders]);
    }

    public function purchase_filter_report($fromdate,$todate){
       $orders = DB::table('orders_products')->whereDate('updated_at','>=',$fromdate)->whereDate('updated_at','<=',$todate)->get();
       foreach($orders as $key => $val){
            $vendor = DB::table('vendors')->where('id',$val->vendor_id)->first();
            $orders[$key]->vendor_share = $vendor->ratio;
            if($val->currency == "INR"){
                $gst_price = ($val->product_price/100)*18;
                $price = $val->product_price + $gst_price;
                $orders[$key]->gst_price = $gst_price;
            }else{
                $orders[$key]->gst_price = 0;
                $price = $val->product_price;
            }
            $templates = DB::table('templates')->where('id',$val->product_id)->first();
            $orders[$key]->template_name = $templates->title;
            $orders[$key]->price = $price;
       }
        return response()->json(['orders'=>$orders]);
    }

    public function viewUserSearch(){
        $search = DB::table('search')->groupBy('keyword')->orderBy('keyword','ASC')->get();
        foreach($search as $key => $val){
            $searchcnt = DB::table('search')->where('keyword',$val->keyword)->count();
            $search[$key]->count = $searchcnt;
        }
        return response()->json(['search'=>$search]);
    }

    public function updateContactDetails(Request $request,$id){
        $data = $request->all();
        DB::table('contact')->where('id',$id)->update(['phone'=>$data['mobile'],'email'=>$data['email'],'address'=>$data['address']]);
        return response()->json(['message'=>'Contact Updated successfully']);
    }

    public function add_file_extension(Request $request){
        $data = $request->all();
        $date = Carbon::now();
        DB::table('file_extensions')->insert(['name' => $data['name'],'created_at'=>$date]);
        return response()->json(['message' => 'File extesnion added successfully.','status'=>200]);
    }

    public function view_file_extension(){
        $extension =  DB::table('file_extensions')->orderBy('Name','ASC')->get();
        return response()->json(['extension'=>$extension, 'status'=>200]);
    }

    public function delete_file_extension(Request $request){
        $data = $request->all();
        DB::table('file_extensions')->where('id',$data['id'])->delete();
        return response()->json(['message' => 'File extension deleted successfully.','status'=>200]);
    }

    public function view_edit_file_extension($id){
        $extension =  DB::table('file_extensions')->where('id',$id)->first();
        return response()->json(['extension'=>$extension, 'status'=>200]);
    }

    public function edit_file_extension(Request $request,$id){
        $data = $request->all();
            // dd( $data);
            if(empty($data['name'])){
                $data['name'] = '';
            }
            DB::table('file_extensions')->where('id',$id)->update(['name'=>$data['name']]);
            return response()->json(['message' => 'File extension edited successfully.','status'=>200]);
    }

    public function view_rates(){
        $rates =  DB::table('rates')->where('country','!=','DIRHAM')->orderBy('country','ASC')->get();
        return response()->json(['rates'=>$rates, 'status'=>200]);
    }

    public function view_edit_rate($id){
        $rates =  DB::table('rates')->where('id',$id)->first();
        return response()->json(['rates'=>$rates, 'status'=>200]);
    }

    public function edit_rate(Request $request,$id){
        $data = $request->all();
            // dd( $data);
            if(empty($data['name'])){
                $data['name'] = '';
            }
            DB::table('rates')->where('id',$id)->update(['rate'=>$data['rate']]);
            return response()->json(['message' => 'Rate edited successfully.','status'=>200]);
    }

    public function add_blog(Request $request){
        if(!$request->hasFile('image')) {
            return response()->json(['message'=>'upload_file_not_found'], 400);
        }
            $data = $request->all();
            $blog = new Blog;

            // Upload Images
            $file = $data['image'];
            $extension = $file->getClientOriginalExtension();
            $fileName = rand(111,99999).'.'.$extension;
            $path  = 'images/blog/';
            $file->move($path,$fileName);
            $blog->image=$fileName;

            $blog->title= $data['title'];
            $blog->content= $data['content'];
            $blog->author= $data['author'];
            $blog->save();

        return response()->json(['message' => 'Blog added successfully.','status'=>200]);
    }

    public function view_blogs(){
        $blog =  Blog::orderBy('created_at','DESC')->get();
        return response()->json(['blog'=>$blog, 'status'=>200]);
    }

    public function edit_view_blog($id){
        $blog =  Blog::where('id',$id)->first();
        return response()->json(['blog'=>$blog, 'status'=>200]);
    }

    public function editBlog(Request $request){
        $data = $request->all();

        if(empty($data['title'])){
            $data['title'] = '';
        }

        if(empty($data['content'])){
            $data['content'] = '';
        }

        if(empty($data['author'])){
            $data['author'] = '';
        }

        if($data['image'] == "null" || empty($data['image'])){
            $blog=Blog::where('id',$data['id'])->first();
           $fileName=$blog->image;
           }else{
            $file = $data['image'];
            $extension = $file->getClientOriginalExtension();
            $fileName = rand(111,9999999).'.'.$extension;
            $path1  = 'images/blog/';
            $file->move($path1,$fileName);
           }

           Blog::where('id',$data['id'])->update(['title'=>$data['title'],'image'=>$fileName,'content'=>$data['content'],'author'=>$data['author']]);

           return response()->json(['message' => 'Blog update successfully.','status'=>200]);
    }

    public function delete_blog(Request $request){
        $data = $request->all();
        Blog::where('id',$data['id'])->delete();
        return response()->json(['message' => 'Blog deleted successfully.','status'=>200]);
    }

    public function view_enquiry(Request $request){
        $enquiries =  DB::table('enquiries')->orderBy('id','DESC')->get();
        return response()->json(['enquiries'=>$enquiries, 'status'=>200]);
    }

    public function view_newsletter(Request $request){
        $emails = DB::table('sub_emails')->get();
        return response()->json(['emails'=>$emails, 'status' => 200]);
    }

    protected function respondWithToken($token){
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'token_validaty' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    public function guard(){
        return Auth::guard('admin_api');
    }
}
