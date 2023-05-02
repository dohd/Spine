<?php

namespace App\Http\Controllers\Focus\assetissuance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Focus\assetissuance\AssetissuanceRepository;
use App\Models\hrm\Hrm;
use App\Models\assetissuance\Assetissuance;
use App\Models\product\ProductVariation;
use App\Models\assetissuance\AssetissuanceItems;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\Focus\assetissuance\CreateResponse;
use App\Http\Responses\Focus\assetissuance\EditResponse;
use App\Http\Responses\Focus\assetissuance\UpdateResponse;
use App\Http\Responses\ViewResponse;

class AssetissuanceController extends Controller
{
    /**
     * variable to store the repository object
     * @var AssetissuanceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AssetissuanceRepository $repository ;
     */
    public function __construct(AssetissuanceRepository $repository)
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
        return new ViewResponse('focus.assetissuance.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = Hrm::all();
        return view('focus.assetissuance.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());
         // extract input fields
         $assetissuance = $request->only([
            'employee_id',
            'employee_name',
            'issue_date',
            'return_date',
            'note', 
            'acquisition_number',
            
        ]);
        $assetissuance_items = $request->only([
            'item_id','name', 
            'serial_number',
            'qty_issued','quantity',
            'purchase_price'
        ]);
        
        $assetissuance['ins'] = auth()->user()->ins;
        $assetissuance['user_id'] = auth()->user()->id;
        //$assetissuance_items['asset_issuance_id'] = auth()->user()->id;
        $subtracted = array_map(function ($x, $y) { 
            if($y - $x < 0){
                $x=$y;
                return $x;
            }
            else{
                return $x;
            }
            //return $y-$x;
         } , $assetissuance_items['qty_issued'], $assetissuance_items['quantity']);
        $quantity_issued = array_combine(array_keys($assetissuance_items['quantity']), $subtracted);
        // modify and filter items without item_id
        $assetissuance_items['qty_issued'] = $quantity_issued;
       // dd($assetissuance_items['item_id']);
        
        //dd($product_variations);
        foreach ($assetissuance_items['item_id'] as $key => $value) {
            //dd($key);
            $product_variations = ProductVariation::where('id', $value)->get()->first();
            $product_variations->qty = $product_variations->qty - $quantity_issued[$key];
            $product_variations->update();
            //dd($product_variations);
        }
        $assetissuance_items = modify_array($assetissuance_items);
        $assetissuance_items = array_filter($assetissuance_items, function ($v) { return $v['item_id']; });
       // $assetissuance_items['purchase_price'] = $assetissuance_items['purchase_price'] * $assetissuance_items['qty_issued'];
        
        $result = $this->repository->create(compact('assetissuance', 'assetissuance_items'));

        return new RedirectResponse(route('biller.assetissuance.index'), ['flash_success' => 'Assetissuance Assetissuance created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Assetissuance $assetissuance, Request $request)
    {
        return new ViewResponse('focus.assetissuance.view', compact('assetissuance'));
    }

    public function shows(Request $request)
    {
        // $assetissuance = Assetissuance::find($id);
        // $budget = AssetissuanceItems::where('asset_issuance_id', $assetissuance->id)->first();
        //dd($assetissuance);
        // if ($budget) return redirect(route('biller.assetissuance.edit_asset_return', [$assetissuance, $budget]));
        return new UpdateResponse($assetissuance);
        //return new ViewResponse('focus.assetissuance.edit_asset_return', compact('assetissuance'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Assetissuance $assetissuance, Request $request)
    {
        return new EditResponse($assetissuance);
    }
    public function edit_asset_return($id, $item_id)
    {
        $assetissuance = Assetissuance::find($id);
        $budget = AssetissuanceItems::find($item_id);
        //$budget = AssetissuanceItems::where('asset_issuance_id', $assetissuance->id)->get();
        //dd($budget);
        //if ($budget) return redirect(route('biller.assetissuance.edit_asset_return', [$assetissuance, $budget]));
        
        //$budget_items = $budget->items()->orderBy('row_index')->get();

        return view('focus.assetissuance.edit_asset_return', compact('assetissuance', 'budget'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Assetissuance $assetissuance)
    {
        //dd($request->all());
        $data = $request->only([
            'employee_id','employee_name','issue_date','return_date','note','acquisition_number'
        ]);
        $data_items = $request->only([
            'item_id','name', 'serial_number','qty_issued','id','purchase_price'
        ]);

        //dd($data_items);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $assetissuance['issue_date'] = $data['issue_date'];
        $assetissuance['return_date'] = $data['return_date'];


        $data_items = modify_array($data_items);
        //dd($data_items);

        $data_items = array_filter($data_items, fn($v) => $v['item_id']);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);
        //dd($assetissuance);
        $assetissuance = $this->repository->update($assetissuance, compact('data', 'data_items'));

        $msg = 'Direct assetissuance Updated Successfully.';

        return new RedirectResponse(route('biller.assetissuance.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Assetissuance $assetissuance)
    {

        $this->repository->delete($assetissuance);

        return new RedirectResponse(route('biller.assetissuance.index'), ['flash_success' => 'Assetissuance deleted successfully']);        
   
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Hrm::where('first_name', 'LIKE', '%'.$q.'%')
            ->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get(['id', 'first_name', 'email']);

        return response()->json($users);
    }
    public function update_asset(Request $request, Assetissuance $budget)
    {
        // extract request input
        $data = $request->only('employee_id','employee_name','issue_date','return_date','note');
        $data_items = $request->only(
            'item_id','actual_return_date', 'lost_items','qty_issued','broken','id'
        );
        //dd($data_items);
       // $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        //$data_skillset = modify_array($data_skillset);

        $this->repository->update_asset($budget, compact('data', 'data_items'));

        return new RedirectResponse(route('biller.assetissuance.index'), ['flash_success' => 'AssetIssuance updated successfully']);
    }
}
