@extends('core.layouts.app', [
    'page' => 'class = "horizontal-layout horizontal-menu content-detached-left-sidebar app-contacts" data-open = "click" data-menu = "horizontal-menu" data-col = "content-detached-left-sidebar"'
])

@section('title', trans('labels.backend.suppliers.management') . ' | ' . trans('labels.backend.customers.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12">
            <h4 class="content-header-title">Supplier Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                @include('focus.suppliers.partials.suppliers-header-buttons')
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
                                <a href="{{route('biller.suppliers.edit', [$supplier->id])}}" class="btn btn-blue btn-outline-accent-5 btn-sm float-right">
                                    <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                </a>
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Supplier Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Transactions</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Purchase Orders</a>
                                        </li>   
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Aging</a>
                                        </li>                                        
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <table class="table table-bordered zero-configuration" cellspacing="0" width="100%">
                                                @php
                                                    $labels = [
                                                        'Name', 'Email', 'Address', 'City', 'Region', 'Country', 'PostBox', 'Bank',
                                                        'Supplier No' => 'supplier_no', 
                                                        'Tax ID' => 'taxid',  
                                                        'Account No' => 'account_no', 
                                                        'Account Name' => 'account_name', 
                                                        'Bank Code' =>  'bank_code',
                                                        'Mpesa Account' => 'mpesa_payment',
                                                        'Document ID' => 'docid'
                                                    ];
                                                @endphp
                                                <tbody> 
                                                    @foreach ($labels as $key => $val)
                                                        <tr>
                                                            <th>{{ is_numeric($key) ? $val : $key }}</th>
                                                            <td>{{ $supplier[strtolower($val)] }}</td>
                                                        </tr>
                                                    @endforeach                        
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                            <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                                        
                                                        @foreach (['Date', 'Type', 'Note', 'Amount', 'Paid', 'Balance'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($transactions as $k => $tr)
                                                        <tr>
                                                            <td>{{ dateFormat($tr->tr_date) }}</td>
                                                            <td>{{ $tr->tr_type }}</td>
                                                            <td>{{ $tr->note }}</td>                                                           
                                                            <td>{{ number_format($tr->credit, 2) }}</td>
                                                            <td>{{ number_format($tr->debit, 2) }}</td>
                                                            <td>
                                                                @if ($tr->tr_type == 'BILL')
                                                                    {{ number_format($tr->credit - $tr->bill->amountpaid, 2)  }}
                                                                @endif
                                                            </td>
                                                        </tr>                                                        
                                                    @endforeach
                                                </tbody>                                                
                                            </table>
                                        </div>

                                        <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
                                            <table class="table table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                                    
                                                        @foreach (['Date', 'Type', 'Note', 'Amount', 'Paid'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($bills as $bill)
                                                        <tr>
                                                            <td>{{ dateFormat($bill->date) }}</td>
                                                            <td>{{ $bill->doc_ref_type }} - {{ $bill->doc_ref }}</td>
                                                            <td>{{ $bill->note }}</td>                                                      
                                                            <td>{{ number_format($bill->grandttl, 2) }}</td>
                                                            <td>{{ number_format($bill->amountpaid, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>                        
                                        </div>
                                        <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
                                         <!-- aging tab -->
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
                                    <img src="{{ Storage::disk('public')->url('app/public/img/supplier/' . $supplier->picture) }}" alt="avatar">
                                </span>
                            </div>
                        </div>
                        <div class="media-body media-middle p-1">
                            <h5 class="media-heading">{{ $supplier->name }} </h5>
                            <h5 class="info">{{ trans('suppliers.supplier') }}</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="suppliers-table" class="table table-striped table-bordered zero-configuration small" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('customers.name') }}</th>
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

    function draw_data() {
        const tableLan = {
            @lang('datatable.strings')
        };
        var dataTable = $('#suppliers-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.suppliers.get") }}',
                type: 'post'
            },
            columns: [{
                data: 'name',
                name: 'name'
            }, ],
            order: [
                [0, "asc"]
            ],
            searchDelay: 500,
            dom: 'frt',
        });
    }
</script>
@endsection