@extends ('core.layouts.app')

@section('title', 'Health and Safety Tracking')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title mb-0">Health and Safety Tracking</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.health_and_safety.partials.health-and-safety-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <label for="date">Date</label>
                                    <input type="date" id="date" name="date"
                                        class="datepicker form-control box-size mb-2">
                                        {{-- {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }} --}}
                                </div>
                                <div class="col-6">
                                    <label for="ticket">PDCA Cycle</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                aria-hidden="true"></span></div>
                                        <select class="custom-select" id="pdca_cycle" name="pdca_cycle">
                                            <option value="">Select PDCA Cycle</option>
                                            <option value="plan">Action Identified(PLAN)</option>
                                            <option value="do">Action Being Implemented(DO)</option>
                                            <option value="check">Action Being Evaluated(CHECK)</option>
                                            <option value="act">Action Closed(ACT)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="healthAndSafetyTable"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Project</th>
                                            <th>Incident</th>
                                            <th>Root Cause</th>
                                            <th>Status</th>
                                            <th>PDCA Cycle</th>
                                            <th>Resolution Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}

    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        };

        // $('#date').datepicker({
        //     autoHide: true,
        //     format: '{{ config('core.user_date_format') }}'
        // });
        // $('#date').datepicker('setDate', '{{ date(config('core.user_date_format')) }}');

        // $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

        const Index = {
            date: @json(request('date')),
            pdca_cycle: @json(request('pdca_cycle')),

            init() {
                this.drawDataTable();
                $('#date').val(this.dateId).change(this.dateChange);
                $('#pdca_cycle').val(this.pdcaCycleId).change(this.pdcaCycleChange);
            },

            pdcaCycleChange() {
                Index.pdcaCycleId = $(this).val();
                $('#healthAndSafetyTable').DataTable().destroy();
                return Index.drawDataTable();
            },

            dateChange() {
                Index.dateId = $(this).val();
                $('#healthAndSafetyTable').DataTable().destroy();
                return Index.drawDataTable();
            },

            drawDataTable() {
                $('#healthAndSafetyTable').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.health-safety-table.get') }}',
                        data: {
                            date: this.dateId,
                            pdca_cycle: this.pdcaCycleId,
                        },
                        type: 'post'
                    },

                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'client',
                            name: 'client'
                        },
                        {
                            data: 'project',
                            name: 'project'
                        },
                        {
                            data: 'incident',
                            name: 'incident'
                        },
                        {
                            data: 'root_cause',
                            name: 'root_cause'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'pdca_cycle',
                            name: 'pdca_cycle'
                        },
                        {
                            data: 'resolution_time',
                            name: 'resolution_time'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
        };

        $(() => Index.init());
    </script>
@endsection
