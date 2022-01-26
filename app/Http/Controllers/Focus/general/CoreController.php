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

use App\Events\Frontend\Auth\UserLoggedOut;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Auth\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class CoreController extends Controller
{
    use AuthenticatesUsers;

    public function redirectPath()
    {
        return route('biller.dashboard');
    }

    public function showLoginForm()
    {
        // if (!file_exists(storage_path('installed'))) 
        //     return redirect()->to('install');

        if (auth()->user()) 
            return redirect()->route('biller.dashboard');
        
        return view('core.index');
    }

    /*
    * Check to see if the users account is confirmed and active
    */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->isConfirmed()) {
            access()->logout();
            throw new GeneralException(
                trans('exceptions.frontend.auth.confirmation.resend', 
                ['user_id' => $user->id]), 
                true
            );
        } 
        if (!$user->isActive()) {
            access()->logout();
            throw new GeneralException(trans('exceptions.frontend.auth.deactivated'));
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
        if (app('session')->has(config('access.socialite_session_name'))) {
            app('session')->forget(config('access.socialite_session_name'));
        }
        app()->make(Auth::class)->flushTempSession();
        /*
         * Fire event, Log out user, Redirect
         */
        // event(new UserLoggedOut($this->guard()->user()));
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('biller.index');
    }
}
