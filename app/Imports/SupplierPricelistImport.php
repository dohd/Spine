<?php

namespace App\Imports;

use App\Models\supplier_product\SupplierProduct;
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
        if (empty($this->data['supplier_id']) || empty($this->data['contract']))
            trigger_error('Supplier or Contract is required!');
        
        $product_data = [];
        foreach ($rows as $key => $row) {
            $row_num = $key+1;
            if ($row_num == 1 && $row->count() < 4) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[1])) trigger_error('Product Name is required on row no. $row_num');
                if (empty($row[2])) trigger_error('Unit is required on row no. $row_num');
                if (empty($row[3])) trigger_error('Price is required on row no. $row_num');

                $product_data[] = [
                    'supplier_id' => $this->data['supplier_id'],
                    'contract' => $this->data['contract'],
                    'row_num' => $row[0],
                    'descr' => $row[1],
                    'uom' => $row[2],
                    'rate' => numberClean($row[3]),
                    'ins' => $this->data['ins'],
                    'user_id' => auth()->user()->id,
                ];
                ++$this->rows;
            }            
        }
        SupplierProduct::insert($product_data);
    }

    public function rules(): array
    {
        return [
            '1' => 'required|string',
            '2' => 'required|string',
            '3' => 'required',
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
