@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.management'))

@section('page-header')
<h1>{{ trans('labels.backend.projects.management') }}</h1>
@endsection
@section('content')
<div class="">
    <div class="sidebar-left">
        <div class="sidebar">
            <div class="sidebar-content sidebar-todo">
                <div class="card">
                    <div class="card-body">
                        @permission( 'project-create' )
                        <div class="form-group form-group-compose text-center">
                            <button type="button" class="btn btn-info btn-block" id="addt" data-toggle="modal" data-target="#AddProjectModal">
                                {{trans('projects.new_project')}}
                            </button>
                        </div> @endauth
                        <div class="sidebar-todo-container">
                            <h6 class="text-muted text-bold-500 my-1">{{trans('general.messages')}}</h6>
                            <div class="list-group list-group-messages">
                                <a href="{{route('biller.dashboard')}}" class="list-group-item list-group-item-action border-0">
                                    <i class="icon-home mr-1"></i>
                                    <span>{{trans('navs.frontend.dashboard')}}</span>
                                </a>
                                <a href="{{route('biller.todo')}}" class="list-group-item list-group-item-action border-0">
                                    <i class="icon-list mr-1"></i>
                                    <span>{{trans('general.tasks')}}</span><span class="badge badge-secondary badge-pill float-right">8</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action border-0">
                                    <i class="icon-bell mr-1"></i>
                                    <span>{{trans('general.messages')}}</span>
                                    <span class="badge badge-danger badge-pill float-right">3</span> 
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content-right">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <div class="content-overlay"></div>
                <!-- Modal -->
                @include('focus.projects.modal.project_new')
                <div class="card todo-details rounded-0">
                    <div class="sidebar-toggle d-block d-lg-none info"><i class="ft-menu font-large-1"></i></div>
                    <div class="search"></div>
                    <div class="card-body">
                        <table id="projects-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('projects.project') }}</th>
                                    <th>Project No</th>
                                    <th>#Quote / PI No</th>
                                    <th>{{ trans('projects.priority') }}</th>
                                    <th>Started</th>                                    
                                    <th>{{ trans('projects.end_date') }}</th>
                                    <th>{{ trans('projects.status') }}</th>
                                    <th>{{ trans('general.createdat') }}</th>
                                    <th>{{ trans('general.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Content-->
<div class="sidenav-overlay"></div>
<div class="drag-target"></div>
<input type="hidden" id="loader_url" value="{{route('biller.projects.load')}}">
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
    // draw dataTable data
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    // ajax header set up
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function draw_data() {
        $("#submit-data_project").on("click", function(e) {
            e.preventDefault();
            var form_data = [];
            form_data['form'] = $("#data_form_project").serialize();
            form_data['url'] = $('#action-url').val();
            $('#AddProjectModal').modal('toggle');
            addObject(form_data, true);
        });

        const tableLang = { @lang('datatable.strings') };
        var dataTable = $('#projects-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLang,
            ajax: {
                url: "{{ route('biller.projects.get') }}",
                type: 'post'
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'project_number',
                    name: 'project_number'
                },
                {
                    data: 'quote_tid',
                    name: 'quote_tid'
                },
                {
                    data: 'priority',
                    name: 'priority'
                },
                {
                    data: 'started_status',
                    name: 'started_status'
                },
                {
                    data: 'deadline',
                    name: 'deadline'
                },
                {
                    data: 'progress',
                    name: 'progress'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [

                    {
                        extend: 'csv',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        extend: 'print',
                        footer: true,
                        exportOptions: {
                            columns: [0, 1]
                        }
                    }
                ]
            }
        });
    }

    $('#AddProjectModal').on('shown.bs.modal', function() {
        $('[data-toggle="datepicker"]').datepicker({ format: "{{config('core.user_date_format')}}" });

        $('.from_date')
            .datepicker('setDate', 'today')
            .datepicker({ format: "{{date(config('core.user_date_format'))}}" });

        // Add thirty days to the current date
        const d = new Date();
        const days_in_ms = (30 * 24 * 60 * 60 * 1000);
        d.setTime(d.getTime() + days_in_ms);
        $('.to_date')
            .datepicker({ format: "{{ config('core.user_date_format') }}" })
            .datepicker('setDate', d);

        // initiate select2 select menu
        $("#main_quote").select2();
        $("#other_quote").select2();
        $("#branch_id").select2();
        $("#tags").select2();
        $("#employee").select2();
        $("#sales_account").select2();
        $('#color').colorpicker();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // fetch customers
        $("#person").select2({
            tags: [],
            ajax: {
                url: "{{route('biller.customers.select')}}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: function(person) {
                    return { person };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: `${item.name} - ${item.company}`,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });

        // on selecting customer fetch branches
        const quoteData = [];
        $("#person").on('change', function() {
            var id = $('#person :selected').val();
            // fetch customer branches
            $("#branch_id").html('').select2({
                ajax: {
                    url: "{{route('biller.branches.branch_load')}}?id=" + id,
                    dataType: 'json',
                    quietMillis: 50,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });

            // fetch customer quotes
            $("#main_quote").html('').select2({
                ajax: {
                    url: "{{ route('biller.quotes.customer_quotes') }}?id=" + id,
                    dataType: 'json',
                    quietMillis: 50,
                    processResults: function(data) {
                        const results = $.map(data, function(item) {
                            const tid = String(item.tid).length < 4 ? ('000'+item.tid).slice(-4) : item.tid;
                            return {
                                text: `${item.bank_id ? '#PI-' : '#QT-'} ${tid} - ${item.notes}`,
                                id: item.id
                            };
                        });
                        // replace array data
                        quoteData.length = 0;
                        quoteData.push.apply(quoteData, results);

                        return { results };
                    },
                }
            });
        });

        // On selecting Main Quote
        $("#main_quote").change(function(e) {
            const id = Number(e.target.value);
            // set Other Quote select options 
            const data = quoteData.filter(function(item) { return id !== item.id; });
            $("#other_quote").html('').select2({ data });
            // set project title
            const name = $(this).find(':selected').text().split(' - ')[1];
            $('#project-name').val(name);
        });
    });

    function trigger(data) {
        $(data.row).prependTo("table > tbody");
        $("#data_form_project").trigger('reset');
    }

    $(document).on('click', ".view_project", function(e) {
        var did = $(this).attr('data-item');
        $.ajax({
            url: $('#loader_url').val(),
            type: 'POST',
            dataType: 'json',
            data: {
                'project_id': did
            },
            success: function(data) {
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