<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\UserStatus;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Helpers\CMAIL;

class AuthController extends Controller
{
    //
    public function loginform(Request $request)
    {
        $data = [
            'pageTitle' => 'login'
        ];
        return view('backend.pages.auth.login', $data);
    }
    public function forgotForm(Request $request)
    {
        $data = [
            'pageTitle' => 'Forgot Password'
        ];
        return view('backend.pages.auth.forgot', $data);
    }

    public function loginHandler(Request $request)
    {
        $fieldType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if ($fieldType == 'email') {
            $request->validate([
                'login_id' => 'required|email|exists:users,email',
                'password' => 'required|min:5'
            ], [
                'login_id.required' => 'Enter your email or username',
                'login_id.email' => 'Invalid email address',
                'login_id.exists' => 'No account found for this email'
            ]);
        } else {
            $request->validate([
                'login_id' => 'required|exists:users,username',
                'password' => 'required|min:5'
            ], [
                'login_id.required' => 'Enter your username or email',
                'login_id.exists' => 'No account found for this username'
            ]);
        }

        $creds = array(
            $fieldType => $request->login_id,
            'password' => $request->password,
        );

        // First attempt to login
        if (Auth::attempt($creds)) {
            // Check if account is inactive
            if (auth()->user()->status == UserStatus::Inactive) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('admin.login')->with('fail', 'Your account is currently inactive. Please, contact support at support@blogsystem.com for further assistance.');
            }

            // Second attempt to login after session regeneration
            if (Auth::attempt($creds)) {
                // Check if account is pending
                if (auth()->user()->status == UserStatus::Pending) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('admin.login')->with('fail', 'Your account is currently Pending approval. Please, contact support at support@blogsystem.com for further assistance.');
                }

                // If login is successful
                return redirect()->route('admin.dashboard');
            }
        }

        // If the login attempt fails (incorrect password)
        return redirect()->route('admin.login')->withInput()->with('fail', 'Incorrect password.');
    }



    public function sendPasswordResetLink(Request $request){
        $request->validate([
            'email'=>'required|email|exists:users,email'
        ],[
            'email.required'=>'The :attribute is required',
            'email.email'=>'Invalid email address',
            'email.exists'=>'We cannot find a user with this email address',
        ]);

    //Get user details
    $user = User::where('email',$request->email)->first();

    //Generate Token
    $token = base64_encode(Str::random(64));

    //Check if there is an existing token
    $oldToken = DB::table('password_reset_tokens')
                    ->where('email',$user->email)
                    ->first();

    if( $oldToken)
        //Update existing Token
        DB::table('password_reset_tokens')
        ->where('email',$user->email)
        ->update([
            'token'=>$token,
            'created_at'=>Carbon::now()
        ]);
        else{
            DB::table('password_reset_tokens')->insert([
                'email'=>$user->email,
                'token'=>$token,
                'created_at'=>Carbon::now()
            ]);
        }

        //create clickable actionable link
        $actionLink = route('admin.reset_password_form',['token'=>$token]);

        $data = array(
            'actionlink'=>$actionLink,
            'user'=>$user
        );

        $mail_body = view('email-templates.forgot-template', $data)->render();

        $mailConfig = [
            'recipient_address' => $user->email,
            'recipient_name'    => $user->name,
            'subject'          => 'Reset Password',
            'body'             => $mail_body
        ];

        if (CMail::send($mailConfig)) {
            return redirect()->route('admin.forgot')->with('success', 'We have e-mailed your password reset link.');
        } else {
            return redirect()->route('admin.forgot')->with('fail', 'Something went wrong. Resetting password link not sent. Try again later.');
        }
    }

}

