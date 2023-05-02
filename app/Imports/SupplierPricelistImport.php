<?php

namespace App\Imports;

use App\Models\supplier_product\SupplierProduct;
use Error;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SupplierPricelistImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
        //add extra columns  
        $columns = [
            'Id','Description','Category Name','product_code','uom','row_num','description','rate'
        ];

        $row_count = 0;
        foreach ($rows as $i => $row) {
            $row = $row->toArray();
            $row = array_slice($row, 0, count($columns));
            printlog($row);
            if ($i == 0) {
                $omitted_cols = array_diff($columns, $row);
                if ($omitted_cols) throw new Error('Column label mismatch: ' . implode(', ',$omitted_cols));
                continue;
            } elseif (count($row) != count($columns)) {
                throw new Error('Column mismatch on row ' . strval($i+1)  . '!');
            }
            //add Array Slice
            $row_data = array_combine($columns, $row);
            $row_data = array_replace($row_data, [
                'product_code' => $row_data['product_code'],
                'descr' => $row_data['description'],
                'contract' => $this->data['contract'],
                'supplier_id' => $this->data['supplier_id'],
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id
            ]);
            unset($row_data['description']);
            unset($row_data['Description']);
            unset($row_data['Category Name']);
            unset($row_data['Id']);
            foreach ($row_data as $key => $val) {
                if ($key == 'rate') $row_data[$key] = numberClean($row_data['rate']);
                if (strcasecmp($val, 'null') == 0) $row_data[$key] = null;
            }
            
            $result = SupplierProduct::create($row_data);
            if ($result) $row_count++;
        }

        if (!$row_count) throw new Error('Please fill template with required data');
        $this->row_count = $row_count;
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
