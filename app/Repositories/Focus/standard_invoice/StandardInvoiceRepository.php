<?php

namespace App\Repositories\Focus\standard_invoice;

use DB;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class InvoiceRepository.
 */
class StandardInvoiceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Invoice::class;

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

    /**
     * Convert Invoice totals to KES
     */
    public function convert_totals_to_kes($result)
    {
        $quote_ids = [];
        $inv_has_tax = $result['tax_id'] > 0;
        foreach ($result->products as $key => $inv_product) {
            $quote = $inv_product->quote;
            if ($quote) {
                if (in_array($quote->id, $quote_ids)) continue;
                $quote_ids[] = $quote->id;
                $currency = $inv_product->quote->currency;
                if ($currency && $currency->rate > 1) {
                    $subtotal = $quote->verified_products()->sum(DB::raw('product_subtotal * product_qty')) * $currency->rate;
                    $total = $quote->verified_products()->sum(DB::raw('product_price * product_qty')) * $currency->rate;
                    if ($key == 0) {
                        foreach (['total', 'tax', 'subtotal'] as $value) {
                            $result[$value] = 0;
                        }
                    }
                    $result['total'] += $total;
                    $result['subtotal'] += $subtotal;
                    if ($inv_has_tax) $result['tax'] += $total - $subtotal;
                }
            }
        }
        return $result;
    }


    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $data['is_standard'] = 1;
        $duedate = $data['invoicedate'] . ' + ' . $data['validity'] . ' days';
        $data['invoiceduedate'] = date_for_database($duedate);
        foreach ($data as $key => $val) {
            if ($key == 'invoicedate') $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'taxable', 'tax'])) 
                $data[$key] = numberClean($val);
        }

        $result = Invoice::create($data);
        
        $data_items = modify_array($input['data_items']);
        $data_items = array_filter($data_items, fn($v) => $v['product_qty']);
        if (!$data_items) throw ValidationException::withMessages(['Cannot Invoice without product line items!']);

        foreach ($data_items as $k => $item) {
            foreach ($item as $j => $value) {
                if (in_array($j, ['product_price', 'product_tax', 'product_amount'])) 
                    $item[$j] = floatval(str_replace(',', '', $value));
            }
            $data_items[$k] = array_replace($item, ['invoice_id' => $result->id,]);
        }
        InvoiceItem::insert($data_items);

        // convert invoice totals to KES
        // $result = $this->convert_totals_to_kes($result);
        
        /** accounting */
        $this->post_transaction($result);

        if ($result) {
            DB::commit();
            return $result;
        }

        DB::rollBack();
        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Invoice $invoice
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Invoice $invoice, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.charges.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Invoice $invoice
     * @throws GeneralException
     * @return bool
     */
    public function delete(Invoice $invoice)
    {
        dd($invoice);     

        throw new GeneralException(trans('exceptions.backend.charges.delete_error'));
    }

    public function post_transaction($result)
    {
        // debit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'inv')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $result->total,
            'tr_date' => $result->invoicedate,
            'due_date' => $result->invoiceduedate,
            'user_id' => $result->user_id,
            'note' => $result->notes,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ]; 
        Transaction::create($dr_data);

        unset($dr_data['debit'], $dr_data['is_primary']);

        // credit Revenue Account (Income)
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $result->account_id,
            'credit' => $result->subtotal,
        ]); 
        Transaction::create($inc_cr_data);

        // credit tax (VAT)
        if ($result->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $tax_cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $result->tax,
            ]); 
            Transaction::create($tax_cr_data);
        }

        // debit COG
        $cog_account = Account::where('system', 'cog')->first(['id']);
        $cog_dr_data = array_replace($dr_data, [
            'account_id' => $cog_account->id,
            'debit' => $result->subtotal,
        ]); 
        Transaction::create($cog_dr_data);
        
        // credit Inventory
        $stock_account = Account::where('system', 'stock')->first(['id']);
        $stock_cr_data = array_replace($dr_data, [
            'account_id' => $stock_account->id,
            'credit' => $result->subtotal,
        ]);
        Transaction::create($stock_cr_data);

        aggregate_account_transactions();        
    }
}
