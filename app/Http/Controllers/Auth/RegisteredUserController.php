<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view or redirect when registration is temporarily suspended.
     */
    public function create()
    {
        $notification = [
            'message' => 'Registration is temporarily suspended / التسجيل مغلق مؤقتاً.',
            'alert-type' => 'warning'
        ];

        return redirect()->route('login')->with($notification);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $notification = [
            'message' => 'Registration is temporarily suspended / التسجيل مغلق مؤقتاً.',
            'alert-type' => 'warning'
        ];

        return redirect()->route('login')->with($notification);
    }
}
