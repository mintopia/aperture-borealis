<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class PasskeyController extends Controller
{
    public function assertionOptions(AssertionRequest $request)
    {
        return $request->toVerify();
    }

    public function verify(AssertedRequest $request)
    {
        $user = $request->login();

        if (!$user || !$user->is_admin) {
            abort(403, "You don't have admin access.");
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function registerOptions(AttestationRequest $request)
    {
        return $request->toCreate();
    }

    public function register(AttestedRequest $request)
    {
        $request->save();

        return back()->with('success', 'Passkey registered successfully.');
    }
}
