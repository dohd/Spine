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
use App\Models\projectequipment\Projectequipment;

/**
 * Class BranchTableController.
 */
class EquipmentsTableController extends Controller
{
  /**
   * variable to store the repository object
   * @var ProductcategoryRepository
   */
  protected $equipment;

  /**
   * contructor to initialize repository object
   * @param ProductcategoryRepository $productcategory ;
   */
  public function __construct(EquipmentRepository $equipment)
  {

    $this->equipment = $equipment;
  }

  /**
   * This method return the data of the model
   * @param ManageProductcategoryRequest $request
   *
   * @return mixed
   */
  public function __invoke(ManageEquipmentRequest $request)
  {
    $core = $this->equipment->getForDataTable();
  
    if (request('rel_type') == 1) {
      return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('customer', function ($equipment) {
          return $equipment->customer->company;
        })
        ->addColumn('region', function ($equipment) {
          return $equipment->region->name;
        })
        ->addColumn('branch', function ($equipment) {
          return $equipment->branch->name;
        })

        ->addColumn('section', function ($equipment) {
          return $equipment->project_section->name;
        })
        ->addColumn('category', function ($equipment) {
          return $equipment->category->name;
        })
        ->addColumn('unit_type', function ($equipment) {
          $unit_type = "InDoor";
          if ($equipment->unit_type == 2)  $unit_type = "OutDoor";
          if ($equipment->unit_type == 3) $unit_type = "StandAlone";

          return $unit_type;
        })
        ->addColumn('status', function ($equipment) {
          $equipments = Projectequipment::where('equipment_id', $equipment->id)->where('schedule_id', request('rel_id'))->first();
          if ($equipments) return '<span class="badge" style="background-color:#12C538">Loaded</span>';

          return '<span class="badge" style="background-color:#f48fb1">Not Loaded</span>';
        })
        ->addColumn('relationship', function ($equipment) {
          if ($equipment->rel_id > 0) return $equipment->indoor->unique_id;
        })
        ->addColumn('last_maint_date', function ($equipment) {
          return dateFormat($equipment->last_maint_date);
        })
        ->addColumn('name', function ($equipment) {
          return '<a class="font-weight-bold" href="' . route('biller.products.index') . '?rel_type=' . $equipment->id . '&rel_id=' . $equipment->id . '">' . $equipment->name . '</a>';
        })
        ->addColumn('mass_delete', function ($equipment) {
          return  '<input type="checkbox" class="row-select" value="' . $equipment->id . '">';
        })
        ->addColumn('created_at', function ($equipment) {
          return dateFormat($equipment->created_at);
        })
        ->make(true);
    } 
    return Datatables::of($core)
      ->escapeColumns(['id'])
      ->addIndexColumn()
      ->addColumn('customer', function ($equipment) {
        if ($equipment->customer && $equipment->branch)
        return $equipment->customer->company . ' ' . $equipment->branch->name;
      })
      ->addColumn('unit_type', function ($equipment) {
        $unit_type = "InDoor";
        if ($equipment->unit_type == 2) $unit_type = "OutDoor";
        if ($equipment->unit_type == 3) $unit_type = "StandAlone";

        return $unit_type;
      })
      ->addColumn('relationship', function ($equipment) {
        if ($equipment->rel_id > 0) 
          return $equipment->indoor->unique_id;
      })
      ->addColumn('last_maint_date', function ($equipment) {
        return dateFormat($equipment->last_maint_date);
      })
      ->addColumn('next_maintenance_date', function ($equipment) {
        return dateFormat($equipment->next_maintenance_date);
      })
      ->addColumn('name', function ($equipment) {
        return '<a class="font-weight-bold" href="' . route('biller.products.index') . '?rel_type=' . $equipment->id . '&rel_id=' . $equipment->id . '">' . $equipment->name . '</a>';
      })
      ->addColumn('created_at', function ($equipment) {
        return dateFormat($equipment->created_at);
      })
      ->addColumn('actions', function ($equipment) {
        return $equipment->action_buttons;
      })
      ->make(true);
  }
}