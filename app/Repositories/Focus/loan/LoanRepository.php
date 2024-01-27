<?php

namespace App\Repositories\Focus\loan;

use DB;
use App\Exceptions\GeneralException;
use App\Models\loan\Loan;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class LoanRepository.
 */
class LoanRepository extends BaseRepository
{
    use Accounting;

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
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'fee', 'month_installment'])) 
                $input[$key] = numberClean($val);
        }

        $result = Loan::create($input);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Loan $loan, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'approval_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'fee', 'month_installment'])) 
                $input[$key] = numberClean($val);
        }

        $result = $loan->update($input);
        if (!$loan->employee) throw ValidationException::withMessages(['server error! something went wrong']);

        if ($loan->approval_status == 'approved') {
            $loan->amount += $loan->fee; 
            /** accounting */
            $loan->transactions()->delete();
            $this->post_loan_issuance($loan);
        }

        if ($result) {
            DB::commit();
            return $result;
        }        
    }

    /**
     *  Remove resource from storage
     */
    public function delete($loan)
    {
        DB::beginTransaction();
        $loan->transactions()->delete();
        aggregate_account_transactions();
        if ($loan->delete()) {
            DB::commit();
            return true;
        };
    }
}