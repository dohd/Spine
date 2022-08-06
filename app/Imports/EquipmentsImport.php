<?php

namespace App\Imports;

use App\Models\equipment\Equipment;
use Error;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EquipmentsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     *
     * @var int $row_count
     */
    private $row_count = 0;

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
        $has_customer = isset($this->data['customer_id']);
        $has_branch = isset($this->data['branch_id']);

        $equipments_data = [];
        $columns = [];
        $tid = Equipment::max('tid') + 1;

        foreach ($rows as $i => $row) {
            if ($i == 0) {
                $columns = $row;
                continue;
            } elseif (count($row) != count($columns)) {
                throw new Error('Columns mismatch!');
            }
            
            $new_row = [];
            foreach ($columns as $j => $col) {
                $value = $row[$j];
                if (in_array($col, ['id', 'created_at', 'updated_at'])) $value = null;                
                $new_row[$col] = $value;
            }
            $new_row['ins'] = $this->data['ins'];
            $new_row['tid'] = $tid;
            if ($new_row['customer_id'] == $this->data['customer_id']) {
                $is_invalid_branch = $has_branch && $this->data['branch_id'] != $new_row['branch_id'];
                if ($is_invalid_branch) throw new Error('Branch does not exist!');
                $equipments_data[$i] = $new_row;
                $tid++;
            }
        }

        // delete previous data
        if ($has_customer && $has_branch) {
            Equipment::where([
                'customer_id' => $this->data['customer_id'], 
                'branch_id' => $this->data['branch_id']
            ])->delete();
        } else Equipment::where(['customer_id' => $this->data['customer_id']])->delete();
            
        $result = Equipment::insert($equipments_data);
        if ($result) $this->row_count = count($equipments_data);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
            '1' => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->row_count;
    }

    public function startRow(): int
    {
        return 2;
    }
}
