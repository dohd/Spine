@extends ('core.layouts.app')

@section ('title', 'Price List Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Price List Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.pricelists.partials.pricelists-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => array('biller.pricelists.destroy', 0), 'method' => 'DELETE']) }}
                            <div class="row">
                                <div class="col-4">
                                    <label for="client">Customer</label>                             
                                    <select name="customer_id" id="customer" class="custom-select" data-placeholder="Choose Customer" required>
                                        <option value="">-- select customer --</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="client">Contract</label>                             
                                    <select name="contract" id="contract" class="custom-select" disabled>
                                        <option value="">-- select contract --</option>
                                        @foreach ($contracts as $row)
                                            <option value="{{ $row->contract }}" customer_id={{ $row->customer_id }}>
                                                {{ $row->contract }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="edit-form-btn">
                                    <label for="">&nbsp;</label>
                                    {{ Form::submit('Mass Delete', ['class' => 'form-control btn-danger mass-delete']) }}
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
                            <table id="listTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Contract</th>
                                        <th>Row No.</th>
                                        <th>Product Description</th>
                                        <th>UoM</th>
                                        <th>Rate</th>
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
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}}
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            this.drawDataTable();
            $('.mass-delete').click(this.massDelete);
            $('#customer').change(this.customerChange);
        },

        massDelete() {
            event.preventDefault();
            if (!$('#customer').val()) return alert('customer is required!');
            const form = $(this).parents('form');
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => form.submit());
        },

        customerChange() {
            const customerId = $(this).val();
            if (customerId) {
                $('#contract').attr('disabled', false).val('');
                $('#contract option:not(:first)').each(function() {
                    if ($(this).attr('customer_id') == customerId) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                })
            } else {
                $('#contract').attr('disabled', true).val('');
            }
        },

        drawDataTable() {
            $('#listTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.pricelists.get") }}',
                    type: 'post',
                },
                columns: [
                    {
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'contract',
                        name: 'contract'
                    },
                    {
                        data: 'row',
                        name: 'row'
                    },
                    {
                        data: 'descr',
                        name: 'descr'
                    },
                    {
                        data: 'uom',
                        name: 'uom'
                    },
                    {
                        data: 'rate',
                        name: 'rate'
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
                buttons: ['csv', 'excel', 'pdf']
            });
        },
    };

    $(() => Index.init());
</script>
@endsection