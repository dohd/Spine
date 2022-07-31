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
     * @var Illuminate\Support\Collection $data
     */
    private $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * 
     * @param Illuminate\Support\Collection $rows
     * @return void
     */
    public function collection($rows)
    {
        $equipments_data = array();
        $tid = Equipment::max('tid');
        $ins = auth()->user()->ins;
        $columns = array();
        foreach ($rows as $i => $row) {
            if ($i == 0) {
                $columns = $row;
                continue;
            }
            if (count($row) != count($columns)) throw new Error('Columns mismatch!');
            foreach ($columns as $j => $label) {
                $val = $row[$j];
                if (in_array($label, ['id', 'created_at', 'updated_at'])) continue;
                if ($label == 'ins') $val = $ins;
                if ($label == 'tid') {
                    $tid++;
                    $val = $tid;
                }
                $equipments_data[$i][$label] = $val;
            }
        }

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
