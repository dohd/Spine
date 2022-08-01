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
use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\productcategory\Productcategory;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\warehouse\Warehouse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
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
    public function index(ManageReports $request)
    {
        $prop = (object) array(
            'title' => '', 
            'template' => $request->type
        );
        $data = collect();
        switch ($request->type) {
            case 'customers':
                $prop->title = trans('import.import_customers');
                break;
            case 'products':
                $prop->title = trans('import.import_products');
                $data->put('product_category', Productcategory::all());
                $data->pu('warehouses', Warehouse::all());
                break;
            case 'accounts':
                $prop->title = trans('import.import_accounts');
                break;
            case 'transactions':
                $prop->title = trans('import.import_transactions');
                $data->put('accounts', Account::all());
                $data->put('transaction_categories', Transactioncategory::all());
                break;
            case 'equipments':
                $prop->title = 'Import Equipments';
                $data->put('customers', Customer::all());
                break;
        }

        return new ViewResponse('focus.import.index', compact('prop', 'data'));
    }

    /**
     * Download sample template
     */
    public function sample_template($file_name)
    {
        $file = Storage::disk('public')->get('sample/' . $file_name . '.csv');

        return response($file, 200, ['Content-Type' => 'text/csv']);
    }    

    /**
     * store template data in storage 
     */
    public function store(Request $request)
    {
        $request->validate(['import_file' => 'required|max:' . config('master.file_size')]);

        $extension = File::extension($request->import_file->getClientOriginalName());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) 
            throw ValidationException::withMessages([trans('import.import_invalid_file')]);
            
        $data = collect();
        $template = $request->type;
        switch ($template) {
            case 'customer':
                $data->put('customer_password_type', $request->customer_password_type);
                session(['customer_password' => $request->customer_password]);
                break;
            case 'products':
                $data->put('productcategory', $request->productcategory);
                $data->put('warehouse', $request->warehouse);
                break;
            case 'transactions':
                $data->put('accounts', $request->account);
                $data->put('transaction_categories', $request->trans_category);
                break;
            case 'transactions':
                $data->put('accounts', $request->account);
                $data->put('transaction_categories', $request->trans_category);
                break;
            case 'equipments':
                $data->put('customer', $request->customer);
                break;
        }

        $file = $request->file('import_file');
        $filename = date('Ymd_his') . rand(9999, 99999) . $file->getClientOriginalName();
        $path = 'temp' . DIRECTORY_SEPARATOR;
        $success_upload = $this->upload_temp->put($path . $filename, file_get_contents($file->getRealPath()));

        return new ViewResponse('focus.import.import_progress', compact('data', 'filename', 'success_upload', 'template'));
    }    

    /**
     * Process imported template
     */
    public function process_template(ManageReports $request)
    {   
        try {
            DB::beginTransaction();

            $filename = $request->name;
            $path = 'temp' . DIRECTORY_SEPARATOR;
            $file_exists = Storage::disk('public')->exists($path . $filename);
            if (!$file_exists) throw ValidationException::withMessages(['file does not exist!']);
            
            $import_model = null;
            $data = collect();
            switch ($request->type) {
                case 'customer':
                    if ($request->customer_password_type == 1) 
                        $data->put('password', $request->session()->pull('customer_password', null));
                    $import_model = new CustomersImport((array) $data);
                    break;
                case 'products':
                    $data = $data->merge(['category' => $request->productcategory, 'warehouse' => $request->warehouse]);
                    $import_model = new ProductsImport($data);
                    break;
                case 'accounts':
                    $import_model = new AccountsImport();
                    break;
                case 'transactions':
                    $data = $data->merge(['account' => $request->accounts, 'trans_category' => $request->transaction_categories]);
                    $import_model = new TransactionsImport((array) $data);
                    break;
                case 'equipments':
                    $data->put('customer', $request->customer);
                    $import_model = new EquipmentsImport($data);
                    break;
            }

            $storage_path = Storage::disk('public')->path($path . $filename);
            Excel::import($import_model, $storage_path);
            $rowCount = $import_model->getRowCount();
            if (!$rowCount) throw new Error(trans('import.import_process_failed'));

            DB::commit();
            Storage::disk('public')->delete($path . $filename);
            $msg = ' ' . $rowCount . ' rows imported successfully';
            return response()->json(['status' => 'Success', 'message' => trans('import.import_process_success') . $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('public')->delete($path . $filename);
            return response()->json(['status' => 'Error', 'message' => trans('import.import_process_failed')]);
        }
    }
}
