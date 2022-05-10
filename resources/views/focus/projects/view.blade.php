@extends('core.layouts.app')

@section('title', 'Project Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <a href="javascript:" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#closeProject">
                </i> End Project
            </a>
        </div>
        <div class="card-body">
            <table id="projectTbl" class="table table-lg table-bordered">
                <tbody>
                    @php
                        $quote_tid = $project->quote->bank_id ? gen4tid('PI-', $project->quote->tid) : gen4tid('QT-', $project->quote->tid);
                        $details = [
                            'Project No' => gen4tid('Prj-', $project->tid),
                            'Name' => $project->name,
                            'Customer' => $project->customer_project->company,
                            'Start Date' => dateFormat($project->start_date),
                            'Note' => $project->note,
                            'Quote' => $quote_tid . ' - ' . $project->quote->notes
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.projects.modal.close-project')
@include('focus.projects.modal.project_new')
@endsection

@section('after-scripts')
<script>
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());
</script>
@endsection