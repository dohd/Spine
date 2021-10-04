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


class TestController extends Controller
{
    
    public function showLoginForm()
    {
    	dd(2222);
        if (!file_exists(storage_path('installed'))) return redirect()->to('install');
        if (auth()->user()) {
            // Authentication passed...
            return redirect()->route('biller.dashboard');
        }
        return view('core.index');
    }

    

   
}
