<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Support\Facades\Mail;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function confirmAccount($email){
        $email = base64_decode($email);
        $userCount = User::where('email',$email)->count();
        $vendorCount = Vendor::where('email',$email)->count();
        if($userCount > 0){
            $userDetails = User::where('email',$email)->first();
            if($userDetails->status == 1){
                return redirect('/response')->with('flash_message_success','Your Email account is already activated. You can login now.');
            }else{
                User::where('email',$email)->update(['status'=>1]);

                // Send Welcome Email
                $messageData = ['email'=>$email,'name'=>$userDetails->name];
                 Mail::send('emails.welcome',$messageData,function($message) use($email){
                    $message->to($email)->subject('Welcome to the Artaux family!- Artaux');
                });

                return redirect('/response')->with('flash_message_success','Your Email account is activated. You can login now.');
            }
        }elseif($vendorCount > 0){
            $vendorDetails = Vendor::where('email',$email)->first();
            if($vendorDetails->status == 1){
                return redirect('/response')->with('flash_message_success','Your Email account is already activated. You can login now.');
            }else{
                User::where('email',$email)->update(['status'=>1]);

                // Send Welcome Email
                $messageData = ['email'=>$email,'name'=>$userDetails->name];
                Mail::send('emails.welcome_contributor',$messageData,function($message) use($email){
                    $message->to($email)->subject('Preparing for take off - Artaux')->attach('images/EULA_Artaux.docx');;
                });

                return redirect('/response')->with('flash_message_success','Your Email account is activated. You can login now.');
            }
        }else{
            abort(404);
        }
    }

}
