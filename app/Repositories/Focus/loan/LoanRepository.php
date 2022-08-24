<?php

namespace App\Repositories\Focus\loan;

use DB;
use App\Exceptions\GeneralException;
use App\Models\items\PaidloanItem;
use App\Models\loan\Loan;
use App\Models\loan\Paidloan;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;
/**
 * Class CustomerRepository.
 */
class LoanRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Loan::class;

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
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $input = array_replace($input, [
            'date' => date_for_database($input['date']),
            'amount' => numberClean($input['amount']),
            'amount_pm' => numberClean($input['amount_pm']),
        ]);
        $result = Loan::create($input);

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.customers.create_error'));
    }

    /**
     *  Remove resource from storage
     */
    public function delete($loan)
    {
        DB::beginTransaction();

        $loan->transactions()->delete();
        $result = $loan->delete();

        DB::commit();
        if ($result) return true;
    }

    /**
     * approve loan
     */
    public function approve_loan($loan)
    {
        DB::beginTransaction();

        $loan->update(['is_approved' => 1]);

        /** accounts  */
        // credit loan account (liability)
        $tr_category = Transactioncategory::where('code', 'loan')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $loan->lender_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $loan['amount'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $loan['date'],
            'user_id' => $loan['user_id'],
            'ins' => $loan['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $loan['id'],
            'user_type' => 'employee',
            'is_primary' => 1,
            'note' => $loan->note,
        ];
        Transaction::create($cr_data);

        // debit bank
        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $loan->bank_id,
            'debit' => $loan->amount,
        ]);
        Transaction::create($dr_data);
        
        // update account ledgers debit and credit totals
        aggregate_account_transactions();

        DB::commit();
        return true;
    }

    /**
     * Pay Loan
     */
    public function store_loans(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $data['date'] = date_for_database($data['date']);
        $data['amount'] = numberClean($data['amount']);
        $result = Paidloan::create($data);
        $result['note'] = $result['payment_mode'] . ' - ' . $result['ref'];

        $data_items = $input['data_items'];
        foreach ($data_items as $k => $item) {
            $item['paid_loan_id'] = $result->id;
            foreach ($item as $key => $val) {
                if (in_array($key, ['paid', 'interest', 'penalty'], 1))
                    $item[$key] = numberClean($val);
            }
            $data_items[$k] = $item;
        }
        PaidloanItem::insert($data_items);

        // update paid amount in loans
        $loan_ids = $result->items()->pluck('loan_id')->toArray();
        $paid_loans = PaidloanItem::whereIn('loan_id', $loan_ids)
            ->select(DB::raw('loan_id as id, SUM(paid) as amountpaid'))
            ->groupBy('loan_id')
            ->get()->toArray();
        Batch::update(new Loan, $paid_loans, 'id');
        
        // update payment status in loans
        foreach ($result->items as $item) {
            $loan = $item->loan;
            if ($loan->amountpaid == 0) $loan->update(['status' => 'pending']);
            elseif (round($loan->amount) > round($loan->amountpaid)) $loan->update(['status' => 'partial']);
            else $loan->update(['status' => 'paid']);
        }

        /** accounts */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return true;
    }

    public function post_transaction($result)
    {
        // credit bank
        $tr_category = Transactioncategory::where('code', 'loan')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $result['bank_id'],
            'trans_category_id' => $tr_category->id,
            'credit' => $result['amount'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $result['date'],
            'user_id' => $result['user_id'],
            'ins' => $result['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $result['id'],
            'user_type' => 'employee',
            'is_primary' => 1,
            'note' => $result['note'],
        ];
        Transaction::create($cr_data);

        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit account expense (lender)
        $principle = $result->items->sum('paid');
        $dr_data_1 = array_replace($cr_data, [
            'account_id' =>  $result['lender_id'],
            'debit' => $principle,
        ]);
        Transaction::create($dr_data_1);

        // debit account income (loan interest)
        $interest = $result->items->sum('interest');
        if ($interest && $result->interest_id) {
            $dr_data_2 = array_replace($cr_data, [
                'account_id' =>  $result->interest_id,
                'debit' => $result['amount'],
            ]);
            Transaction::create($dr_data_2);
        } 

        // debit account income (loan penalty)
        $penalty = $result->items->sum('penalty');
        if ($penalty && $result->penalty_id) {
            $dr_data_3 = array_replace($cr_data, [
                'account_id' =>  $result->penalty_id,
                'debit' => $result['amount'],
            ]);
            Transaction::create($dr_data_3);
        } 

        // update account ledgers debit and credit totals
        aggregate_account_transactions();    
    }
}