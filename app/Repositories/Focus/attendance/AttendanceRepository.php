<?php

namespace App\Repositories\Focus\attendance;

use App\Exceptions\GeneralException;
use App\Models\attendance\Attendance;
use App\Repositories\BaseRepository;
use DateTime;
use Illuminate\Support\Arr;

class AttendanceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Attendance::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Attendance $attendance
     */
    public function create(array $input)
    {
        // dd($input);
        $data_items = Arr::only($input, ['clock_in', 'clock_out', 'status', 'employee_id']);
        $data_items = array_filter(modify_array($data_items), function ($v) { 
            return $v['clock_in'] && $v['clock_out'];
        });

        $date = date_for_database(implode('-', [date('Y'), $input['month'], $input['day']]));
        $data_items = array_map(function ($v) use($date) {
            $c1 = new DateTime($v['clock_in']);
            $c2 = new DateTime($v['clock_out']);
            $hrs = $c2->diff($c1)->format('%h');
            return array_replace($v, compact('date', 'hrs'));
        }, $data_items);

        $employee_ids = array_map(function ($v) { return $v['employee_id']; }, $data_items);
        $attendances = Attendance::whereMonth('date', $input['month'])
            ->whereIn('employee_id', $employee_ids)
            ->where('is_overtime', 0)
            ->get();

        if ($attendances->count()) {
            $updated_employee_ids = [];
            // update attendance
            foreach ($attendances as $attendance) {
                foreach ($data_items as $item) {
                    $d = (int) (new DateTime($attendance['date']))->format('d');
                    $same_day = $input['day'] == $d;
                    $same_employee = $attendance->employee_id == $item['employee_id'];
                    if ($same_employee && $same_day) {
                        $attendance->update($item);
                        $updated_employee_ids[] = $item['employee_id'];
                        break;
                    } 
                }
            }
            // exclude updated data
            $data_items = array_filter($data_items, function ($v) use($updated_employee_ids) { 
                return !in_array($v['employee_id'], $updated_employee_ids);
            });
        } 
        // save new attendance
        foreach ($data_items as $item) {
            Attendance::create($item);
        }
        if ($data_items) return true;
                    
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Attendance $attendance
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Attendance $attendance, array $input)
    {
        dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }

        if ($attendance->update($input)) return $attendance;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Attendance $attendance
     * @throws GeneralException
     * @return bool
     */
    public function delete(Attendance $attendance)
    {
        if ($attendance->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
