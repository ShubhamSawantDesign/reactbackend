<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Template;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['policies','chk_id','username_chk','vendor_email_chk','products_data','trending_data','get_footer_data','hot_data','home_data','get_header_data','email_chk','view_about','get_sub_categories','home_view_blogs','view_blog','view_blogs','get_categories','vendor_total_sale','vendor_sold_templates','total_orders_count','total_vendor_count','view_collection_banners','view_mid_section_banners','search_templates','viewfiletypes','getstatus','get_templates','filter_templates','get_trending_templates','get_hot_templates','get_template','get_currency','view_banners','trending_templates','sold_templates']]);
    }
    
    
    public function policies(){
        $page = DB::table('pages')->whereIn('id',[1,2,3,6])->get();
        return response()->json(['page'=>$page, 'status' => 200]);
    }

    public function get_header_data(Request $request){
        $category = Category::where('main_cat',0)->get();
        $subcategory = Category::where('main_cat','!=',0)->get();
        $filetype = DB::table('templates')->select('file_type')->groupBy('file_type')->get();
        $INR_rate = DB::table('rates')->where('country','INR')->first();
        $USD_rate = DB::table('rates')->where('country','USD')->first();
        $GBP_rate = DB::table('rates')->where('country','GBP')->first();
        $EURO_rate = DB::table('rates')->where('country','EURO')->first();
        $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
        return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'category' => $category,'subcategory' => $subcategory,'filetype'=>$filetype,'status'=>200]);
    }

    public function get_footer_data(Request $request){
        $category = Category::where('main_cat',0)->get();
        $subcategory = Category::where('main_cat','!=',0)->get();
        $filetype = DB::table('templates')->select('file_type')->groupBy('file_type')->get();
        $INR_rate = DB::table('rates')->where('country','INR')->first();
        $USD_rate = DB::table('rates')->where('country','USD')->first();
        $GBP_rate = DB::table('rates')->where('country','GBP')->first();
        $EURO_rate = DB::table('rates')->where('country','EURO')->first();
        $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
        return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'category' => $category,'subcategory' => $subcategory,'filetype'=>$filetype,'status'=>200]);
    }

    public function home_data(Request $request){
        $banners = DB::table('banners')->where(['collection'=>0,'mid_section'=>0])->where('status',1)->get();
        $midbanners1 = DB::table('banners')->where('id',14)->first();
        $midbanners2 = DB::table('banners')->where('id',15)->first();
        $midbanners3 = DB::table('banners')->where('id',16)->first();
        $midbanners4 = DB::table('banners')->where('id',17)->first();
        $banners1 = DB::table('banners')->where('id',3)->first();
        $banners2 = DB::table('banners')->where('id',4)->first();
        $banners3 = DB::table('banners')->where('id',5)->first();
        $banners4 = DB::table('banners')->where('id',6)->first();
        $templates = DB::table('templates')->where('trending',1)->where('status',1)->get();
        $hottemplates = DB::table('templates')->where('hot',1)->where('status',1)->get();
        foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
            $vendor= Vendor::where('id',$val->vendor_id)->first();
            $templates[$key]->uploaded_by = $vendor->username;
        }
        foreach($hottemplates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $hottemplates[$key]->product_price = $price;
            }else{
                $hottemplates[$key]->product_price =$val->price;
            }
            $vendor= Vendor::where('id',$val->vendor_id)->first();
            $hottemplates[$key]->uploaded_by = $vendor->username;
        }
        $blog =Blog::orderBy('created_at','DESC')->limit(3)->get();
        $INR_rate = DB::table('rates')->where('country','INR')->first();
        $USD_rate = DB::table('rates')->where('country','USD')->first();
        $GBP_rate = DB::table('rates')->where('country','GBP')->first();
        $EURO_rate = DB::table('rates')->where('country','EURO')->first();
        $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
        return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'banners'=>$banners,'midbanners1'=>$midbanners1,'midbanners2'=>$midbanners2,'midbanners3'=>$midbanners3,'midbanners4'=>$midbanners4,'banners1'=>$banners1,'banners2'=>$banners2,'banners3'=>$banners3,'banners4'=>$banners4,'templates'=>$templates,'hottemplates'=>$hottemplates,'blog'=>$blog,'status'=>200]);
    }

    public function hot_data(Request $request){
        $category = Category::where('main_cat',0)->get();
        $filetype = DB::table('templates')->select('file_type')->groupBy('file_type')->get();
        $templates = Template::where(['status'=>1,'hot'=>1])->whereNotNull('price')->get();
        foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
            $vendor= Vendor::where('id',$val->vendor_id)->first();
            $templates[$key]->uploaded_by = $vendor->username;
        }
        $INR_rate = DB::table('rates')->where('country','INR')->first();
        $USD_rate = DB::table('rates')->where('country','USD')->first();
        $GBP_rate = DB::table('rates')->where('country','GBP')->first();
        $EURO_rate = DB::table('rates')->where('country','EURO')->first();
        $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
        return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'category' => $category,'filetype'=>$filetype,'templates' => $templates,'status'=>200]);
    }


    public function trending_data(Request $request){
        $category = Category::where('main_cat',0)->get();
        $filetype = DB::table('templates')->select('file_type')->groupBy('file_type')->get();
        $templates = DB::table('templates')->where('trending',1)->where('status',1)->get();
        foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
            $vendor= Vendor::where('id',$val->vendor_id)->first();
            $templates[$key]->uploaded_by = $vendor->username;
        }
        $INR_rate = DB::table('rates')->where('country','INR')->first();
        $USD_rate = DB::table('rates')->where('country','USD')->first();
        $GBP_rate = DB::table('rates')->where('country','GBP')->first();
        $EURO_rate = DB::table('rates')->where('country','EURO')->first();
        $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
        return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'category' => $category,'filetype'=>$filetype,'templates' => $templates,'status'=>200]);
    }

    public function products_data(Request $request){
        $category = Category::where('main_cat',0)->get();
        $filetype = DB::table('templates')->select('file_type')->groupBy('file_type')->get();
        $INR_rate = DB::table('rates')->where('country','INR')->first();
        $USD_rate = DB::table('rates')->where('country','USD')->first();
        $GBP_rate = DB::table('rates')->where('country','GBP')->first();
        $EURO_rate = DB::table('rates')->where('country','EURO')->first();
        $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
        return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'category' => $category,'filetype'=>$filetype,'status'=>200]);
    }


     public function get_categories(Request $request){
        $category = Category::where('main_cat',0)->get();
          return response()->json(['category' => $category,'status'=>200]);
     }

     public function email_chk(Request $request){
         $data = $request->all();
        $email_cnt = User::where('email',$data['email'])->count();
        if($email_cnt > 0){
            return response()->json(['message' => 'Email already exist.','status'=>401]);
        }else{
            return response()->json(['status'=>200]);
        }
        //   return response()->json(['category' => $category,'status'=>200]);
     }

     public function vendor_email_chk(Request $request){
         $data = $request->all();
        $email_cnt = Vendor::where('email',$data['email'])->count();
        if($email_cnt > 0){
            return response()->json(['message' => 'Email already exist.','status'=>401]);
        }else{
            return response()->json(['status'=>200]);
        }
        //   return response()->json(['category' => $category,'status'=>200]);
     }

     public function username_chk(Request $request){
         $data = $request->all();
        $email_cnt = Vendor::where('username',$data['username'])->count();
        if($email_cnt > 0){
            return response()->json(['message' => 'Username already exist.','status'=>401]);
        }else{
            return response()->json(['status'=>200]);
        }
        //   return response()->json(['category' => $category,'status'=>200]);
     }

     public function chk_id($id){
        $cat = Category::where('id',$id)->first();
        if($cat->main_cat == 1 || $cat->main_cat == 4 || $cat->id == 1 || $cat->id == 4){
            return response()->json(['status'=>200]);
        }else{
            return response()->json(['status'=>401]);
        }
        //   return response()->json(['category' => $category,'status'=>200]);
     }

     public function get_sub_categories(Request $request){
        $category = Category::where('main_cat','!=',0)->get();
          return response()->json(['category' => $category,'status'=>200]);
     }

      public function view_about(){
        $about = DB::table('abouts')->first();
        return response()->json(['about'=>$about]);
    }

    public function get_templates(Request $request,$id){
        $category = Category::where('id',$id)->first();
        if($category->main_cat != 0){
              $templates = Template::where('category',$id)->where(['status'=>1])->whereNotNull('price')->get();
              foreach($templates as $key => $val){
                if($val->discount != 0){
                    $price = $val->price /100*$val->discount;
                    $price = $val->price-$price;
                    $templates[$key]->product_price = $price;
                }else{
                    $templates[$key]->product_price =$val->price;
                }
                $vendor= Vendor::where('id',$val->vendor_id)->first();
                if(isset($vendor)){
                    $templates[$key]->uploaded_by = $vendor->username;
                }
            }
        }else{
            $subcats= Category::select('id')->where('main_cat',$id)->get();
            foreach($subcats as $subcat){
                $sub_ids[]= $subcat->id;
            }

             $templates = Template::where(['status'=>1])->whereIn('category',$sub_ids)->orWhere('category',$id)->whereNotNull('price')->get();
            //  dd($templates);
              foreach($templates as $key => $val){
                if($val->discount != 0){
                    $price = $val->price /100*$val->discount;
                    $price = $val->price-$price;
                    $templates[$key]->product_price = $price;
                }else{
                    $templates[$key]->product_price =$val->price;
                }
                $vendor= Vendor::where('id',$val->vendor_id)->first();
                if(isset($vendor)){
                $templates[$key]->uploaded_by = $vendor->username;
                }
            }
        }
        $category = Category::where('id',$id)->first();
          return response()->json(['templates' => $templates,'category' => $category,'status'=>200]);

     }


    public function filter_templates($category,$file_type,$discount,$range){
            // dd($category);
            if($discount == 0){
                $first = 0;
                $second = 100;
            }elseif($discount == 20){
                $first = 0;
                $second = 20;
            }elseif($discount == 40){
                $first = 20;
                $second = 40;
            }elseif($discount == 60){
                $first = 40;
                $second = 60;
            }elseif($discount == 80){
                $first = 60;
                $second = 80;
            }

            if($category == 0 && $file_type == 0){

                $templates = Template::where('discount','>=',$first)->where('discount','<=',$second)->where('approve',1)->where('price','<=',$range)->get();
            }elseif($category != 0 && $file_type == 0){
                 $subcats= Category::select('id')->where('main_cat',$category)->get();
                foreach($subcats as $subcat){
                    $sub_ids[]= $subcat->id;
                }
                $templates = Template::whereIn('category',$sub_ids)->where('discount','>=',$first)->where('discount','<=',$second)->where('approve',1)->where('price','<=',$range)->get();
            }elseif($category == 0 && $file_type != 0){

                $templates = Template::where(['file_type'=>$file_type])->where('discount','>=',$first)->where('discount','<=',$second)->where('approve',1)->where('price','<=',$range)->get();
            }else{
                $subcats= Category::select('id')->where('main_cat',$category)->get();
                foreach($subcats as $subcat){
                    $sub_ids[]= $subcat->id;
                }
                $templates = Template::whereIn('category',$sub_ids)->where(['file_type'=>$file_type])->where('discount','>=',$first)->where('discount','<=',$second)->where('approve',1)->where('price','<=',$range)->get();
            }
            //   dd($templates);
          foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
        }
        $category = Category::where('id',$category)->first();
          return response()->json(['templates' => $templates,'category' => $category,'status'=>200]);

     }

    public function get_trending_templates(Request $request){
          $templates = Template::where(['status'=>1,'trending'=>1])->whereNotNull('price')->get();
          foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
        }
          return response()->json(['templates' => $templates,'status'=>200]);

     }

    public function get_hot_templates(Request $request){
          $templates = Template::where(['status'=>1,'hot'=>1])->whereNotNull('price')->get();
          foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
        }
          return response()->json(['templates' => $templates,'status'=>200]);

     }

     public function search_templates($keyword,$file_type){
            $category_search =Category::where('name','LIKE','%'.$keyword.'%')->get();
            $category_search_count =Category::where('name','LIKE','%'.$keyword.'%')->count();
            if($category_search_count > 0){
                foreach($category_search as $category){
                    $ids[]=$category->id;
                }
                if($file_type == "All" || $file_type == "undefined"){
                $templates = Template::whereIn('category',$ids)->where(['status'=>1])->whereNotNull('price')->get();
                }else{
                    $templates = Template::whereIn('category',$ids)->where(['status'=>1,'file_type'=>$file_type])->whereNotNull('price')->get();
                }
            }else{
                if($file_type == "All" || $file_type == "undefined"){
                    $templates = Template::where(['status'=>1])->where('title','LIKE','%'.$keyword.'%')->orWhere(function($query) use($keyword)
                                {
                                    $query->where('description','LIKE','%'.$keyword.'%')
                                    ->orwhere('tags','LIKE','%'.$keyword.'%')
                                    ->orwhere('add_desc','LIKE','%'.$keyword.'%');
                                })
                    ->whereNotNull('price')->get();
                }else{
                    $templates = Template::where(['status'=>1])->where('file_type',$file_type)->where('title','LIKE','%'.$keyword.'%')->orWhere(function($query) use($keyword)
                                {
                                    $query->where('description','LIKE','%'.$keyword.'%')
                                    ->orwhere('add_desc','LIKE','%'.$keyword.'%');
                                })
                    ->whereNotNull('price')->get();
                }
            }


          foreach($templates as $key => $val){
            if($val->discount != 0){
                $price = $val->price /100*$val->discount;
                $price = $val->price-$price;
                $templates[$key]->product_price = $price;
            }else{
                $templates[$key]->product_price =$val->price;
            }
        }
        $date = Carbon::now();
          DB::table('search')->insert(['keyword' => $keyword,'created_at'=>$date]);
          return response()->json(['templates' => $templates,'status'=>200]);
     }

     public function get_currency(Request $request){
          $INR_rate = DB::table('rates')->where('country','INR')->first();
          $USD_rate = DB::table('rates')->where('country','USD')->first();
          $GBP_rate = DB::table('rates')->where('country','GBP')->first();
          $EURO_rate = DB::table('rates')->where('country','EURO')->first();
          $DIRHAM_rate = DB::table('rates')->where('country','DIRHAM')->first();
          return response()->json(['INR_rate' => $INR_rate,'USD_rate' => $USD_rate,'GBP_rate' => $GBP_rate,'EURO_rate' => $EURO_rate,'DIRHAM_rate' => $DIRHAM_rate,'status'=>200]);
     }


    public function view_banners(Request $request){
            $banners = DB::table('banners')->where(['collection'=>0,'mid_section'=>0])->where('status',1)->get();
            return response()->json(['banners'=>$banners, 'status' => 200]);
    }

    public function view_collection_banners(Request $request){
            $banners1 = DB::table('banners')->where('id',3)->first();
            $banners2 = DB::table('banners')->where('id',4)->first();
            $banners3 = DB::table('banners')->where('id',5)->first();
            $banners4 = DB::table('banners')->where('id',6)->first();
            return response()->json(['banners1'=>$banners1,'banners2'=>$banners2,'banners3'=>$banners3,'banners4'=>$banners4, 'status' => 200]);
    }

    public function view_mid_section_banners(Request $request){
            $banners1 = DB::table('banners')->where('id',14)->first();
            $banners2 = DB::table('banners')->where('id',15)->first();
            $banners3 = DB::table('banners')->where('id',16)->first();
            $banners4 = DB::table('banners')->where('id',17)->first();
            return response()->json(['banners1'=>$banners1,'banners2'=>$banners2,'banners3'=>$banners3,'banners4'=>$banners4, 'status' => 200]);
    }

    public function viewfiletypes(Request $request){
            $filetype = DB::table('templates')->select('file_type')->groupBy('file_type')->get();
            return response()->json(['filetype'=>$filetype, 'status' => 200]);
    }

    public function trending_templates(Request $request){
            $templates = DB::table('templates')->where('trending',1)->where('status',1)->get();
            foreach($templates as $key => $val){
                if($val->discount != 0){
                    $price = $val->price /100*$val->discount;
                    $price = $val->price-$price;
                    $templates[$key]->product_price = $price;
                }else{
                    $templates[$key]->product_price =$val->price;
                }
            }
            return response()->json(['templates'=>$templates, 'status' => 200]);
    }

    public function getstatus(Request $request,$id){
            $templates = DB::table('contributors_orders')->where('vendor_id',$id)->where('order_status','paid')->get();
            $total_sale = 0;
             foreach($templates as $template){
                $total_sale=$template->grand_total +$total_sale;
            }

            $total_order = DB::table('contributors_orders')->where('vendor_id',$id)->count();

            $total_templates = DB::table('templates')->where('vendor_id',$id)->count();

            return response()->json(['total_sale'=>$total_sale,'total_order'=>$total_order, 'total_templates'=>$total_templates, 'status' => 200]);
    }

    public function sold_templates(){
            $users =  DB::table('orders_products')->select('id', 'updated_at')->whereBetween('updated_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ])
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->updated_at)->format('m'); // grouping by months
            });
            $usermcount = [];
            $userArr = [];
            foreach ($users as $key => $value) {
                $usermcount[(int)$key] = count($value);
            }
            for($i = 1; $i <= 12; $i++){
                if(!empty($usermcount[$i])){
                    $userArr[] = $usermcount[$i];
                }else{
                    $userArr[] = 0;
                }
            }

            $order =  DB::table('orders')->select('id', 'updated_at')->whereBetween('updated_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ])
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->updated_at)->format('m'); // grouping by months
            });
            $ordermcount = [];
            $orderArr = [];
            foreach ($order as $key => $value) {
                $ordermcount[(int)$key] = count($value);
            }
            for($i = 1; $i <= 12; $i++){
                if(!empty($ordermcount[$i])){
                    $orderArr[] = $ordermcount[$i];
                }else{
                    $orderArr[] = 0;
                }
            }

            $templatescnt = DB::table('templates')->count();
            $orderscnt = DB::table('orders')->count();
            return response()->json(['userArr'=>$userArr,'orderArr'=>$orderArr,'templatescnt'=>$templatescnt,'orderscnt'=>$orderscnt, 'status' => 200]);
    }


    public function total_vendor_count(){
            $vendor = Vendor::select('id', 'created_at')->whereBetween('created_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ])
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
            $vendormcount = [];
            $vendorArr = [];
            foreach ($vendor as $key => $value) {
                $vendormcount[(int)$key] = count($value);
            }
            for($i = 1; $i <= 12; $i++){
                if(!empty($vendormcount[$i])){
                    $vendorArr[] = $vendormcount[$i];
                }else{
                    $vendorArr[] = 0;
                }
            }

            $users = User::select('id', 'created_at')->whereBetween('created_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ])
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
            $usermcount = [];
            $userArr = [];
            foreach ($users as $key => $value) {
                $usermcount[(int)$key] = count($value);
            }
            for($i = 1; $i <= 12; $i++){
                if(!empty($usermcount[$i])){
                    $userArr[] = $usermcount[$i];
                }else{
                    $userArr[] = 0;
                }
            }

            $vendorscnt = DB::table('vendors')->count();
            $usersscnt = DB::table('users')->count();
            return response()->json(['userArr'=>$userArr,'vendorArr'=>$vendorArr,'usersscnt'=>$usersscnt,'vendorscnt'=>$vendorscnt, 'status' => 200]);
    }


    public function vendor_sold_templates($id){
        $templates =  DB::table('orders_products')->select('id', 'updated_at')->where('vendor_id',$id)->whereBetween('updated_at', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->updated_at)->format('m'); // grouping by months
        });
        $templatemcount = [];
        $templateArr = [];
        foreach ($templates as $key => $value) {
            $templatemcount[(int)$key] = count($value);
        }
        for($i = 1; $i <= 12; $i++){
            if(!empty($templatemcount[$i])){
                $templateArr[] = $templatemcount[$i];
            }else{
                $templateArr[] = 0;
            }
        }
        $templates_id =  DB::table('orders_products')->select('order_id')->where('vendor_id',$id)->get();
        foreach($templates_id as $template_id){
            $ids[] = $template_id->order_id;
        }
        $order =  DB::table('orders')->select('id', 'updated_at')->whereIn('id',$ids)->whereBetween('updated_at', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->updated_at)->format('m'); // grouping by months
        });
        $ordermcount = [];
        $orderArr = [];
        foreach ($order as $key => $value) {
            $ordermcount[(int)$key] = count($value);
        }
        for($i = 1; $i <= 12; $i++){
            if(!empty($ordermcount[$i])){
                $orderArr[] = $ordermcount[$i];
            }else{
                $orderArr[] = 0;
            }
        }
        return response()->json(['templateArr'=>$templateArr,'orderArr'=>$orderArr, 'status' => 200]);
}

    public function vendor_total_sale($id){
        $templates =  DB::table('contributors_orders')->select('id', 'grand_total', 'updated_at')->where('vendor_id',$id)->where('order_status','paid')->whereBetween('updated_at', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->updated_at)->format('m'); // grouping by months
        });
        // dd($templates);
        $salemcount = [];
        $saleArr = [];
        // dd($templates);
        foreach ($templates as $key => $value) {
            $sale = 0 ;
            foreach ($value as $key1 => $value1) {
                $sale = $sale+$value1->grand_total;
            }
            $salemcount[(int)$key] = number_format((float)$sale, 2, '.', '');

        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($salemcount[$i])){
                $saleArr[] = $salemcount[$i];
            }else{
                $saleArr[] = 0;
            }
        }

        return response()->json(['saleArr'=>$saleArr, 'status' => 200]);
}

    public function view_blogs(Request $request){
        $blog =Blog::orderBy('created_at','DESC')->get();
        return response()->json(['blog'=>$blog, 'status' => 200]);
    }

    public function view_blog($id){
        $blog =Blog::where('id',$id)->first();
        return response()->json(['blog'=>$blog, 'status' => 200]);
    }

    public function get_template(Request $request,$id){
          $templates = Template::where('id',$id)->first();
          foreach($templates as $key => $val){
            $category = Category::where(['id'=>$templates->category])->first();
            $vendor= Vendor::where('id',$templates->vendor_id)->first();
            $templates->category_name = $category->name;
            $templates->uploaded_by = $vendor->username;
            }
            if($templates->discount != 0){
                $price = $templates->price /100*$templates->discount;
                $price = $templates->price-$price;
                $product_price = $price;
            }else{
                $product_price =$templates->price;
            }
          return response()->json(['templates' => $templates,'product_price' => $product_price,'status'=>200]);
     }
}
