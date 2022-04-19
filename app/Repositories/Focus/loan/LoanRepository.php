<?php

namespace App\Repositories\Focus\loan;

use DB;
use App\Exceptions\GeneralException;
use App\Models\loan\Loan;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
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
     * For updating the respective Model in storage
     *
     * @param Customer $customer
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Customer $customer, array $input)
    {

        throw new GeneralException(trans('exceptions.backend.customers.update_error'));
    }

  
    /**
     * For deleting the respective model from storage
     *
     * @param Customer $customer
     * @return bool
     * @throws GeneralException
     */
    public function delete(Customer $customer)
    {
        if ($customer->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.customers.delete_error'));
    }

    /**
     * Accounting
     */
    public function post_transaction($result)
    {
        // credit liability (loan)
        $tr_category = Transactioncategory::where('code', 'loan')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $result->lender_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $result['amount'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $result['date'],
            'user_id' => $result['user_id'],
            'ins' => $result['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $result['id'],
            'user_type' => 'user',
            'is_primary' => 1,
            'note' => $result->note,
        ];
        Transaction::create($cr_data);

        // debit accounts income (bank)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $result->bank_id,
            'debit' => $result->amount,
        ]);
        Transaction::create($dr_data);
        
        // update account ledgers debit and credit totals
        aggregate_account_transactions();
    }
}
