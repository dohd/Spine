<?php

namespace App\Imports;

use App\Models\equipment\Equipment;
use App\Models\region\Region;
use App\Models\branch\Branch;
use App\Models\equipmentcategory\EquipmentCategory;
use App\Models\section\Section;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class EquipmentsImport implements ToCollection, WithBatchInserts, WithValidation, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $rows = 0;
    private $records;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }


    public function collection(Collection $rows)
    {
          if (isset($this->data['customer'])) $customer = $this->data['customer']; else return false;
           //if (isset($this->data['warehouse'])) $warehouse = $this->data['warehouse']; else return false;
        ++$this->rows;
        foreach ($rows as $row) {
            if (count($row) == 19) {


               //Add region
                    //Check if region exists else create new
                    $region_name = $row[0];
                    if (!empty($region_name)) {
                        $region = Region::firstOrCreate(
                            ['name' => $region_name,'ins' => auth()->user()->ins]
                        );
                        $region_id = $region->id;
                    }

                       //Add branch
                    //Check if branch exists else create new
                    $branch_name = $row[1];
                    if (!empty($branch_name)) {
                        $branch = Branch::firstOrCreate(
                            ['name' => $branch_name,'ins' => auth()->user()->ins]
                        );
                        $branch_id = $branch->id;
                    }



                       //Add section
                    //Check if section exists else create new
                    $section_name = $row[2];
                    if (!empty($section_name)) {
                        $section = Section::firstOrCreate(
                            ['name' => $section_name,'ins' => auth()->user()->ins]
                        );
                        $section_id = $section->id;
                    }

                       $category_name = $row[4];
                    if (!empty($category_name)) {
                        $category = EquipmentCategory::firstOrCreate(
                            ['name' => $category_name,'ins' => auth()->user()->ins]
                        );
                        $category_id = $category->id;
                    }

                    


                $equipment = new  Equipment([
                   'customer_id'=>$customer,
                   'region_id' =>$region_id,
                    'branch_id' => $branch_id,
                    'section_id' => $section_id,
                    'location' => $row[3],
                    'equipment_category_id' => $category_id,
                    'unit_type' => $row[5],
                    'make_type' => $row[6],
                    'capacity' => $row[7],
                    'machine_gas' => $row[8],
                    'model' => $row[9],
                    'equip_serial' => $row[10],
                    'unique_id' => $row[11],
                    'related_equipments' => $row[12],
                    'main_duration' => $row[13],
                    'service_rate' => $row[14],
                    'attendance_rate' => $row[15],
                    'total_rate' => $row[16],
                    'actual_extra' => $row[17],
                    'equip_status' => $row[18],
                    'ins' => auth()->user()->ins

                ]);
            
                $equipment->save();

             
            }
            else {
               return false;
           }
        }


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
        return $this->rows;
    }

    public function startRow(): int
    {
        return 2;
    }
}
