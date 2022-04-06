@extends ('core.layouts.app')

@section ('title', trans('labels.backend.customers.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title mb-0">{{ trans('labels.backend.customers.management') }}</h4>
            <ul class="list-inline mt-1">
                <li><a href="{{route('biller.customers.index')}}?due_filter=true" class="btn btn-purple btn-sm rounded"> <span class="fa fa-money"></span>
                        {{ trans('customers.due_clients') }}</a></li>
                <li><a href="#sendMail" data-toggle="modal" data-remote="false" class="btn btn-info btn-sm rounded" data-lang="{{ trans('customers.email_selected') }}"> <span class="fa fa-envelope"></span>
                        {{ trans('customers.email_selected') }}</a></li>
                <li><a href="#sendSmsS" data-toggle="modal" data-remote="false" class="btn btn-success btn-sm rounded" data-lang="{{ trans('customers.sms_selected') }}"> <span class="fa fa-mobile"></span>
                        {{ trans('customers.sms_selected') }}</a></li>
                @if (access()->allow('delete-customer'))
                <li><a href="#deleteSelected" data-toggle="modal" data-remote="false" class="btn btn-danger btn-sm rounded" data-lang="{{ trans('customers.delete_selected') }}"> <span class="fa fa-trash-o"></span>
                        {{ trans('customers.delete_selected') }}</a>
                </li>
                @endif
            </ul>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.customers.partials.customers-header-buttons')
                </div>
            </div>
        </div>
    </div>

    @if(@$segment->group_data)
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-2">
                        <p> {{trans('customergroups.title')}}</p>
                    </div>
                    <div class="col-sm-6">
                        <p>
                            <a href="{{route('biller.customergroups.show',[$segment->group_data['id']])}}">
                                {{$segment->group_data['title']}}
                            </a>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <p>{{trans('customergroups.summary')}}</p>
                    </div>
                    <div class="col-sm-6">
                        <p>{{$segment->group_data['summary']}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <p>{{trans('customergroups.members')}}</p>
                    </div>
                    <div class="col-sm-6">
                        <p>{{numberFormat($segment->group_data->count('id'))}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <p>{{trans('customergroups.disc_rate')}}</p>
                    </div>
                    <div class="col-sm-6">
                        <p>{{numberFormat(@$segment->group_data->disc_rate)}} %</p>
                    </div>
                </div>
                <a href="#sendEmailGroup" data-toggle="modal" class="btn btn-primary btn-sm my-1">
                    <i class="fa fa-paper-plane-o"></i> {{trans('customergroups.group_message')}}
                </a>
            </div>
        </div>
    @endif

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="customers-table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('customers.picture') }}</th>
                                <th class="all">{{ trans('customers.name') }}</th>
                                <th>{{ trans('customers.company') }}</th>
                                <th>{{ trans('customers.email') }}</th>
                                <th>{{ trans('customers.address') }}</th>
                                <th>{{ trans('customers.gid') }}</th>
                                <th>{{ trans('general.active') }}</th>
                                <th class="all">{{ trans('labels.general.actions') }}</th>
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

@if(@$segment->group_data)
@php
$customergroup= $segment->group_data;
@endphp
@include("focus.modal.group_email_model")
@endif
@include("focus.customers.modal.selected_email")
@include("focus.customers.modal.selected_sms")
@include("focus.customers.modal.selected_delete")
@endsection


@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', ".customer_active", function() {
        $(this).addClass('checked');
        $(this).attr('data-active', 1);

        var active = $(this).attr('data-active');
        if (active == 1) {
            $(this).removeClass('checked');
            $(this).attr('data-active', 0);
        }

        var cid = $(this).attr('data-cid');
        $.ajax({
            url: '{{ route("biller.customers.active") }}',
            type: 'post',
            data: {
                'cid': cid,
                'active': active
            }
        });
    });

    $('.summernote').summernote({
        height: 150,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['fullscreen', ['fullscreen']],
            ['codeview', ['codeview']]
        ],
        popover: {}
    });

    $(document).on('click', "#confirmDeleteSelected", function(e) {
        selectedAction('sendmail_form', 'sendMail', 'delete');
        $('#customers-table').DataTable();
    });

    //uni sender
    $('#sendMail').on('click', '#sendNowSelected', function(e) {
        selectedAction('sendmail_form', 'sendMail', 'mail');
    });
    $('#sendSmsS').on('click', '#sendSmsSelected', function(e) {
        selectedAction('sendsms_form', 'sendSmsS', 'sms');
    });

    function draw_data() {
        const dueFilter = @json(@$input['due_filter']);
        const relId = @json(@$input['rel_id']);
        const customerGroup = @json(@$segment['customer_group_id ']);
        const relType = @json(@$input['rel_type ']);

        const tableLan = {
            @lang('datatable.strings')
        };
        var dataTable = $('#customers-table').dataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.customers.get") }}',
                type: 'post',
                data: {
                    due_filter: dueFilter ? true : '',
                    g_rel_id: relId ? customerGroup : '',
                    g_rel_type: relId ? relType : ''
                },
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'image',
                    name: 'image'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'group',
                    name: 'group'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, 'desc']
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
            },
            drawCallback: function(settings) {
                let api = this.api();
                var info = api.page.info();
                const rowCount = api.rows({
                    page: 'current'
                }).count();
                if (info.pages !== 0 && api.page() > 0 && rowCount === 0) {
                    api.page('first').state.save();
                    window.location.reload();
                }
            }
        });
    }


    function selectedAction(form_name, modal_name, r_type) {
        $("#" + modal_name).modal('hide');
        if ($("#notify").length == 0) {
            $("#c_body").html('<div id="notify" class="alert" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a><div class="message"></div></div>');
        }

        $.ajax({
            url: "{{route('biller.customers.selected_action')}}",
            type: 'POST',
            data: $("input[name='cust[]']:checked").serialize() + '&' + $("#" + form_name).serialize() + '&r_type=' + r_type,
            dataType: 'json',
            success: function(data) {
                $("#notify .message").html("<strong>" + data.status + "</strong>: " + data.message);
                $("#notify").removeClass("alert-danger").addClass("alert-success").fadeIn();
                $("html, body").animate({
                    scrollTop: $('#notify').offset().top
                }, 1000);
            }
        });
    }
</script>
@endsection