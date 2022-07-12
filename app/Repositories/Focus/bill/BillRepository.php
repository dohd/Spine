<?php

namespace App\Repositories\Focus\bill;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use App\Models\bill\Bill;
use App\Models\bill\Paidbill;
use App\Models\items\PaidbillItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Support\Facades\DB;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class PurchaseorderRepository.
 */
class BillRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Bill::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = Bill::query();

        return $q->get();
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

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) 
                $bill[$key] = date_for_database($val);
            if (in_array($key, ['amount_ttl', 'deposit_ttl', 'deposit'], 1)) 
                $bill[$key] = numberClean($val);
        }
        $result = Paidbill::create($bill);
        $result->note = $result->doc_ref_type . ' - '. $result->doc_ref . ' ' . $result->note;

        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item['paidbill_id'] = $result->id;
            $item['paid'] = numberClean($item['paid']);
            $bill_items[$k] = $item;
        }
        PaidbillItem::insert($bill_items);

        // update paid amount in bills
        $bill_ids = $result->items()->pluck('bill_id')->toArray();
        $paid_bills = PaidbillItem::whereIn('bill_id', $bill_ids)
            ->select(DB::raw('bill_id as id, SUM(paid) as amountpaid'))
            ->groupBy('bill_id')
            ->get()->toArray();
        Batch::update(new Bill, $paid_bills, 'id');
        
        // update payment status in bills
        foreach ($result->items as $item) {            
            $bill = $item->bill;
            if ($bill->grandttl == $bill->amountpaid) $bill->update(['status' => 'paid']);  
            elseif ($bill->amountpaid == 0) $bill->update(['status' => 'pending']);
            elseif ($bill->grandttl > $bill->amountpaid) $bill->update(['status' => 'partial']);
        }

        /** accounts */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    // create bill transaction 
    public function post_transaction($bill)
    {
        // credit Bank (Income)
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $bill['account_id'],
            'trans_category_id' => $tr_category->id,
            'credit' => $bill['deposit_ttl'],
            'tr_date' => $bill['date'],
            'due_date' => $bill['due_date'],
            'user_id' => $bill['user_id'],
            'ins' => $bill['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill['id'],
            'user_type' => 'supplier',
            'is_primary' => 1,
            'note' => $bill['note'],
        ];
        Transaction::create($cr_data);

        // debit Accounts Payable (Creditor)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'payable')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $bill['deposit_ttl'],
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();    
    }

    /**
     * Create KRA Bill
     */
    public function create_kra(array $input)
    {
        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }
}
