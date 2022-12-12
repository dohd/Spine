<?php

namespace App\Http\Controllers\Focus\assetreturned;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Focus\assetreturned\AssetreturnedRepository;
use App\Models\hrm\Hrm;
use App\Models\assetreturned\Assetreturned;
use App\Models\assetreturned\AssetreturnedItems;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\Focus\assetreturned\CreateResponse;
use App\Http\Responses\Focus\assetreturned\EditResponse;
use App\Http\Responses\Focus\assetreturned\UpdateResponse;
use App\Http\Responses\ViewResponse;

class AssetreturnedController extends Controller
{
    /**
     * variable to store the repository object
     * @var AssetreturnedRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AssetreturnedRepository $repository ;
     */
    public function __construct(AssetreturnedRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       // dd($request->id);
        $assetissuance = $request->id;
        return new ViewResponse('focus.assetreturned.create', compact('assetissuance'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.assetreturned.index');
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
         $assetreturned = $request->only([
            'employee_id','employee_name','issue_date','return_date','note'
        ]);
        $assetreturned_items = $request->only([
            'item_id','actual_return_date', 'lost_items','qty_issued','broken','serial_number','name',
        ]);

        $assetreturned['ins'] = auth()->user()->ins;
        $assetreturned['user_id'] = auth()->user()->id;
        //$assetreturned_items['asset_returned_id'] = auth()->user()->id;
        // modify and filter items without item_id
        
        $assetreturned_items = modify_array($assetreturned_items);
        $assetreturned_items = array_filter($assetreturned_items, function ($v) { return $v['item_id']; });
        //dd($assetreturned_items);
        $result = $this->repository->create(compact('assetreturned', 'assetreturned_items'));

        return new RedirectResponse(route('biller.assetreturned.create'), ['flash_success' => 'Assetreturned Assetreturned created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Assetreturned $assetreturned, Request $request)
    {
        return new ViewResponse('focus.assetreturned.view', compact('assetreturned'));
    }

    public function shows($assetreturned, Request $request)
    {
        // $assetreturned = Assetreturned::find($id);
        // $budget = AssetreturnedItems::where('asset_returned_id', $assetreturned->id)->first();
        //dd($assetreturned);
        // if ($budget) return redirect(route('biller.assetreturned.edit_asset_return', [$assetreturned, $budget]));
        return new UpdateResponse($assetreturned);
        //return new ViewResponse('focus.assetreturned.edit_asset_return', compact('assetreturned'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Assetreturned $assetreturned, Request $request)
    {
        
        return new EditResponse($assetreturned);
    }
    public function edit_asset_return($id, $item_id)
    {
        $assetreturned = Assetreturned::find($id);
        $budget = AssetreturnedItems::find($item_id);
        //$budget = AssetreturnedItems::where('asset_returned_id', $assetreturned->id)->get();
        //dd($budget);
        //if ($budget) return redirect(route('biller.assetreturned.edit_asset_return', [$assetreturned, $budget]));
        
        //$budget_items = $budget->items()->orderBy('row_index')->get();

        return view('focus.assetreturned.edit_asset_return', compact('assetreturned', 'budget'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Assetreturned $assetreturned)
    {
        //dd($request->all());
        $data = $request->only([
            'employee_id','employee_name','issue_date','return_date','note'
        ]);
        $data_items = $request->only([
            'item_id','name', 'serial_number','qty_issued','id',
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_id']);
        
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);

        $assetreturned = $this->repository->update($assetreturned, compact('data', 'data_items'));

        $msg = 'Direct assetreturned Updated Successfully.';

        return new RedirectResponse(route('biller.assetreturned.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Assetreturned $assetreturned)
    {
        $this->repository->delete($assetreturned);

        return new RedirectResponse(route('biller.assetreturned.create'), ['flash_success' => 'Assetreturned deleted successfully']);        
   
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Hrm::where('first_name', 'LIKE', '%'.$q.'%')
            ->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get(['id', 'first_name', 'email']);

        return response()->json($users);
    }
    public function update_asset(Request $request, Assetreturned $budget)
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

        return new RedirectResponse(route('biller.assetreturned.index'), ['flash_success' => 'Assetreturned updated successfully']);
    }
    public function items()
    {
        return new ViewResponse('focus.assetreturned.items');
    }
}
