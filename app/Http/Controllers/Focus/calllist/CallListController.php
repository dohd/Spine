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

namespace App\Http\Controllers\Focus\calllist;

use App\Models\prospect\Prospect;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\calllist\CreateResponse;
use App\Http\Responses\Focus\calllist\EditResponse;
use App\Repositories\Focus\calllist\CallListRepository;
use App\Http\Requests\Focus\calllist\CallListRequest;
use App\Models\branch\Branch;
use App\Models\calllist\CallList;
use DB;

/**
 * CallListController
 */
class CallListController extends Controller
{
    /**
     * variable to store the repository object
     * @var CallListRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CallListRepository $repository ;
     */
    public function __construct(CallListRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {

       
        
        return new ViewResponse('focus.prospects.calllist.index');
        //return new ViewResponse('focus.prospects.calllist.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\calllist\CreateResponse
     */
    public function create()
    {
        $direct = Prospect::where('category','direct')->count();
        $excel = Prospect::select(DB::raw('title,COUNT("*") AS count '))->groupBy('title')->where('category','excel')->get();
        
        return view('focus.prospects.calllist.create', compact('direct','excel'));
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(CallListRequest $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);

       
         $this->repository->create($data);
        

        // $calllists = CallList::where('prospect_id', $request->prospect_id)->orderBy('created_at', 'DESC')->limit(10)->get();
        return view('focus.prospects.calllist.index');
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\calllist\CallList $calllist
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(CallList $calllist)
    {
        $branches = Branch::get(['id', 'name', 'customer_id']);


        return new EditResponse('focus.calllists.edit', compact('calllist', 'branches'));
    }
    public function update(CallListRequest $request,CallList $calllist)
    {
      
        

        return new EditResponse('focus.calllists.edit', compact('calllist'));
    }
    public function show(CallList $calllist)
    {
        return new ViewResponse('focus.prospects.calllist.view', compact('calllist'));
    }

    public function mytoday(){
        $called = CallList::where('call_status', 1)->count();
        $not_called = CallList::where('call_status', 0)->count();
        $total_prospect = CallList::count();
        
        return new ViewResponse('focus.prospects.calllist.mycalls', compact('called', 'not_called', 'total_prospect'));
    }
}
