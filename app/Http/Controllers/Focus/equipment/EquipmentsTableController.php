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

namespace App\Http\Controllers\Focus\equipment;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\equipment\EquipmentRepository;
use App\Http\Requests\Focus\equipment\ManageEquipmentRequest;

/**
 * Class BranchTableController.
 */
class EquipmentsTableController extends Controller
{
  /**
   * variable to store the repository object
   * @var EquipmentRepository
   */
  protected $repository;

  /**
   * contructor to initialize repository object
   * @param EquipmentRepository $repository ;
   */
  public function __construct(EquipmentRepository $repository)
  {

    $this->repository = $repository;
  }

  /**
   * This method return the data of the model
   * @param ManageProductcategoryRequest $request
   *
   * @return mixed
   */
  public function __invoke(ManageEquipmentRequest $request)
  {
    $query = $this->repository->getForDataTable();
    $prefixes = prefixesArray(['equipment'], auth()->user()->ins);
    
    return Datatables::of($query)
      ->escapeColumns(['id'])
      ->addIndexColumn()
      ->editColumn('tid', function ($equipment) use($prefixes) {
        return gen4tid("{$prefixes[0]}-", $equipment->tid);
      })
      ->filterColumn('tid', function($query, $tid) use ($prefixes){
        $arr = explode('-',$tid);
        if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
            $query->where('tid', floatval($arr[1]));
        }
        elseif (floatval($tid)) {
            $query->where('tid', floatval($tid));
        }
    })
      ->addColumn('customer', function ($equipment) {
        $customer = $equipment->customer;
        $branch = $equipment->branch;
        if ($customer && $branch)
          return "{$customer->company} - {$branch->name}";
      })
      ->editColumn('capacity', function ($equipment) {
        return $equipment->capacity;
      })
      ->addColumn('service_rate', function ($equipment) {
        return numberFormat($equipment->service_rate);
      })
      ->addColumn('actions', function ($equipment) {
        return $equipment->action_buttons;
      })
      ->make(true);
  }
}
