<?php

namespace App\Http\Controllers\Focus\surcharge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\hrm\Hrm;
use App\Models\surcharge\Surcharge;
use App\Models\assetreturned\Assetreturned;
use App\Models\assetissuance\Assetissuance;
use App\Models\assetissuance\AssetissuanceItems;
use App\Models\assetreturned\AssetreturnedItems;
use App\Http\Responses\RedirectResponse;
use App\Models\surcharge\SurchargeItems;
use App\Repositories\Focus\surcharge\SurchargeRepository;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\surcharge\CreateResponse;
use App\Http\Responses\Focus\surcharge\EditResponse;
use Carbon\Carbon;

class SurchargeController extends Controller
{
     /**
     * variable to store the repository object
     * @var SurchargeRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SurchargeRepository $repository ;
     */
    public function __construct(SurchargeRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.surcharge.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.surcharge.index_view');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       // dd($request->all());
        $surcharge = $request->only([
            'employee_id',
            'employee_name',
            'date',
            'issue_type',
            'months',
            'cost',
            
        ]);
        $surcharge_items = $request->only([
            'costpermonth',
            'datepermonth',

        ]);
        
        $surcharge['ins'] = auth()->user()->ins;
        $surcharge['user_id'] = auth()->user()->id;
        $surcharge_items = modify_array($surcharge_items);
        
        $result = $this->repository->create(compact('surcharge', 'surcharge_items'));

        return new RedirectResponse(route('biller.surcharges.index'), ['flash_success' => 'Surcharge created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Surcharge $surcharge, Request $request)
    {
        return new ViewResponse('focus.surcharge.view', compact('surcharge'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Surcharge $surcharge, Request $request)
    {
        return new EditResponse($surcharge);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Surcharge $surcharge)
    {
       // dd($request->all());
        $data = $request->only([
            'employee_id',
            'employee_name',
            'date',
            'issue_type',
            'months',
            'cost',
        ]);
        $data_items = $request->only([
            'costpermonth',
            'datepermonth',
            'id',
        ]);

        //dd($data_items);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;


        $data_items = modify_array($data_items);
        //dd($data_items);

        //$data_items = array_filter($data_items, fn($v) => $v['item_id']);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);
        //dd($surcharge);
        $surcharge = $this->repository->update($surcharge, compact('data', 'data_items'));
       
        $msg = 'Surcharge Updated Successfully.';

        return new RedirectResponse(route('biller.surcharges.create'), ['flash_success' => $msg]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Hrm::where('first_name', 'LIKE', '%'.$q.'%')
            ->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get(['id', 'first_name', 'email']);

        return response()->json($users);
    }
    public function get_issuance(Request $request)
    {
        //dd($request->all());
        $employee_id = $request->employee_id;
        $issue_type = $request->issue_type;
        $assetissuance = Assetissuance::where('employee_id', $employee_id)->get();
        if($issue_type == 'lost_broken'){
            $total_cost = $assetissuance->sum('total_cost');
            $payable = $assetissuance->sum('surcharge_payable');
            $dates = [];

                // Get the current date
                $currentDate = Carbon::parse($request->date);

                $dates[] = $currentDate->format('Y-m-d');

                // Generate 5 dates per month
                for ($i = 1; $i < $request->months; $i++) {
                    // Add $i months to the current date
                    $date = $currentDate->addMonthNoOverflow();

                    // Add the date to the array
                    $dates[] = $date->toDateString();
                }
                foreach ($dates as $date) {
                    
                }
                $month_installment = 0;
                $result = [];
                
                if($request->cost_type == 'total_cost'){
                    $month_installment = $total_cost / $request->months;
                    $result = array_map(function($date) use ($month_installment) {
                        return [
                            'date' => $date,
                            'month_installment' => $month_installment
                        ];
                    }, $dates);
                    
                }else if($request->cost_type == 'payable'){
                    $month_installment = $payable / $request->months;
                    $result = array_map(function($date) use ($month_installment) {
                        return [
                            'date' => $date,
                            'month_installment' => $month_installment
                        ];
                    }, $dates);
                }
                
                //dd($result);
            
        }

        return response()->json($result);
    }

    public function load($employeeId)
    {
        $employees_issued = Assetissuance::where('employee_id',$employeeId)->get();
        $cost = Assetissuance::where('employee_id',$employeeId)->get()->sum('total_cost');
        return view('focus.surcharge.load_issued', compact('employees_issued','cost'));
    }

    public function load_items($requisition)
    {
        $items = Assetreturned::where('acquisition_number',$requisition)->first();
        //dd($items->item());
        $cost = Assetreturned::where('acquisition_number',$requisition)->first()->item()->where('qty_issued', '>', '0')->sum('purchase_price');
        

        return view('focus.surcharge.items_issued', compact('items','cost'));
    }
    public function send(Request $request)
    {
        if ($request->ajax()) {
            $assetreturned = Assetreturned::where('id',$request->assetId)->first();
            $assetissuance = Assetissuance::where('acquisition_number',$assetreturned->acquisition_number)->first();
            $assetissuance->surcharge_payable = $request->pay_price;
            $assetissuance->update();
        }
    }
    public function pay(Request $request, $items)
    {
        $data_items = $request->only([
            'itemId',
            'cost',
            'asset_returned_id',
        ]);
        $data_items = modify_array($data_items);
        foreach ($data_items as $item) {         
            $surcharge_item = AssetreturnedItems::where('asset_returned_id', $item['asset_returned_id'])->where('item_id',$item['itemId'])->get()->first();  
            $surcharge_item->pay_price = $item['cost'];
            $surcharge_item->update();
        }
        $items = Assetreturned::where('id', $items)->first();
       // dd($items);
        return redirect()->route('biller.surcharge.load_items',[$items->acquisition_number]);
    }
}
