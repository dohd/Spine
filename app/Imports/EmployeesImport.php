<?php

namespace App\Imports;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\salary\Salary;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     *
     * @var int $row_count
     */
    private $rows = 0;

    /**
     *
     * @var array $data
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 
     * @param Illuminate\Support\Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {   
        // dd($rows);
        // if (empty($this->data['supplier_id']) || empty($this->data['contract']))
        //     trigger_error('Supplier or Contract is required!');
        
        $employee = [];
        $hrm = [];
        $salary = [];
        foreach ($rows as $key => $row) {
            $row_num = $key+1;
            if ($row_num == 1 && $row->count() < 4) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[1])) trigger_error('First Name is required on row no. $row_num');
                if (empty($row[2])) trigger_error('Last Name is required on row no. $row_num');
                if (empty($row[8])) trigger_error('Email is required on row no. $row_num');

                $employee = [
                    'first_name' => $row[1],
                    'last_name' => $row[2],
                    'email' => $row[8],
                    'ins' => $this->data['ins'],
                    'status' => '1',
                    'confirmed' => '1',
                ];
                $dob = dateFormat($row[4]);
                $hrm = [
                    'employee_no' => $row[0],
                    'id_number' => $row[3],
                    'primary_contact' => $row[5],
                    'gender' => $row[6],
                    'marital_status' => $row[7],
                    'home_county' => $row[9],
                    'home_address' => $row[10],
                    'bank_name' => $row[11],
                    'account_name' => $row[12],
                    'account_number' => $row[13],
                    'branch' => $row[14],
                    'kra_pin' => $row[15],
                    'nssf' => $row[16],
                    'nhif' => $row[17],
                    'dob' => Carbon::createFromDate($dob)->format('Y-m-d'),
                ];
                $start_date = dateFormat($row[20]);
                $salary = [
                    'basic_pay' => $row[18],
                    'contract_type' => $row[19],
                    'start_date' =>  Carbon::createFromDate($start_date)->format('Y-m-d'),
                    'duration' => $row[21],
                    'ins' => $this->data['ins'],
                    'user_id' => auth()->user()->id,
                ];
                $employees = Hrm::create($employee);
                $hrm['user_id'] = $employees->id;
                 HrmMeta::create($hrm);
                 $salary['employee_id'] = $employees->id;
                 Salary::create($salary);
                ++$this->rows;
            }  
            
                    
        }
    
        
    }

    public function rules(): array
    {
        return [
            '1' => 'required|string',
            '2' => 'required|string',
            '8' => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function startRow(): int
    {
        return 1;
    }
}
