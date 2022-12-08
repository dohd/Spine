@extends ('core.layouts.app')

@section ('title', 'Create Project Invoice')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create Project Invoice</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => 'biller.invoices.filter_invoice_quotes', 'method' => 'GET', 'id' => 'mass_add_form']) }}
                            <div class="row">                            
                                <div class="col-2">
                                    <div class="form-group pl-3" style="padding-top: .5em">
                                        {{ Form::hidden('selected_products', null, ['id' => 'selected_products']) }}
                                        {{ Form::hidden('customer', null, ['id' => 'customer']) }}
                                        {{ Form::submit('Add Selected', ['class' => 'btn btn-xs btn-success update_product_location mt-2', 'id' => 'add-selected']) }}
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label><strong>Customer :</strong></label>
                                        <select name="customer_id" id="customer_id" class="form-control" data-placeholder="Choose Customer" required>
                                            @foreach ($customers as $row)
                                                <option value="{{ $row->id }}">{{ $row->company }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label><strong>LPO :</strong></label>
                                        <select name="lpo_id" id="lpo_number" class="form-control" data-placeholder="Choose Client LPO" required>
                                            @foreach ($lpos as $row)
                                                <option value="{{ $row->id }}" customer_id="{{ $row->customer_id }}">{{ $row->lpo_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label><strong>Project :</strong></label>
                                        <select name="project_id" id="project_id" class="form-control" data-placeholder="Choose Project" required>
                                            @foreach ($projects as $row)
                                                <option value="{{ $row->id }}" customer_id="{{ $row->customer_id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-row"></th>
                                        <th>{{ trans('customers.customer') }}</th>
                                        <th># {{ trans('quotes.quote') }} / PI</th>
                                        <th>Title</th>
                                        <th>{{ trans('general.amount') }} (Ksh.)</th>
                                        <th>Verified (Ksh.)</th>
                                        <th>Difference (Ksh.)</th>
                                        <th>Quote / PI Date</th>
                                        <th>LPO No</th>
                                        <th>Project No</th>
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
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    config = {
        select: {
            allowClear: true,
        }
    };

    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('#customer_id').select2(config.select).val('').change();
    $('#lpo_number').select2(config.select).val('').change();
    $('#project_id').select2(config.select).val('').change();

    $('#search').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        if (start_date && end_date) {
            $('#quotesTbl').DataTable().destroy();
            return draw_data(start_date, end_date);
        }
        alert("Date range is Required");
    });

    // On selecting filter dropdown
    $('#customer_id, #lpo_number, #project_id').change(function() {
        var customer_id = $('#customer_id').val();
        var lpo_number = $('#lpo_number').val();
        var project_id = $('#project_id').val();

        if ($(this).is('#customer_id')) {
            $('#lpo_number option').each(function() {
                if ($(this).attr('customer_id') == customer_id) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
            $('#project_id option').each(function() {
                if ($(this).attr('customer_id') == customer_id) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
        }

        $('#customer').val(customer_id);
        $('#quotesTbl').DataTable().destroy();
        return draw_data(customer_id, lpo_number, project_id);
    });

    $(document).on('click', '#select-all-row', function() {
        const $input = $(this).closest('table').find('tbody').find('input.row-select');
        if (this.checked) {
            $input.each(function() {
                if (!this.checked) $(this).prop('checked', true).change();
            });
        } else {
            $input.each(function() {
                if (this.checked) $(this).prop('checked', false).change();                
            });
        }      
    });

    //get selected items
    function getSelectedRows() {
        const rows = [];
        $('.row-select:checked').each(function() {
            rows.push($(this).val());
        });
        return rows;
    }

    //add selected rows
    $(document).on('click', '#add-selected', function(e) {
        e.preventDefault();
        if (!$('#customer_id').val()) return swal('Filter Customer');
        const selected_rows = getSelectedRows();
        if (!selected_rows.length) {
            $('#selected_products').val('');
            return swal('No records Selected');
        }
        $('input#selected_products').val(selected_rows);
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => $('form#mass_add_form').submit()); 
    });

    function draw_data(customer_id = '', lpo_number = '', project_id = '') {
        const table = $('#quotesTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.invoices.get_uninvoiced_quote') }}",
                type: 'POST',
                data: { customer_id, lpo_number, project_id },
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
                    data: 'diff_total',
                    name: 'diff_total'
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
                    data: 'project_tid',
                    name: 'project_tid'
                },
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print',]
        });
    }
</script>
@endsection