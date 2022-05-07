@extends ('core.layouts.app')

@section ('title', 'Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Customer Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.customers.partials.customers-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-detached content-right">
        <div class="content-body">
            <section class="row all-contacts">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="btn-group float-right">
                                    <a href="{{ route('biller.customers.edit', $customer) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                        <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                    </a>&nbsp;
                                    <button type="button" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delCustomer">
                                        {{Form::open(['route' => ['biller.customers.destroy', $customer], 'method' => 'DELETE'])}}{{Form::close()}}
                                        <i class="fa fa-trash"></i> {{trans('buttons.general.crud.delete')}}
                                    </button>
                                </div>
                                
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Customer Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Transactions</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Invoices</a>
                                        </li> 
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Aging</a>
                                        </li>                                       
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <!-- Customer Info -->
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                @php
                                                    $labels = [
                                                        'Name', 'Phone', 'Email', 'Address', 'Company', 'City', 'Region', 'Country', 'PostBox',
                                                        'Tax ID' => 'taxid',  
                                                    ];
                                                @endphp
                                                <tbody> 
                                                    @foreach ($labels as $key => $val)
                                                        <tr>
                                                            <th>{{ is_numeric($key) ? $val : $key }}</th>
                                                            <td>{{ $customer[strtolower($val)] }}</td>
                                                        </tr>
                                                    @endforeach      
                                                </tbody>
                                            </table>
                                        </div>
                                                    
                                        <!-- Transactions -->
                                        <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                            
                                                        @foreach (['Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Balance'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $bal = count($transactions) ? $transactions[0]['debit'] : 0;
                                                    @endphp
                                                    @foreach ($transactions as $i => $tr)
                                                        @php
                                                            if ($i && $tr->debit > 0) $bal += $tr->debit;
                                                            elseif ($i && $tr->credit > 0) $bal -= $tr->credit;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ dateFormat($tr->tr_date) }}</td>
                                                            <td>{{ $tr->tr_type }}</td>
                                                            <td>{{ $tr->note }}</td>                                                           
                                                            <td>{{ numberFormat($tr->debit) }}</td>
                                                            <td>{{ numberFormat($tr->credit) }}</td>
                                                            <td>{{ numberFormat($bal) }}</td>
                                                        </tr>                                                        
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Invoices -->
                                        <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                                    
                                                        @foreach (['Date', 'Status', 'Note', 'Amount', 'Paid'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($invoices as $invoice)
                                                        <tr>
                                                            <td>{{ dateFormat($invoice->invoicedate) }}</td>
                                                            <td>{{ $invoice->status }}</td>
                                                            <td>{{ $invoice->notes }}</td>                                                      
                                                            <td>{{ numberFormat($invoice->total) }}</td>
                                                            <td>{{ numberFormat($invoice->amountpaid) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>                                               
                                            </table>                                            
                                        </div>

                                        <!-- Aging -->
                                        <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <div class="sidebar-detached sidebar-left">
        <div class="sidebar">
            <div class="bug-list-sidebar-content">
                <div class="card">
                    <div class="card-head">
                        <div class="media-body media p-1">
                            <div class="media-middle pr-1">
                                <span class="avatar avatar-lg rounded-circle ml-2">
                                    <img src="{{ Storage::disk('public')->url('app/public/img/customer/' . $customer->picture) }}" alt="avatar">
                                </span>
                            </div>
                        </div>
                        <div class="ml-1">
                            <h5 class="info">Customer</h5>
                            <h5 class="media-heading">{{ $customer->name }}</h5>
                            <h5>Balance: <span class="text-danger">{{ numberFormat($bal) }}</span></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="customerTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('customers.name') }}</th>
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    // delete customer
    $('#delCustomer').click(function() {
        $(this).children('form').submit();
    });
    
    function draw_data() {
        const language = {@lang('datatable.strings')};
        const dataTable = $('#customerTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: '{{ route("biller.customers.get") }}',
                type: 'post'
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
            ],
            order: [
                [0, "asc"]
            ],
            searchDelay: 500,
            dom: 'frt',
        });
    }
</script>
@endsection