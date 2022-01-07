@extends ('core.layouts.app')

@section ('title', 'LPO Management')

@section('content')
<div>    
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title">LPO Management</h4>
            </div>
            <div class="content-header-right col">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.lpo.partials.lpo-header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card todo-details rounded-0">
                <div class="sidebar-toggle d-block d-lg-none info"><i class="ft-menu font-large-1"></i></div>
                <div class="search"></div>
                <div class="card-body">
                    <table id="lpo-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client & Branch</th>
                                <th>LPO No</th>
                                <th>LPO Amount</th>
                                <th>Invoiced (QT/PI)</th>
                                <th>Verified & Uninvoiced (QT/PI)</th>
                                <th>Approved & Unverified (QT/PI)</th>
                                <th>Balance</th>
                                <th>Action</th>
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
</div>
@include('focus.lpo.modal.lpo_new')
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
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

    // submit lpo-form
    $('#create-btn').click(function() {
        $('#lpo-form').submit();
    });

    // initialize customer select2
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
    });  

    // fetch table data
    function draw_data() {
        const tableLang = { @lang('datatable.strings') };
        var dataTable = $('#lpo-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLang,
            ajax: {
                url: "{{ route('biller.lpo.get') }}",
                type: 'post',
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'lpo_no',
                    name: 'lpo_no'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'verified',
                    name: 'verified'
                },
                {
                    data: 'verified_uninvoiced',
                    name: 'verified_uninvoiced'
                },
                {
                    data: 'approved_unverified',
                    name: 'approved_unverified'
                },
                {
                    data: 'balance',
                    name: 'balance'
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
</script>
@endsection