<?php

namespace App\Imports;

use App\Models\equipment\Equipment;
use App\Models\equipmentcategory\EquipmentCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EquipmentsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     *
     * @var int $rows
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
        if (empty($this->data['customer_id'])) trigger_error('Customer is required!');

        $equip_data = [];
        $tid = Equipment::where('ins', $this->data['ins'])->max('tid')+1;
        foreach ($rows as $key => $row) {
            $row_num = $key+1;
            if ($row_num == 1 && $row->count() < 11) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[0])) trigger_error('Equipment Category is required!');

                $category = EquipmentCategory::where('name', 'LIKE', $row[0])->first();
                if (!$category) $category = EquipmentCategory::create(['name' => $row[0], 'ins' => $this->data['ins']]);

                $equip_data[] = [
                    'equipment_category_id' => $category->id,
                    'customer_id' => $this->data['customer_id'],
                    'branch_id' => isset($this->data['branch_id'])? $this->data['branch_id'] : null,
                    'equip_serial' => $row[1],
                    'unique_id' => $row[2],
                    'capacity' => $row[3],
                    'location' => $row[4],
                    'machine_gas' => $row[5],
                    'make_type' => $row[6],
                    'model' => $row[7],
                    'service_rate' => numberClean($row[8]),
                    'building' => $row[9],
                    'floor' => $row[10],
                    'tid' => $tid,
                    'ins' => $this->data['ins'],
                ];
                $tid++;
                ++$this->rows;
            }            
        }
        Equipment::insert($equip_data);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
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
