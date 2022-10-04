<?php

namespace App\Repositories\Focus\advance_payment;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\advance_payment\AdvancePayment;
use App\Models\items\UtilityBillItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

class AdvancePaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = AdvancePayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        if (!access()->allow('department-manage'))
            $q->where('employee_id', auth()->user()->id);
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return AdvancePayment $advance_payment
     */
    public function create(array $input)
    {
        // dd($input);
        $input['amount'] = numberClean($input['amount']);
        $input['date'] = date_for_database($input['date']);
        
        $result = AdvancePayment::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.advance_payment.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param AdvancePayment $advance_payment
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(AdvancePayment $advance_payment, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        if (empty($input['status'])) {
            $input['amount'] = numberClean($input['amount']);
            $input['date'] = date_for_database($input['date']);    
        } else {
            $input['approve_amount'] = numberClean($input['approve_amount']);
            if ($input['approve_amount'] == 0) throw ValidationException::withMessages(['Amount is required!']);
            $input['approve_date'] = date_for_database($input['approve_date']);  
        }

        $prev_note = $advance_payment->note;
        $result = $advance_payment->update($input);

        if ($advance_payment->status == 'approved') {
            UtilityBill::where(['note' => $prev_note, 'ref_id' => $advance_payment->id])->delete();
            $this->generate_bill($advance_payment);
        }
           
        if ($result) {
            DB::commit();
            return $result;   
        }

        throw new GeneralException(trans('exceptions.backend.advance_payment.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param AdvancePayment $advance_payment
     * @throws GeneralException
     * @return bool
     */
    public function delete(AdvancePayment $advance_payment)
    {
        DB::beginTransaction();

        UtilityBill::where(['note' => $advance_payment, 'ref_id' => $advance_payment->id])->delete();

        if ($advance_payment->delete()) {
            DB::commit();
            return true;
        }
            
        throw new GeneralException(trans('exceptions.backend.advance_payment.delete_error'));
    }

    /**
     * Generate Advance Payment Bill
     */
    public function generate_bill($payment)
    {
        $tid = UtilityBill::max('tid') + 1;
        $bill_data = [
            'tid' => $tid,
            'employee_id' => $payment->employee_id,
            'document_type' => 'advance_payment',
            'ref_id' => $payment->id,
            'date' => $payment->date,
            'due_date' => $payment->date,
            'subtotal' => $payment->approve_amount,
            'total' => $payment->approve_amount,
            'note' => $payment->approve_note,
        ];
        $bill = UtilityBill::create($bill_data);

        $bill_items_data = [
            'bill_id' => $bill->id,
            'ref_id' => $payment->id,
            'note' => $payment->approve_note,
            'qty' => 1,
            'subtotal' => $payment->approve_amount,
            'total' => $payment->approve_amount, 
        ];
        UtilityBillItem::create($bill_items_data);
    }
}
