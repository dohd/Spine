<?php

namespace App\Repositories\Focus\openingbalance;

use DB;
use Carbon\Carbon;
use App\Models\openingbalance\Openingbalance;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Models\items\PurchaseItem;
use App\Models\transactioncategory\Transactioncategory;


/**
 * Class BankRepository.
 */
class OpeningbalanceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Openingbalance::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->where('transaction_type','transfers')
        ->get();
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
        //credit entry
       $credit_trans_category_id = Transactioncategory::where('code', 'bc_transactions')->first();
       $credit_trans_category_id=$credit_trans_category_id->id;

        $input['credit']['tid'] = $input['credit']['tid'];
        $input['credit']['account_id'] = $input['credit']['account_id'];
        $input['credit']['method'] = $input['credit']['method'];
        $input['credit']['refer_no'] = $input['credit']['refer_no'];
        $input['credit']['note'] = strip_tags(@$input['credit']['note']);
        $input['credit']['trans_category_id'] = $credit_trans_category_id;
        $input['credit']['transaction_type'] ='transfers';
       

        DB::beginTransaction();
        $input['credit'] = array_map( 'strip_tags', $input['credit']);
        $result = Banktransfer::create($input['credit']);


        if ($result) {

         //begin debit entry for bank charges
       $debit_trans_category_id = Transactioncategory::where('code', 'bc_transactions')->first();
       $debit_trans_category_id=$debit_trans_category_id->id;
        //$invoice_d = Purchase::where('id',$input['debit']['id'])->first();
        $input['debit']['tid'] = $input['debit']['tid'];
        $input['debit']['bill_id'] = $result->id;
        $input['debit']['trans_category_id'] = $debit_trans_category_id;
        $input['debit']['method'] = $input['method']['id'];
        $input['debit']['refer_no'] = $input['debit']['refer_no'];
        $input['debit']['note'] = strip_tags(@$input['debit']['note']);
        $input['debit']['second_trans'] = 1;
        $input['debit']['transaction_type'] ='transfers';
        $input['debit'] = array_map( 'strip_tags', $input['debit']);
         Banktransfer::create($input['debit']);




            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.charges.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Charge $charge, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($charge->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.charges.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bank $bank
     * @throws GeneralException
     * @return bool
     */
    public function delete(Charge $charge)
    {
        if ($bank->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.charges.delete_error'));
    }
}
