<?php

namespace App\Http\Controllers\Focus\import;

use App\Http\Requests\Focus\report\ManageReports;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * ImportController
 */
class ImportController extends Controller
{
    // temp upload
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
        $labels = [
            'customer' => trans('import.import_customers'),
            'supplier' => 'Import Suppliers',
            'products' => trans('import.import_products'),
            'accounts' => trans('import.import_accounts'),
            'equipments' => 'Import Equipments',
            'client_pricelist' => 'Import Client Pricelist',
            'supplier_pricelist' => 'Import Supplier Pricelist',
        ];
        $data = ['title' => $labels[$type], 'type' => $type];

        if ($type == 'products') {
            $data['warehouses'] = \App\Models\warehouse\Warehouse::get(['id', 'title']);
            $data['product_categories'] = \App\Models\productcategory\Productcategory::get(['id', 'title']);
        }
        
        return new ViewResponse('focus.import.index', compact('data'));
    }

    /**
     * Process and Store imported data
     */
    public function store(Request $request, $type)
    {
        $request->validate(['import_file' => 'required|max:' . config('master.file_size')]);

        $data = $request->except(['_token']) + compact('type');

        $extension = File::extension($request->import_file->getClientOriginalName());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) 
            throw ValidationException::withMessages(['File extension unsupported!']);

        $file = $request->file('import_file');
        $file_name = $file->getClientOriginalName();
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬]/', $file_name))
            throw ValidationException::withMessages(['Remove special characters from file name!']);

        $file_name = preg_replace('/\s+/', '', $file_name);
        $filename = date('Ymd_his') . rand(9999, 99999) . $file_name;

        // temp storage
        $path = 'temp' . DIRECTORY_SEPARATOR;
        $is_success = $this->upload_temp->put($path . $filename, file_get_contents($file->getRealPath()));
        if (!$is_success) throw ValidationException::withMessages(['Something went wrong try again later!']);

        // process file
        $data['ins'] = auth()->user()->ins;
        $models = [
            'customer' => new \App\Imports\CustomersImport($data),
            'supplier' => new \App\Imports\SuppliersImport($data),
            'products' => new \App\Imports\ProductsImport($data),
            'accounts' => new \App\Imports\AccountsImport($data),
            'equipments' => new \App\Imports\EquipmentsImport($data),
            'client_pricelist' => new \App\Imports\ClientPricelistImport($data),
            'supplier_pricelist' => new \App\Imports\SupplierPricelistImport($data),
        ];

        $file_path = $path . $filename;
        $model = $models[$data['type']];

        DB::beginTransaction();
        
        try {
            // set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            Excel::import($model, Storage::disk('public')->path($file_path));

            $row_count = $model->getRowCount();
            if (!$row_count) trigger_error('{$row_count} rows imported');
            Storage::disk('public')->delete($file_path);

            DB::commit();
            return redirect()->back()->with('flash_success', trans('import.import_process_success') . " {$row_count} rows imported successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('public')->delete($file_path);
            return redirect()->back()->with('flash_error', trans('import.import_process_failed'));
        }
    }    
}
