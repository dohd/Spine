<?php

namespace App\Http\Controllers\Focus\health_and_safety;

use App\Http\Controllers\Controller;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HealthAndSafetyTrackingTableController extends Controller
{
    const MODEL = HealthAndSafetyTracking::class;

    public function __invoke()
    {
        //
        // $core = $this->term->getForDataTable();
        // $core = HealthAndSafetyTracking::where('date', request('date'))
        //     ->where('pdca_cycle', request('pdca_cycle'))
        //     ->get();

        $core = HealthAndSafetyTracking::when(request('date'), function ($q) {
            $q->where('date', request('date'));
        })->when(request('pdca_cycle'), function ($q) {
            $q->where('pdca_cycle', request('pdca_cycle'));
        })->get();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($healthAndSafetyTracking) {
                return dateFormat($healthAndSafetyTracking->date);
            })
            ->addColumn('client', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->client ? $healthAndSafetyTracking->client->name : " ";
            })
            ->addColumn('project', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->project ? $healthAndSafetyTracking->project->name : " ";
            })
            ->addColumn('incident', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->incident_desc;
            })
            ->addColumn('root_cause', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->route_course;
            })
            ->addColumn('status', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->status;
            })
            ->addColumn('pdca_cycle', function ($healthAndSafetyTracking) {
                // $pdcaCycle= '';
                if ($healthAndSafetyTracking->pdca_cycle == 'plan') {
                    $pdcaCycle = "Action Identified";
                } elseif ($healthAndSafetyTracking->pdca_cycle == 'do') {
                    $pdcaCycle = "Action Being Implemented";
                } elseif ($healthAndSafetyTracking->pdca_cycle == 'check') {
                    $pdcaCycle = "Action Being Evaluated";
                } elseif ($healthAndSafetyTracking->pdca_cycle == 'act') {
                    $pdcaCycle = "Action Closed";
                }

                return $pdcaCycle;
            })
            ->addColumn('resolution_time', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->timing;
            })
            // ->addColumn('created_at', function ($healthAndSafetyTracking) {
            //     return dateFormat($healthAndSafetyTracking->created_at);
            // })
            ->addColumn('actions', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->action_buttons;
            })
            ->make(true);
    }
}
