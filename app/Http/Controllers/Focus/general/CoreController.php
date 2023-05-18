<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\general;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Auth\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class CoreController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo;

    public function redirectPath()
    {
        return session()->get('url_intended') ?: route('biller.dashboard');
    }

    public function showLoginForm()
    {
        return view('core.index');
    }

    /*
    * Check to see if the users account is confirmed and active
    */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->isConfirmed()) {
            access()->logout();
            trigger_error(trans('exceptions.frontend.auth.confirmation.resend', ['user_id' => $user->id]));
        }
        if (!$user->isActive()) {
            access()->logout();
            trigger_error(trans('exceptions.frontend.auth.deactivated'));
        }
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required_if:captcha_status,true|captcha',
        ], ['g-recaptcha-response.required_if' => 'Captcha Error']);
    }

    public function logout(Request $request)
    {
        if (!$request->auth) $this->redirectTo = session()->get('url_intended');

        // clear session
        if (app('session')->has(config('access.socialite_session_name'))) {
            app('session')->forget(config('access.socialite_session_name'));
        }
        app()->make(Auth::class)->flushTempSession();
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();

        session()->put('url_intended', $this->redirectTo);

        return redirect()->route('biller.index');
    }
}
