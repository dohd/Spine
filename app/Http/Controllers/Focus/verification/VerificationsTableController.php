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
namespace App\Http\Controllers\Focus\verification;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\verification\VerificationRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class VerificationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var VerificationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param VerificationRepository $repository ;
     */
    public function __construct(VerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('tid', function ($verification) {
                return;
            })
            ->addColumn('actions', function ($verification) {
                return $verification->action_buttons;
            })
            ->make(true);
    }
}