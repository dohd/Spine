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

    return Datatables::of($core)
      ->escapeColumns(['id'])
      ->addIndexColumn()
      ->addColumn('tid', function ($equipment) {
        return gen4tid('E-', $equipment->tid);
      })
      ->addColumn('branch', function ($equipment) {
        if ($equipment->branch)
        return $equipment->branch->name;
      })
      ->addColumn('capacity', function ($equipment) {
        return numberFormat($equipment->capacity);
      })
      ->addColumn('actions', function ($equipment) {
        return $equipment->action_buttons;
      })
      ->make(true);
  }
}
