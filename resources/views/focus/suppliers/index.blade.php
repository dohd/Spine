@extends ('core.layouts.app')

@section ('title', trans('labels.backend.suppliers.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Supplier Management</h4>
        </div>
    </div>
    
    <div class="content-detached content-right">
        <div class="content-body">
            <div class="content-overlay"></div>
            <section class="row all-contacts">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <!-- Task List table -->
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
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                @php
                                                    $labels = [
                                                        'Supplier No', 'Name', 'Phone', 'Email', 'Address', 'City', 'Region', 'Country',
                                                        'PostBox', 'Tax ID', 'Bank', 'Account No', 'Account Name', 'Bank Code', 'Mpesa Account'
                                                    ];
                                                @endphp
                                                <tbody> 
                                                    @foreach ($labels as $val)
                                                        <tr>
                                                            <th>{{ $val }}</th>
                                                            <td>{{ '' }}</td>
                                                        </tr>
                                                    @endforeach                        
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                            <table id="" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Description</th>
                                                        <th>Amount</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
                                            <div class="row">
                                                <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                    <p>{{trans('customers.docid')}}</p>
                                                </div>
                                            </div>
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
                    <div class="card-body">
                        <table id="supplierTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
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
    
    $(document).on('click', ".customer_active", function() {
        var cid = $(this).attr('data-cid');
        var active = $(this).attr('data-active');
        $(this).addClass('checked');
        $(this).attr('data-active', 1);

        if (active == 1) {
            $(this).removeClass('checked');
            $(this).attr('data-active', 0);
        } 

        $.ajax({
            url: '{{ route("biller.suppliers.active") }}',
            type: 'post',
            data: {'cid': cid, 'active': active}
        });
    });    

    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        const dataTable = $('#supplierTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.suppliers.get") }}',
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