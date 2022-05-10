@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.management'))

@section('content') 
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Management</h4>
        </div>
        <div class="content-header-right col">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card todo-details rounded-0">
            <div class="sidebar-toggle d-block d-lg-none info"><i class="ft-menu font-large-1"></i></div>
            <div class="search"></div>
            <div class="card-body">
                <table id="projects-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>#Project No</th>
                            <th>Name</th>
                            <th>Customer</th>
                            <th>#Quote / PI No</th>
                            <th>Ticket No</th>
                            <th>Start Status</th>  
                            <th>Start Date</th>
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
@include('focus.projects.modal.project_new')
@endsection

@section('after-styles')
{{ Html::style('core/app-assets/css-'.visual().'/pages/app-todo.css') }}
{{ Html::style('core/app-assets/css-'.visual().'/plugins/forms/checkboxes-radios.css') }}
{!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
{{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    function draw_data() {
        const language = {@lang('datatable.strings')};
        var dataTable = $('#projects-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: "{{ route('biller.projects.get') }}",
                type: 'post'
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'quote_tid',
                    name: 'quote_tid'
                },
                {
                    data: 'lead_tid',
                    name: 'lead_tid'
                },
                {
                    data: 'start_status',
                    name: 'start_status'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
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

    // On Create Modal click
    $('#AddProjectModal').on('shown.bs.modal', function() {
        $("#main_quote").select2();
        $("#other_quote").select2();
        $("#branch_id").select2();
        // fetch customers
        $("#person").select2({
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({ search: term }),
                processResults: (data) => {
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
            $("#branch_id").html('').select2({
                ajax: {
                    url: "{{ route('biller.branches.branch_load') }}",
                    quietMillis: 50,
                    data: ({term}) => ({ 
                        search: term, 
                        customer_id: $("#person").val()
                    }),
                    processResults: data => ({ results: data.map(v => ({ text: v.name, id: v.id })) }),
                }
            });

            // fetch customer quotes
            $("#main_quote").html('').select2({
                ajax: {
                    url: "{{ route('biller.quotes.customer_quotes') }}?id=" + $(this).val(),
                    dataType: 'json',
                    quietMillis: 50,
                    processResults: function(data) {
                        const results = data.map(v => {
                            const tid = (''+v.tid).length < 4 ? ('000'+v.tid).slice(-4) : v.tid;
                            const text = `${v.bank_id ? '#PI-' : '#QT-'}${tid} - ${v.branch.name} - ${v.notes}`;
                            return {id: v.id, text}; 
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
        $("#main_quote").change(function() {
            // set Other Quote select options 
            $("#other_quote").html('').select2({ 
                data: quoteData.filter(v => v.id != $(this).val())
            });
            const quoteTitle = $(this).find(':selected').text().split(' - ')[2];
            $('#project-name').val(quoteTitle);
        });
    });
</script>
@endsection