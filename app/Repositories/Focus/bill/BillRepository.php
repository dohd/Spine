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
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) {
                $bill[$key] = date_for_database($val);
            }
            if (in_array($key, ['amount_ttl', 'deposit_ttl'], 1)) {
                $bill[$key] = numberClean($val);
            }
        }
        $result = Paidbill::create($bill);

        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item = $item + ['paidbill_id' => $result->id];
            $item['paid'] = numberClean($item['paid']);
            $bill_items[$k] = $item;
        }
        PaidbillItem::insert($bill_items);

        // update payment status in bills
        foreach ($result->items as $item) {
            if ($item->paid) {
                $payable = $item->bill->grandttl;
                if ($item->paid && $item->paid < $payable) $item->bill->update(['status' => 'Partial']);
                if ($item->paid == $payable) $item->bill->update(['status' => 'Paid']);    
            }
        }

        // update paid amount in bills
        $bill_ids = $result->items()->pluck('bill_id')->toArray();
        $paid_bills = PaidbillItem::whereIn('bill_id', $bill_ids)
            ->select(DB::raw('bill_id as id, SUM(paid) as amountpaid'))
            ->groupBy('bill_id')
            ->get()->toArray();
        Batch::update(new Bill, $paid_bills, 'id');

        /** accounting */
        // credit
        $tr_category = Transactioncategory::where('code', 'PMT')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $bill['account_id'],
            'trans_category_id' => $tr_category->id,
            'credit' => $bill['deposit_ttl'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $bill['due_date'],
            'user_id' => $bill['user_id'],
            'ins' => $bill['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $result['id'],
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($cr_data);

        // debit
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'payable')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $bill['deposit_ttl'],
        ]);
        Transaction::create($dr_data);

        // update account ledgers debit and credit totals
        $tr_totals = Transaction::where('tr_ref', $bill->id)
            ->select(DB::raw('SELECT account_id as id, SUM(credit) as credit_ttl, SUM(debit) as debit_ttl'))
            ->groupBy('account_id')
            ->get()->toArray();
        Batch::update(new Account, $tr_totals, 'id');

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bill $bill
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Purchase $purchase, array $input)
    {
        // 
        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bill $bill
     * @throws GeneralException
     * @return bool
     */
    public function delete($bill)
    {
        // 
    }
}
