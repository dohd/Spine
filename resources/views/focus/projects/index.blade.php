@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.management'))

@section('content')
    <div class="content-wrapper">
        <!-- Header -->
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Project Management</h4>
            </div>
            <div class="col-6">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>
        <!-- End Header -->

        <!-- Left sidebar -->
        @include('focus.projects.partials.sidebar')
        <!-- End Left sidebar -->

        <!-- Content -->
        <div class="content-right">
            <div class="content-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                    <option value=""></option>
                                    @foreach ([] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <hr>
                        <table id="projectsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Project No.</th>
                                    <th>Name</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Deadline</th>
                                    <th>{{ trans('general.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1">
                                        <i class="fa fa-spinner spinner"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Content -->
    </div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    {{-- <input type="hidden" id="loader_url" value="{{route('biller.projects.load')}}"> --}}
    @include('focus.projects.modal.project_new')
    @include('focus.projects.modal.project_view')
@endsection
@section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/app-todo.css') }}
    {{ Html::style('core/app-assets/css-'.visual().'/plugins/forms/checkboxes-radios.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection
@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
{{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) };
                },
            }
        }
    };

    // form submit callback
    function trigger(res) {
        // $(data.row).prependTo("table > tbody");
        // $("#data_form_project").trigger('reset');
        $('#projects-table').DataTable().destroy();
        Index.drawDataTable();
    }

    const Index = {
        customerBrances: [],

        init() {
            $.ajaxSetup(config.ajax);
            $("#submit-data_project").on("click", Index.onSubmitProject);
            $("#customerFilter").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);
            $('#AddProjectModal').on('shown.bs.modal', Index.onShownModal);
            Index.drawDataTable();
        },
        
        onSubmitProject() {
            e.preventDefault();
            let form_data = {};
            form_data['form'] = $("#data_form_project").serialize();
            form_data['url'] = $('#action-url').val();
            $('#AddProjectModal').modal('toggle');
            addObject(form_data, true);
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeBranch() {
            $('#projectsTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        onShownModal() {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });

            $('.from_date').datepicker('setDate', 'today');
            $('.from_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
            $('.to_date').datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('+30 days', strtotime(date('Y-m-d')))))}}');
            $('.to_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
            $("#tags").select2();
            $("#employee").select2();
            $("#sales_account").select2();
            $('#color').colorpicker();
            
            $("#person").select2({
                ajax: {
                    url: '{{route('biller.customers.select')}}',
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: (person) => ({person}),
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name+' - '+item.company,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });

            $("#person").on('change', function () {
                $("#branch_id").val('').trigger('change');
                const tips = $('#person').val();

                $("#branch_id").select2({
                    ajax: {
                        url: "{{ route('biller.branches.select') }}",
                        dataType: 'json',
                        type: 'POST',
                        quietMillis: 50,
                        params: {'cat_id': tips},
                        data: (person) => ({person, customer_id: tips}),
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {
                                        text: item.name,
                                        id: item.id
                                    }
                                })
                            };
                        },
                    }
                });
            });
            
        },

        drawDataTable() {
            $('#projectsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.projects.get') }}",
                    type: 'POST',
                    data: {
                        customer_id: $("#customerFilter").val(),
                        branch_id: $("#branchFilter").val(),
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['tid', 'name', 'priority', 'status', 'end_date'].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init);
</script>
@endsection
