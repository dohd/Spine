<?php

namespace App\Imports;

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
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
        if (empty($this->data['category_id']) || empty($this->data['warehouse_id']))
            trigger_error('Category or Warehouse is required!');
            
        $category_id = $this->data['category_id'];
        $warehouse_id = $this->data['warehouse_id'];

        foreach ($rows as $key => $row) {
            $row_num = $key+1;
            if ($row_num == 1 && $row->count() < 13) {
                trigger_error('Missing columns! Use latest CSV file template.');
            } elseif ($row_num > 1) {
                if (empty($row[0])) trigger_error('Product Name is required on row no. $row_num');
                if (empty($row[3])) trigger_error('Unit is required on row no. $row_num');

                $unit = Productvariable::where(['code' => $row[3], 'unit_type' => 'base'])->first();
                $product = Product::create([
                    'productcategory_id' => $category_id,
                    'name' => $row[0],
                    'taxrate' => numberClean($row[1]),
                    'product_des' => empty($row[2])? $row[0] : $row[2],
                    'unit_id' => $unit? $unit->id : null,
                    'code_type' => $row[10],
                    'ins' => $this->data['ins'],
                ]);
                ProductVariation::create([
                    'parent_id' => $product->id,
                    'name' => $product->name,
                    'warehouse_id' => $warehouse_id,
                    'code' => $row[4],
                    'price' => numberClean($row[5]),
                    'purchase_price' => numberClean($row[6]),
                    'disrate' => numberClean($row[7]),
                    'qty' => numberClean($row[8]),
                    'alert' => numberClean($row[9]),
                    'barcode' => $row[11],
                    'expiry' => date_for_database($row[12]),
                    'ins' => $product->ins,
                ]);
                ++$this->rows;
            }            
        }
    }

    public function rules(): array
    {
        return [
            '0' => 'required|string',
            '3' => 'required|string',
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
