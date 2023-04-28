<?php

namespace App\Imports;

use App\Models\account\Account;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AccountsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $rows = 0;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function collection(Collection $rows)
    {
        // dd($rows);
        $account_data = [];
        $no_queue = [];
        foreach ($rows as $key => $row) {
            $row_num = $key+1;
            if ($row_num == 1 && $row->count() < 3) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[0])) trigger_error('Account Name is required on row no. $row_num');
                if (empty($row[1])) trigger_error('Account Type is required on row no. $row_num');

                $is_exist = Account::where('account_type', 'LIKE', $row[1])->where('holder', 'LIKE', $row[0])->exists();
                if ($is_exist) continue;

                $acc_no = Account::where('account_type', 'LIKE', $row[1])->max('number');
                if (isset($no_queue[$row[1]])) $no_queue[$row[1]] += 1;
                else $no_queue[$row[1]] = $acc_no+1;

                $account_data[] = [
                    'holder' => $row[0],
                    'account_type' => ucfirst($row[1]),
                    'note' => empty($row[2])? $row[0]: $row[2],
                    'number' => $no_queue[$row[1]],
                    'ins' => $this->data['ins'],
                ];
                ++$this->rows;
            }            
        }
        Account::insert($account_data);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
            '1' => 'required|string',
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
