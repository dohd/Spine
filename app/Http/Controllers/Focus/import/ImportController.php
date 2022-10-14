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

namespace App\Http\Controllers\Focus\import;

use App\Http\Requests\Focus\report\ManageReports;
use App\Imports\AccountsImport;
use App\Imports\CustomersImport;
use App\Imports\ProductsImport;
use App\Imports\TransactionsImport;
use App\Imports\EquipmentsImport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Imports\ClientPricelistImport;
use App\Models\equipmentcategory\EquipmentCategory;
use DB;
use Error;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * ImportController
 */
class ImportController extends Controller
{
    private $upload_temp;

    public function __construct()
    {
        $this->upload_temp = Storage::disk('public');
    }

    /**
     * index page for import
     */
    public function index(ManageReports $request, $type)
    {
        $titles = [
            'customers' => trans('import.import_customers'),
            'products' => trans('import.import_products'),
            'accounts' => trans('import.import_accounts'),
            'transactions' => trans('import.import_transactions'),
            'equipments' => 'Import Equipments',
            'client_pricelist' => 'Import Client Pricelist',
        ];
        $data = ['title' => $titles[$type]] + compact('type');
            
        return new ViewResponse('focus.import.index', compact('data'));
    }

    /**
     * Download sample template
     */
    public function sample_template($file_name)
    {
        $file_path = public_path() . '/storage/app/public/sample/' . $file_name . '.csv';
        $file_exists = file_exists($file_path);
        if (!$file_exists) throw ValidationException::withMessages(['Template file does not exist!']);
        
        // generate equipment_categories csv file
        if ($file_name == 'equipment_categories') {
            $categories = EquipmentCategory::all();
            $fw = fopen($file_path, 'w');
            fputcsv($fw, ['equipment_category_id', 'name']);
            foreach ($categories as $row) {
                fputcsv($fw, [$row->id, $row->name]);
            }
            fclose($fw);
        }

        return response(file_get_contents($file_path), 200, ['Content-Type' => 'text/csv']);
    }    

    /**
     * store template data in storage 
     */
    public function store(Request $request, $type)
    {
        $request->validate(['import_file' => 'required|max:' . config('master.file_size')]);

        $data = $request->except(['_token']) + compact('type');

        $extension = File::extension($request->import_file->getClientOriginalName());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) 
            throw ValidationException::withMessages([trans('import.import_invalid_file')]);

        $file = $request->file('import_file');
        $filename = date('Ymd_his') . rand(9999, 99999) . $file->getClientOriginalName();
        $path = 'temp' . DIRECTORY_SEPARATOR;
        $is_success = $this->upload_temp->put($path . $filename, file_get_contents($file->getRealPath()));

        return new ViewResponse('focus.import.import_progress', compact('filename', 'is_success', 'data'));
    }    

    /**
     * Process imported template
     */
    public function process_template(ManageReports $request)
    {   
        $data = $request->except('_token');
        $data['ins'] = auth()->user()->ins;

        $filename = $request->name;
        $path = 'temp' . DIRECTORY_SEPARATOR;
        $file_exists = Storage::disk('public')->exists($path . $filename);
        if (!$file_exists) throw new Error('File does not exist');

        $models = [
            'customer' => new CustomersImport($data),
            'products' => new ProductsImport($data),
            'accounts' => new AccountsImport($data),
            'transactions' => new TransactionsImport($data),
            'equipments' => new EquipmentsImport($data),
            'client_pricelist' => new ClientPricelistImport($data),
        ];

        $storage_path = Storage::disk('public')->path($path . $filename);
        $model = $models[$data['type']];

        try {
            DB::beginTransaction();
            Excel::import($model, $storage_path);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('public')->delete($path . $filename);
            printlog($e->getMessage());
            throw new Error(trans('import.import_process_failed'));
        }

        $row_count = $model->getRowCount();
        if (!$row_count) throw new Error(trans('import.import_process_failed'));
        Storage::disk('public')->delete($path . $filename);
        
        return response()->json([
            'status' => 'Success', 
            'message' => trans('import.import_process_success') . ' ' . $row_count . ' rows imported successfully'
        ]);
    }
}
