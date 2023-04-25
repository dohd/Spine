<?php

namespace App\Repositories\Focus\tax_prn;

use App\Exceptions\GeneralException;
use App\Models\tax_prn\TaxPrn;
use App\Repositories\BaseRepository;
use DateTime;
use Illuminate\Validation\ValidationException;

class TaxPrnRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TaxPrn::class;

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
     * @throws GeneralException
     * @return TaxPrn $tax_prn
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'amount') $input[$key] = numberClean($val);
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'return_month') {
                $date = DateTime::createFromFormat('m-Y', $input[$key]);
                if ($date) $input[$key] = $date->format('m-Y');
                else throw ValidationException::withMessages(['Valid date format required mm-YYYY']);
            }
        }

        $result = TaxPrn::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.tax_prn_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TaxPrn $tax_prn
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(TaxPrn $tax_prn, array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'amount') $input[$key] = numberClean($val);
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'return_month') {
                $date = DateTime::createFromFormat('m-Y', $input[$key]);
                if ($date) $input[$key] = $date->format('m-Y');
                else throw ValidationException::withMessages(['Valid date format required mm-YYYY']);
            }
        }

        if ($tax_prn->update($input)) return $tax_prn;

        throw new GeneralException(trans('exceptions.backend.tax_prn_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TaxPrn $tax_prn
     * @throws GeneralException
     * @return bool
     */
    public function delete(TaxPrn $tax_prn)
    {
        if ($tax_prn->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.tax_prn_category.delete_error'));
    }
}
