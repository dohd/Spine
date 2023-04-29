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
        <div class="sidebar-left">
            <div class="sidebar">
                <div class="sidebar-content">                        
                    <div class="card mr-1">
                        <div class="card-body">
                            <div class="card-content">
                                @permission('create-project')
                                    <div class="form-group form-group-compose text-center">
                                        <button type="button" class="btn btn-success btn-block" id="addt" data-toggle="modal" data-target="#AddProjectModal">
                                            {{trans('projects.new_project')}}
                                        </button>
                                    </div> 
                                @endauth
        
                                <div class="sidebar-todo-container">
                                    <h6 class="text-muted text-bold-500 my-1">{{trans('general.messages')}}</h6>
                                    <div class="list-group list-group-messages">
                                        <a href="{{route('biller.dashboard')}}" class="list-group-item list-group-item-action border-0">
                                            <i class="icon-home mr-1"></i>
                                            <span>{{trans('navs.frontend.dashboard')}}</span>
                                        </a>
                                        <a href="{{route('biller.todo')}}" class="list-group-item list-group-item-action border-0">
                                            <i class="icon-list mr-1"></i>
                                            <span>{{trans('general.tasks')}}</span>
                                            <span class="badge badge-secondary badge-pill float-right">8</span>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action border-0">
                                            <i class="icon-bell mr-1"></i>
                                            <span>{{trans('general.messages')}}</span>
                                            <span class="badge badge-danger badge-pill float-right">3</span> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Left sidebar -->

        <!-- Content -->
        <div class="content-right">
            <div class="content-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <select class="form-control custom-select" id="customer">
                                    <option value="">-- Filter Customer --</option>
                                    @foreach ([] as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <select class="form-control custom-select" id="branch">
                                    <option value="">-- Filter Branch --</option>
                                    @foreach ([] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <hr>
                        <table id="projects-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
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
    
    setTimeout(() => draw_data(), {{config('master.delay')}});
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    // on project submit 
    $("#submit-data_project").on("click", function (e) {
        e.preventDefault();
        var form_data = {};
        form_data['form'] = $("#data_form_project").serialize();
        form_data['url'] = $('#action-url').val();
        $('#AddProjectModal').modal('toggle');
        addObject(form_data, true);
    });
    // form submit callback
    function trigger(res) {
        // $(data.row).prependTo("table > tbody");
        // $("#data_form_project").trigger('reset');
        $('#projects-table').DataTable().destroy();
        draw_data();
    }

    // create project modal shown
    $('#AddProjectModal').on('shown.bs.modal', function () {
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
    });

    // dataTable
    function draw_data() {
        $('#projects-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.projects.get") }}',
                type: 'post'
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
    }

    $(document).on('click', ".view_project", function (e) {
        var did = $(this).attr('data-item');
        $.ajax({
            url: $('#loader_url').val(),
            type: 'POST',
            dataType: 'json',
            data: {'project_id': did},
            success: function (data) {
                $('#p_id').val(data.id);
                $('#p_name').text(data.name);
                $('#ps_description').text(data.short_desc);
                $('#p_description').text(data.note);
                $('#p_start').text(data.start_date);
                $('#p_end').text(data.end_date);
                $('#p_creator').text(data.creator);
                $('#p_assigned').text(data.assigned);
                $('#p_status').html(data.status);
                $('#p_status_list').empty();
                $('#p_status_list').append(data.status_list);
                $('#d_view').attr('href', data.view);
            }
        });
    });

</script>
@endsection
