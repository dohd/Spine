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
use DateTime;
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
        $direct = Prospect::where('call_status','notcalled')->where('category', 'direct')->count();
        $excel = Prospect::select(DB::raw('title,COUNT("*") AS count '))->groupBy('title')->where('call_status','notcalled')->where('category', 'excel')->get();

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
    
       $restitle = $res['title'];
       $restitle = strstr($restitle, ' ', true);
       
        //get call id
        $callid = $res['id'];
        //get prospects based on title
        $prospects = Prospect::where('call_status','notcalled')->where('title',$restitle)->get([
            "id"
        ])->toArray();

        //dd($prospects);
        //start and end date  
        $start = $res['start_date'];
        $end = $res['end_date'];
        // Create an empty array to store the valid dates
        $validDates = [];
        $carbonstart = Carbon::parse($start);
        $carbonend = Carbon::parse($end);
        
        // Loop through each date in the range
        for ($date = $carbonstart; $date <= $carbonend; $date->addDay()) {
            // Check if the current date is not a Sunday or Saturday
            if ($date->isWeekday()) {
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
        // //send data to prospectcalllisttable
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
    public function destroy(CallList $calllist)
    {
        $this->repository->delete($calllist);

        return new RedirectResponse(route('biller.calllists.index'), ['flash_success' => 'CallList Deleted Successfully']);
    }
    public function show(CallList $calllist)
    {
     
        return new ViewResponse('biller.calllists.index', compact('calllist'));
    }

    public function mytoday()
    {
        $calllists = CallList::all();
       
        return view('focus.prospects.calllist.mycalls',compact('calllists'));
    }
    public function allocationdays($id)
    {
       $titles =  Prospect::select('title')->distinct('title')->get();
       $calllist = CallList::find($id);
       $daterange ="Days With Prospects ".Carbon::parse($calllist->start_date)->format('Y-m-d')." To ".Carbon::parse($calllist->end_date)->format('Y-m-d');
       $start = Carbon::parse($calllist->start_date)->format('n');
       $end =Carbon::parse($calllist->end_date)->format('n');
        $id = $calllist->id;

        return view('focus.prospects.calllist.allocationdays',compact('id','start','end','daterange','titles'));
    }
    public function prospectviacalllist(Request $request)
    {
      
       
        $prospects = ProspectCallList::where('call_id',$request->id)->whereMonth('call_date', $request->month)
        ->whereDay('call_date', $request->day)
        ->with(['prospect' => function ($q) {
            $q->select('id', 'title', 'company','industry','contact_person','email','phone','region','call_status');
        }])
        ->get();
        $prospectstotal = ProspectCallList::where('call_id',$request->id)->whereMonth('call_date', $request->month)
        ->whereHas('prospect', function ($q) {
                $q->select('id','call_status')->where('is_called',0)->orWhere('is_called',1);
            })
       
        ->get()
        ->toArray();
        $total_call_group = array_reduce($prospectstotal, function ($init, $curr) {
            $d = (new DateTime($curr['call_date']))->format('j');
            $key_exists = in_array($d, array_keys($init));
            if (!$key_exists) $init[$d] = array();
            $init[$d][] = $curr['prospect_id'];
            
            return $init;
        }, []);
        $total_day_call = array();
        foreach ($total_call_group as $key => $val) {
            $total_day_call[] = array(
                'day' => $key,
                'count' => count(array_unique($val))
            );
        }
          
        $not = ProspectCallList::where('call_id',$request->id)->whereMonth('call_date', $request->month)
        ->whereHas('prospect', function ($q) {
                $q->select('id','call_status')->where('is_called',0);
            })
       
        ->get()->toArray();


        $day_call_group = array_reduce($not, function ($init, $curr) {
            $d = (new DateTime($curr['call_date']))->format('j');
            $key_exists = in_array($d, array_keys($init));
            if (!$key_exists) $init[$d] = array();
            $init[$d][] = $curr['prospect_id'];
            
            return $init;
        }, []);
        $day_call = array();
        foreach ($day_call_group as $key => $val) {
            $day_call[] = array(
                'day' => $key,
                'count' => count(array_unique($val))
            );
        }
        
     return response()->json(['notcalled'=>$day_call,'prospectstotal'=>$total_day_call,'prospects'=>$prospects]);
    }

    public function unallocatedbytitle($title){
        
    }
   

}
