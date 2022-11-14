<?php

namespace App\Repositories\Focus\tax_report;

use App\Exceptions\GeneralException;
use App\Models\items\TaxReportItem;
use App\Models\tax_report\TaxReport;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

class TaxReportRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TaxReport::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
            
        return $q->get();
    }

    public function getForSalesDataTable()
    {
        $q = TaxReportItem::query()->whereHas('invoice')->orWhereHas('credit_note');
            
        return $q->with(['invoice', 'credit_note'])->get();
    }

    public function getForPurchasesDataTable()
    {
        $q = TaxReportItem::query()->whereHas('purchase')->orWhereHas('debit_note');
            
        return $q->with(['purchase', 'debit_note'])->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return TaxReport $tax_report
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data_keys = [
            'sale_subtotal', 'sale_tax', 'sale_total', 'purchase_subtotal', 
            'purchase_tax', 'purchase_total',
        ];
        foreach ($input as $key => $val) {
            if (in_array($key, $data_keys)) $input[$key] = numberClean($val);
        }

        // report data
        $report_data = Arr::only($input, ['title', 'sale_tax_rate', 'purchase_tax_rate', ...$data_keys]);
        $report_data = array_replace($report_data, [
            'tid' => TaxReport::max('tid') + 1,
            'date' => date('Y-m-d'),
        ]);
        $result = TaxReport::create($report_data);

        // sale data items
        $sale_data_items = Arr::only($input, ['sale_id', 'sale_type', 'sale_is_filed']);
        $sale_data_items = modify_array($sale_data_items);
        $sale_data_items = array_map(fn($v) => [
            'tax_report_id' => $result->id,
            'invoice_id' => $v['sale_type'] == 'invoice'? $v['sale_id'] : null,
            'credit_note_id' => $v['sale_type'] == 'credit_note'? $v['sale_id'] : null,
            'is_filed' => $v['sale_is_filed'],
        ], $sale_data_items);
        TaxReportItem::insert($sale_data_items);

        // purchase data items
        $purchase_data_items = Arr::only($input, ['purchase_id', 'purchase_type', 'purchase_is_filed']);
        $purchase_data_items = modify_array($purchase_data_items);
        $purchase_data_items = array_map(fn($v) => [
            'tax_report_id' => $result->id,
            'purchase_id' => $v['purchase_type'] == 'purchase'? $v['purchase_id'] : null,
            'debit_note_id' => $v['purchase_type'] == 'debit_note'? $v['purchase_id'] : null,
            'is_filed' => $v['purchase_is_filed'],
        ], $purchase_data_items);
        TaxReportItem::insert($purchase_data_items);
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TaxReport $tax_report
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(TaxReport $tax_report, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TaxReport $tax_report
     * @throws GeneralException
     * @return bool
     */
    public function delete(TaxReport $tax_report)
    {
        if ($tax_report->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
