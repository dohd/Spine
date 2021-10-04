<?php

namespace App\Repositories\Focus\withholding;

use DB;
use Carbon\Carbon;
use App\Models\withholding\Withholding;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Models\items\PurchaseItem;
use App\Models\transactioncategory\Transactioncategory;


/**
 * Class BankRepository.
 */
class WithholdingRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Withholding::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->where('tax_type','withholding_tax')
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
       $account_receivable = Withholding::find($input['credit']['account_id']);
       //$credit_trans_category_id=$credit_trans_category_id->id;

        $input['credit']['tid'] = $input['credit']['tid'];
        $input['credit']['account_id'] =  $account_receivable->account_id;
        $input['credit']['note'] = strip_tags(@$input['credit']['note']);
        $input['credit']['trans_category_id'] =$account_receivable->trans_category_id;
        $input['credit']['transaction_type'] =$account_receivable->transaction_type;
        $input['credit']['payer_id'] =$account_receivable->payer_id;
        $input['credit']['branch_id'] =$account_receivable->branch_id;
        $input['credit']['project_id'] =$account_receivable->project_id;
        $input['credit']['invoice_id'] =$account_receivable->invoice_id;
        $input['credit']['bill_id'] =$input['credit']['account_id'];

        
       

        DB::beginTransaction();
        $input['credit'] = array_map( 'strip_tags', $input['credit']);
        $result = Withholding::create($input['credit']);


        if ($result) {

         //begin debit entry for bank charges
       $debit_trans_category_id = Transactioncategory::where('code', 'p_taxes')->first();
       $debit_trans_category_id=$debit_trans_category_id->id;


        $input['debit']['tid'] = $input['debit']['tid'];
        $input['debit']['bill_id'] = $result->id;
        $input['debit']['trans_category_id'] = $debit_trans_category_id;
        $input['debit']['refer_no'] = $input['debit']['refer_no'];
        $input['debit']['note'] = strip_tags(@$input['debit']['note']);
        $input['debit']['tax_type'] ='withholding_tax';
        $input['debit']['payer_id'] = $account_receivable->payer_id;
        $input['debit']['second_trans'] = 1;
        $input['debit']['transaction_type'] =$input['debit']['transaction_type'];
        $input['debit'] = array_map( 'strip_tags', $input['debit']);
        $input['debit']['invoice_id'] =$account_receivable->invoice_id;
         Withholding::create($input['debit']);




            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.withholdings.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Withholding $charge, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($charge->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.withholdings.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bank $withholding
     * @throws GeneralException
     * @return bool
     */
    public function delete(Withholding $withholding)
    {
        if ($withholding->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.withholdings.delete_error'));
    }
}
