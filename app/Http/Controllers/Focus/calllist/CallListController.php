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
use App\Http\Controllers\Focus\prospect_call_list\ProspectCallListController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\calllist\CreateResponse;
use App\Http\Responses\Focus\calllist\EditResponse;
use App\Repositories\Focus\calllist\CallListRepository;
use App\Http\Requests\Focus\calllist\CallListRequest;
use App\Models\branch\Branch;
use App\Models\calllist\CallList;
use App\Models\prospect_calllist\ProspectCallList;
use DB;
use Illuminate\Support\Carbon;

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
        $direct = Prospect::where('category', 'direct')->count();
        $excel = Prospect::select(DB::raw('title,COUNT("*") AS count '))->groupBy('title')->where('category', 'excel')->get();

        return view('focus.prospects.calllist.create', compact('direct', 'excel'));
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


        $res = $this->repository->create($data);

        //get call id
        $callid = $res['id'];
        //get prospects based on title
        $prospects = Prospect::where('call_status',0)->where('title',$res['title'])->limit($res['prospects_number'])->get([
            "id"
        ])->toArray();


        //start and end date  
        $start = $res['start_date'];
        $end = $res['end_date'];
        // Create an empty array to store the valid dates
        $validDates = [];
        $carbonstart = Carbon::parse($start);
        $carbonend = Carbon::parse($end);
        
        // Loop through each date in the range
        for ($date = $carbonstart; $date <= $carbonend; $date->addDay()) {
            // Check if the current date is not a Sunday
            if ($date->dayOfWeek != Carbon::SUNDAY) {
                // Add the date to the array of valid dates
                $validDates[] = $date->toDateString();
            }
        }
        $prospectcount = count($prospects);
        $dateCount = count($validDates);
        $prospectIndex = 0;
        $dateIndex = 0;

        $prospectcalllist = [];


        // Allocate the prospects to the valid dates

        while ($prospectIndex < $prospectcount && $dateIndex < $dateCount) {
            $prospect = $prospects[$prospectIndex]['id'];
            $date = $validDates[$dateIndex];
            $prospectcalllist[] = [
                "prospect_id" => $prospect,
                "call_id" => $callid,
                "call_date"=>$date
            ];
            $prospectIndex++;
            $dateIndex = ($dateIndex + 1) % $dateCount;
        }
    
        //dd($prospectcalllist);
        //send data to prospectcalllisttable
        ProspectCallList::insert($prospectcalllist);
        
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
    public function update(CallListRequest $request, CallList $calllist)
    {



        return new EditResponse('focus.calllists.edit', compact('calllist'));
    }
    public function show(CallList $calllist)
    {
     
        return new ViewResponse('focus.prospects.calllist.view', compact('calllist'));
    }

    public function mytoday()
    {
        
        return view('focus.prospects.calllist.mycalls');
    }
    public function allocationdays($id)
    {
       $calllist = ProspectCallList::where('call_id',$id)->get();
      
        return view('focus.prospects.calllist.allocationdays',compact('calllist'));
    }
    public function prospectviacalllist(Request $request)
    {
      
        $prospects = ProspectCallList::whereMonth('call_date', $request->month)
        ->whereDay('call_date', $request->day)
        ->with(['prospect' => function ($q) {
            $q->select('id', 'title', 'company','industry','contact_person','email','phone','region','call_status');
        }])
        ->get();
      
    return response()->json($prospects);
    }


}