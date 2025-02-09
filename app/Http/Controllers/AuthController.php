<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Helpers\CMAIL;

class AuthController extends Controller
{
    public function loginform()
    {
        return view('backend.pages.auth.login', ['pageTitle' => 'Login']);
    }

    public function forgotForm()
    {
        return view('backend.pages.auth.forgot', ['pageTitle' => 'Forgot Password']);
    }

    public function loginHandler(Request $request)
    {
        $fieldType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $request->validate([
            'login_id' => 'required|exists:users,' . $fieldType,
            'password' => 'required|min:5',
        ], [
            'login_id.required' => 'Enter your email or username',
            'login_id.exists' => 'No account found for this ' . $fieldType,
            'password.required' => 'Password is required',
        ]);

        $creds = [
            $fieldType => $request->login_id,
            'password' => $request->password,
        ];

        if (Auth::attempt($creds)) {
            $user = auth()->user();

            if ($user->status == 'inactive') {
                Auth::logout();
                return redirect()->route('admin.login')->with('fail', 'Your account is inactive. Contact support.');
            }

            if ($user->status == 'pending') {
                Auth::logout();
                return redirect()->route('admin.login')->with('fail', 'Your account is pending approval.');
            }

            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login')->with('fail', 'Incorrect credentials.');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email is required',
            'email.exists' => 'No account found for this email',
        ]);

        $user = User::where('email', $request->email)->first();
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $actionLink = route('admin.reset_password_form', ['token' => $token]);

        $data = [
            'actionlink' => $actionLink,
            'user' => $user
        ];

        $mail_body = view('email-templates.forgot-template', $data)->render();

        $mailConfig = [
            'recipient_address' => $user->email,
            'recipient_name' => $user->name,
            'subject' => 'Reset Password',
            'body' => $mail_body
        ];

        if (CMAIL::send($mailConfig)) {
            return redirect()->route('admin.forgot')->with('success', 'Reset link sent to your email.');
        }

        return redirect()->route('admin.forgot')->with('fail', 'Error sending reset link. Try again later.');
    }

    public function resetForm(Request $request, $token = null)
    {
        $isTokenExists = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (!$isTokenExists) {
            return redirect()->route('admin.forgot')->with('fail', 'Invalid token. Request another reset password link. ');
        } else {
            //check if Token is not expired
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s',$isTokenExists->created_at)->diffInMinutes(Carbon::now());

            if( $diffMins > 15){
                return redirect()->return('admin.forgot')->with('fail','The password reset link you clicked has expired. Please request a new link.');
            }
            $data = [
                'pageTitle' => 'Reset Password',
                'token' => $token
            ];
        }

        return view('backend.pages.auth.reset', $data);
    }//end method

    public function resetPasswordHandler(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:5|required_with:new_password_confirmation|same:new_password_confirmation',
            'new_password_confirmation' => 'required'
        ]);

        $dbToken = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$dbToken) {
            return redirect()->route('admin.forgot')->with('fail', 'Invalid or expired reset token.');
        }

        // Get User details
        $user = User::where('email', $dbToken->email)->first();

        if (!$user) {
            return redirect()->route('admin.forgot')->with('fail', 'User not found.');
        }

        // Update Password
        User::where('email', $user->email)->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Send notification email to user about password change
        $data = [
            'user' => $user,
            'new_password' => $request->new_password
        ];

        $mail_body = view('email-templates.password-changes-template', $data)->render();

        $mailConfig = [
            'recipient_address' => $user->email,
            'recipient_name' => $user->name,
            'subject' => 'Password Changed',
            'body' => $mail_body
        ];

        if (CMAIL::send($mailConfig)) {
            // Delete token from DB after successful reset
            DB::table('password_reset_tokens')->where([
                'email' => $dbToken->email,
                'token' => $dbToken->token
            ])->delete();

            return redirect()->route('admin.login')->with('success', 'Your password has been changed successfully. Use your new password to log in.');
        } else {
            return redirect()->route('admin.reset_password_form', ['token' => $dbToken->token])->with('fail', 'Something went wrong. Try again later.');
        }
    }

}





