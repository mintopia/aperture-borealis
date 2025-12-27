<?php

namespace App\Http\Controllers;

use App\Exceptions\SocialProviderException;
use App\Models\SocialProvider;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->regenerate(true);
        return response()->redirectToRoute('home')->with('successMessage', 'You have been logged out');
    }

    public function loginRedirect(SocialProvider $socialprovider)
    {
        if (!$socialprovider->enabled) {
            return response()->redirectToRoute('login')->with('errorMessage', 'Unable to login');
        }
        return $socialprovider->redirect();
    }

    public function loginReturn(SocialProvider $socialprovider)
    {
        // If we get 2 redirects back from the auth provider, handle it here.
        if (Auth::hasUser()) {
            return response()->redirectToIntended(route('home'))->with('successMessage', 'You have been logged in');
        }
        if (!$socialprovider->enabled) {
            return response()->redirectToRoute('login')->with('errorMessage', 'Unable to login');
        }
        try {
            $user = $socialprovider->user();
            if ($user !== null) {
                Auth::login($user);
                $user->last_login_at = CarbonImmutable::now();
                $user->save();
                return response()->redirectToIntended(route('home'))->with('successMessage', 'You have been logged in');
            }
        } catch (SocialProviderException $ex) {
            return response()->redirectToRoute('login')->with('errorMessage', $ex->getMessage());
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
        return response()->redirectToRoute('login')->with('errorMessage', 'Unable to login');
    }

    public function login()
    {
        $providers = SocialProvider::whereEnabled(true)->get();
        return view('users.login', [
            'providers' => $providers,
        ]);
    }
}
