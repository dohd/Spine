<?php

namespace App\Imports;

use App\Models\client_product\ClientProduct;
use Error;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClientPricelistImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
        $columns = [
            'row_num','description','uom','rate'
        ];

        $row_count = 0;
        $label_count = count($columns);
        foreach ($rows as $i => $row) {
            $row = array_slice($row->toArray(), 0, $label_count);
            
            if ($i == 0) {
                $omitted_cols = array_diff($columns, $row);
                if ($omitted_cols) throw new Error('Column label mismatch: ' . implode(', ',$omitted_cols));
                continue;
            }

            $row_data = array_combine($columns, $row);
            $row_data = array_replace($row_data, [
                'descr' => $row_data['description'],
                'contract' => $this->data['contract'],
                'customer_id' => $this->data['customer_id'],
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id
            ]);
            unset($row_data['description']);
            foreach ($row_data as $key => $val) {
                if ($key == 'rate') $row_data[$key] = numberClean($row_data['rate']);
                if (strcasecmp($val, 'null') == 0) $row_data[$key] = null;
            }
            
            $result = ClientProduct::create($row_data);
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
