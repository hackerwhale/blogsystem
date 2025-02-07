<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\UserStatus;
use App\Models\UserType;

class AuthController extends Controller
{
    //
    public function loginform(Request $request){
        $data = [
            'pageTitle'=>'login'
        ];
        return view('backend.pages.auth.login',$data);
   }
   public function forgotForm(Request $request){
    $data = [
        'pageTitle'=>'Forgot Password'
    ];
    return view('backend.pages.auth.forgot',$data);
   }

   public function loginHandler(Request $request) {
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

}

