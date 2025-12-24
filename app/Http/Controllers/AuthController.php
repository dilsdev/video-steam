<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        // Turnstile CAPTCHA validation
        if (!$this->validateTurnstile($request)) {
            return back()->withErrors([
                'cf-turnstile-response' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Check for redirect parameter first
            $redirectUrl = $request->input('redirect');
            if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($redirectUrl);
                $appUrl = parse_url(config('app.url'));
                if (isset($parsedUrl['host']) && $parsedUrl['host'] === $appUrl['host']) {
                    return redirect($redirectUrl);
                }
            }

            // Redirect based on role
            $user = auth()->user();
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->isUploader()) {
                return redirect()->intended(route('uploader.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Show register form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        // Turnstile CAPTCHA validation
        if (!$this->validateTurnstile($request)) {
            return back()->withErrors([
                'cf-turnstile-response' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:viewer,uploader',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'viewer',
            'verification_token' => Str::random(64),
        ]);

        Auth::login($user);

        // Check for redirect parameter first
        $redirectUrl = $request->input('redirect');
        if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($redirectUrl);
            $appUrl = parse_url(config('app.url'));
            if (isset($parsedUrl['host']) && $parsedUrl['host'] === $appUrl['host']) {
                return redirect($redirectUrl)->with('success', 'Registrasi berhasil!');
            }
        }

        if ($user->isUploader()) {
            return redirect()->route('uploader.dashboard')
                ->with('success', 'Registrasi berhasil! Selamat datang di Video Platform.');
        }

        return redirect()->route('home')
            ->with('success', 'Registrasi berhasil!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Validate Cloudflare Turnstile response
     */
    private function validateTurnstile(Request $request): bool
    {
        $secretKey = config('services.turnstile.secret_key');
        
        // Skip validation if Turnstile is not configured
        if (empty($secretKey)) {
            return true;
        }

        $token = $request->input('cf-turnstile-response');
        
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();
            
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            // Log error and allow through if Turnstile API fails
            \Log::error('Turnstile validation failed: ' . $e->getMessage());
            return true; // Fail open to not block legitimate users
        }
    }
}
