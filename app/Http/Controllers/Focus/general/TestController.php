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

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    /**
     * Test whether the login form displays
     */
    public function showLoginForm()
    {
        return '
            <form action="'.route('biller.test_login').'" method="POST">
                <input type="hidden" name="_token" value="'.csrf_token().'">
                <div><label>Email</label></div>
                <input type="text" name="email"><br>
                <div><label>Password</label></div>
                <input type="text" name="password">
                <button type="submit">Login</button>
            </form>
        ';

        // if no present file in install directory, redirect to installation page
        if (!file_exists(storage_path('installed'))) 
            return redirect()->to('install');

        // if authenticated user redirect to dashboard
        if (auth()->user()) return redirect()->route('biller.dashboard');
        
        return view('core.index');
    }
}
