@extends ('core.layouts.app')

@section ('title', 'LPO Management')

@section('content')
<div class="content-wrapper">
    <div class="alert alert-warning alert-dismissible d-none lpo-alert">
        <strong>Forbidden!</strong> Cannot delete LPO attached to quote
    </div> 
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
                            <th>#LPO No</th>
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
@include('focus.lpo.modal.lpo_edit')
@include('focus.lpo.modal.lpo_new')
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // Delete LPO
    $(document).on('click', 'a.delete-lpo', function(e) {
        e.preventDefault();
        if (confirm('Are you sure to delete this item ?')) {
            $.ajax({
                url: $(this).attr('href'),
                method: 'DELETE'
            })
            .done(function() { location.reload(); })
            .fail(function(err) {
                $('.lpo-alert').removeClass('d-none');
                setTimeout(() => $('.lpo-alert').addClass('d-none'), 3000);
            })
        }
    });
    
    // Fetch update data
    $(document).on('click', 'a.update-lpo', function(e) {
        e.preventDefault();
        const id = $(this).attr('href');
        $.ajax({ 
            url: baseurl + `lpo/${id}/edit`, 
            method: 'GET'
        })
        .done(function(data) { 
            const {customer, branch, lpo} = data;
            const $form = $("#updateLpoForm");
            $form.find('#lpo_id').val(lpo.id);
            $form.find("#person").append(new Option(customer.name, customer.id, 'selected', true));
            $form.find("#branch_id").append(new Option(branch.name, branch.id, 'selected', true));
            $form.find('#date').val(lpo.date);
            $form.find('#lpo_no').val(lpo.lpo_no);
            $form.find('#amount').val(lpo.amount);
            $form.find('#remark').val(lpo.remark);
        });
    });
    
    // On modal open
    $(document).on('shown.bs.modal', '#updateLpoModal, #AddLpoModal', function() {
        const $modal = $(this);
        
        // initialize customer select2
        $modal.find("#person").select2({
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
        $modal.find("#person").on('change', function() {
            // fetch customer branches
            $modal.find("#branch_id").html('').select2({
                ajax: {
                    url: "{{ route('biller.branches.branch_load') }}?id=" + $(this).val(),
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
                    data: 'invoiced',
                    name: 'invoiced'
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