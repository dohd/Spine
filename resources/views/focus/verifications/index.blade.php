@extends ('core.layouts.app')

@section('title', 'Verification Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Partial Job Verification</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.verifications.partials.verification-header-buttons')
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
                            <table id="verificationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Quote / PI</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>LPO No.</th>
                                        <th>Project No.</th>
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#verificationsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.verifications.get') }}",
                    type: 'POST',
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['tid', 'customer', 'total', 'lpo_no', 'project_no'].map(v => ({data:v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
