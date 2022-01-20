@extends ('core.layouts.app')
@section ('title', 'Create Project Invoice')
@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Create Project Invoice</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        {{-- @include('focus.invoices.partials.invoices-header-buttons') --}}
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
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        {{ Form::open(['route' => 'biller.invoices.create_project_invoice', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'mass_add_form']) }}
                                        {!! Form::hidden('selected_products', null, ['id' => 'selected_products']); !!}
                                        {!! Form::hidden('customer', null, ['id' => 'customer']); !!}
                                        {!! Form::submit('Add Selected', array('class' => 'btn btn-xs btn-success update_product_location mt-2', 'id' => 'add-selected')) !!}
                                        {{ Form::close() }}
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><strong>Customer :</strong></label>
                                        {!! Form::select('customer_id', $customers, null, ['placeholder' => 'All Customers', 'id'=>'customer_id','class' => 'form-control']); !!}
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><strong>LPO :</strong></label>
                                        {!! Form::select('lpo_number', $lpos, null, ['placeholder' => 'All LPOS', 'id'=>'lpo_number','class' => 'form-control']); !!}
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><strong>Project :</strong></label>
                                        {!! Form::select('project_id', $projects, null, ['placeholder' => 'All Projects', 'id'=>'project_id','class' => 'form-control']); !!}
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
                                <table id="quotes-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-row"></th>
                                            <th>{{ trans('customers.customer') }}</th>
                                            <th># {{ trans('quotes.quote') }} / PI</th>
                                            <th>Title</th>
                                            <th>{{ trans('general.amount') }} (Ksh.)</th>
                                            <th>Verified (Ksh.)</th>
                                            <th>Quote / PI Date</th>
                                            <th>LPO No</th>
                                            <th>Project No</th>
                                            <th>Ticket No</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $(function() {
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");
        $('#search').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            if (start_date && end_date) {
                $('#quotes-table').DataTable().destroy();
                return draw_data(start_date, end_date);
            }
            alert("Date range is Required");
        });
        $('[data-toggle="datepicker"]')
            .datepicker({
                format: "{{ config('core.user_date_format') }}"
            })
            .datepicker('setDate', new Date());
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function draw_data(customer_id = '', lpo_number = '', project_id = '') {
        const tableLang = { @lang('datatable.strings') };
        const table = $('#quotes-table').dataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLang,
            ajax: {
                url: '{{ route("biller.quotes.get_univoiced_quote") }}',
                type: 'post',
                data: {
                    customer_id: customer_id,
                    lpo_number: lpo_number,
                    project_id: project_id,
                    pi_page: location.href.includes('page=pi') ? 1 : 0
                },
            },
            columns: [
                {
                    data: 'mass_select',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'verified_total',
                    name: 'verified_total'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'lpo_number',
                    name: 'lpo_number'
                },
                {
                    data: 'project_number',
                    name: 'project_number'
                },
                {
                    data: 'lead_tid',
                    name: 'lead_tid'
                }
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [{
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

    $('#customer_id, #lpo_number, #project_id').change(function() {
        var customer_id = $('#customer_id').val()
        var lpo_number = $('#lpo_number').val()
        var project_id = $('#project_id').val()
        $('#customer').val(customer_id)
        // console.log(project_id);
        // console.log(lpo_number);
        $('#quotes-table').DataTable().destroy();
        return draw_data(customer_id, lpo_number, project_id);
    });

    $(document).on('click', '#select-all-row', function(e) {
        if (this.checked) {
            $(this)
                .closest('table')
                .find('tbody')
                .find('input.row-select')
                .each(function() {
                    if (!this.checked) {
                        $(this)
                            .prop('checked', true)
                            .change();
                    }
                });
        } else {
            $(this)
                .closest('table')
                .find('tbody')
                .find('input.row-select')
                .each(function() {
                    if (this.checked) {
                        $(this)
                            .prop('checked', false)
                            .change();
                    }
                });
        }
    });

    //add selected
    $(document).on('click', '#add-selected', function(e) {
        e.preventDefault();
        var customer_id = $('#customer_id').val();
        if (customer_id == '') {
            swal('Filter Customer');
        } else {
            //select 
            var selected_rows = getSelectedRows();
            //console.log(selected_rows);
            if (selected_rows.length > 0) {
                $('input#selected_products').val(selected_rows);
                // console.log(selected_rows);
                swal({
                        title: 'Are You  Sure?',
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                        showCancelButton: true,
                    }, function() {
                        $('form#mass_add_form').submit();
                    }
                );
            } else {
                $('input#selected_products').val('');
                swal('No record Selected');
            }
        }
    });

    //get selecte items
    function getSelectedRows() {
        var selected_rows = [];
        var i = 0;
        $('.row-select:checked').each(function() {
            selected_rows[i++] = $(this).val();
        });
        return selected_rows;
    }
</script>
@endsection