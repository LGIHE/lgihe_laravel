<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordSetupController extends Controller
{
    /**
     * Show the password setup form.
     */
    public function showSetupForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return view('auth.password-setup-error', [
                'error' => 'Invalid password setup link. Please contact support.'
            ]);
        }

        // Verify the token exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            return view('auth.password-setup-error', [
                'error' => 'User not found. Please contact support.'
            ]);
        }

        // Check if token is valid
        $tokenExists = Password::broker()->tokenExists($user, $token);

        if (!$tokenExists) {
            return view('auth.password-setup-error', [
                'error' => 'This password setup link has expired or is invalid. Please contact your administrator to resend the link.'
            ]);
        }

        return view('auth.password-setup', [
            'token' => $token,
            'email' => $email,
            'user' => $user
        ]);
    }

    /**
     * Handle the password setup submission.
     */
    public function setupPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        // Attempt to reset the user's password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Log the user in
                Auth::login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => [__($status)]]);
        }

        return redirect()->route('password.setup.success');
    }

    /**
     * Show the success page after password setup.
     */
    public function showSuccessPage()
    {
        if (!Auth::check()) {
            return redirect()->route('password.setup.form');
        }

        $user = Auth::user();
        $canAccessPanel = $user->canAccessPanel(\Filament\Facades\Filament::getCurrentPanel());

        return view('auth.password-setup-success', [
            'user' => $user,
            'canAccessPanel' => $canAccessPanel
        ]);
    }
}
