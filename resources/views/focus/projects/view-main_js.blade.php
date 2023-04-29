{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
<script>
    // ajax header set up
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
    });

    // milestone show modal
    $('#AddMileStoneModal').on('shown.bs.modal', function () {
        var project_id = @json($project->id);
        $.ajax({
            methode: "GET",
            url: "{{ route('biller.projects.get_extimated_milestone') }}",
            data: {
                project_id : project_id,
            },
            success: function (response) {
                //console.log(response);
                if(response == -1){
                    $('.extimate').text('No Limit');
                }
                else{
                    $('.extimate').text(response);
                }
                
            }
        });
        $('[data-toggle="datepicker"]').datepicker({autoHide: true, format: '{{config('core.user_date_format')}}'});
        $('.from_date').datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
        $('.from_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
        $('.to_date').datepicker('setDate', 'today');
        $('.to_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
        $('#color').colorpicker();
       
        
    });
    $('#extimated-milestone').on('key up change', function () {
        var extimate = accounting.unformat($('.extimate').text());
        var extimated_milestone_amount = accounting.unformat($(this).val());
        if(extimate <= -1){
            $('#extimated-milestone').val();
            
        }
       else if (extimate == 0) {
            swal({
                    title: 'Adjust Your Budget?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () =>{ 
                    $('#extimated-milestone').val('');
                    $('#AddMileStoneModal').modal('hide')
                });
                
        }
        else if(extimated_milestone_amount > extimate){
            $('#extimated-milestone').val(extimate).change();
            return;
        }
    });

    // quote show modal
    $('#AddQuoteModal').on('shown.bs.modal', function () {
        $('.from_date').val(@json(dateFormat()));
        $('.to_date').val(@json(dateFormat()));
        $("#quote").select2({
            allowClear: true,
            dropdownParent: $('#AddQuoteModal'),
            ajax: {
                url: "{{ route('biller.projects.quotes_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: @json(@$project->customer_id) }),
                processResults: (data) => {
                    return {
                        results: $.map(data, (item) => ({
                            text: `${item.name}`,
                            id: item.id
                        }))
                    };
                },
            }
        });
    });
    

    // milestone submit
    $("#submit-data_mile_stone").on("click", function () {
        event.preventDefault();
        var num = $('#extimated-milestone').val();
        if (num == '') {
            swal({
                    title: 'Enter Extimated Milestone Amount?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () =>{ return;});
            
        }
       else{
    
        const form_data = {};
            form_data['form_name'] = 'data_form_quote';
            form_data['form'] = $("#data_form_mile_stone").serialize();
            form_data['url'] = $('#action-url').val();
            $('#AddMileStoneModal').modal('toggle');
            addObject(form_data, true);
       }
    });

    // log submit
    $("#submit-data_log").on("click", function (e) {
        e.preventDefault();
        var form_data = {};
        form_data['form_name'] = 'data_form_log';
        form_data['form'] = $("#data_form_log").serialize();
        form_data['url'] = $('#action-url_5').val();
        addObject(form_data, true);
        $('#AddLogModal').modal('toggle');
    });

    // quote submit
    $("#submit-data_quote").on("click", function (e) {
        e.preventDefault();
        var form_data = {};
        form_data['form'] = $("#data_form_quote").serialize();
        form_data['url'] = $('#action-url_7').val();
        addObject(form_data, true);
        $('#AddQuoteModal').modal('toggle');
    });

    // note submit
    $("#submit-data_note").on("click", function() {
        event.preventDefault();
        var form_data = {};
        form_data['form_name'] = 'data_form_note';
        form_data['form'] = $("#data_form_note").serialize();
        form_data['url'] = $('#action-url_6').val();
        addObject(form_data, true);
        $('#AddNoteModal').modal('toggle');
    });

    // task submit
    @include('focus.projects.adt.new_task_js');

    $(function () {
        'use strict';

        var slider = $('#progress');
        var textn = $('#prog');
        textn.text(slider.val() + '%');
        $(document).on('change', slider, function (e) {
            e.preventDefault();
            textn.text($('#progress').val() + '%');
            $.ajax({
                url: '{{route('biller.projects.update_status')}}',
                type: 'POST',
                data: {
                    'project_id': '{{$project['id']}}',
                    'r_type': '1',
                    'progress': $('#progress').val()
                },
                success: function (data) {
                    $('#description').html(data.description);
                    $('#task_title').html(data.name);
                    $('#employee').html(data.employee);
                    $('#assign').html(data.assign);
                    $('#priority').html(data.priority);
                }
            });
        });

        // on select tab
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('project_tab', $(e.target).attr('href'));
            switch ($(e.target).attr('href')) {
                case '#tab_data3': 
                    tasks(); break;
                case '#tab_data4': 
                    project_log(); break;
                case '#tab_data6': 
                    notes(); break;
                case '#tab_data7': 
                    invoices(); break;
                case '#tab_data9': 
                    quotes(); break;
                case '#tab_data10': 
                    budgets(); break;
                case '#tab_data11': 
                    skillset();
                    service();
                    stocks();
                    expense();
                    purchase(); break;    
            }
        });

        var project_tab = localStorage.getItem('project_tab');
        if (project_tab) $('a[href="' + project_tab + '"]').tab('show');

        // Change this to the location of your server-side upload handler:
        var url = '{{route('biller.project_attachment')}}';
        $('#fileupload').fileupload({
            url: url,
            dataType: 'json',
            formData: {_token: "{{ csrf_token() }}", id: '{{$project['id']}}', 'bill': 11},
            done: function (e, data) {
                $.each(data.result, function (index, file) {
                    $('#files').append('<tr><td><a data-url="{{route('biller.project_attachment')}}?op=delete&id= ' + file.id + ' " class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> ' + file.name + ' </td></tr>');
                });
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                    'width',
                    progress + '%'
                );

            }
        }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    });

    $('.summernote').summernote({
        height: 300,
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
    $(document).on('click', ".aj_delete", function (e) {
        e.preventDefault();
        var aurl = $(this).attr('data-url');
        var obj = $(this);
        $.ajax({
            url: aurl,
            type: 'POST',
            success: function (data) {
                obj.closest('tr').remove();
                obj.remove();
            }
        });
    });

    // detach quote
    $(document).on('click', ".quote_delete", function (e) {
        e.preventDefault();
        addObject({form: '', url: $(this).attr('href')}, true);
        $(this).closest('tr').remove();
    });
    // detach budget
    $(document).on('click', ".budget_delete", function (e) {
        var pro_id = e.target.getAttribute('data-pro');
        var budget_id = e.target.getAttribute('data-id');
        //console.log(quote_id);
        var url = "{{ route('biller.projects.detach_budget') }}";
        $.ajax({
            method: "POST",
            url: url,
            data: {
                budget_id: budget_id,
            },
            
            success: function (response) {
                swal({
                    title: response.status,
                    icon: "success",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () =>{ 
                    window.location.reload();
                });
            }
        });
        // console.log(quote_id);
        // e.preventDefault();
        // addObject({form: '', url: $(this).attr('href')}, true);
        // $(this).closest('tr').remove();
    });
   

    // modal submit callback
    function trigger(data) {
        switch (data.t_type) {
            case 1:
                $('#m_' + data.meta).remove();
                break;
            case 2:
                $('.timeline').prepend(data.meta);
                break;
            case 3:
                $(data.row).prependTo("#tasks-table tbody");
                $("#data_form_task").trigger('reset');
                break;
            case 5:
                $(data.meta).prependTo("#log-table  tbody");
                $("#data_form_log").trigger('reset');
                break;
            case 6:
                $(data.meta).prependTo("#notes-table  tbody");
                $("#data_form_note").trigger('reset');
                break;
            case 7:
                $("#data_form_quote").trigger('reset');
                break;
            case 8:
                $("#data_form_budget").trigger('reset');
                break;                
        }
        return;
    }
    
    /**Fetch quotes / pi */
    function quotes() {
        if ($('#quotesTbl tbody tr').length) return;        
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        quoteIds = quoteIds.join(',');


        $('#quotesTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.quotes.get') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds},
                dataSrc: ({data}) => {
                    data = data.map(v => {
                        const url = "{{ route('biller.projects.detach_quote', ['project_id' => $project->id]) }}" + `&quote_id=${v.id}`;
                        const create_url = "{{ url('projects/budget/') }}" +`/${v.id}`;
                        //v['actions'] = `<a href="${url}" class="quote_delete"><i class="fa fa-trash fa-lg text-danger"></i></a>`;
                        v['actions'] = `
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item create" href="${create_url}">Create</a>
                                        <a class="dropdown-item quote_delete text-danger" href="${url}">Remove</a>
                                    </div>
                                </div> 
                        `;
                        return v;
                    });
                    return data;
                }
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                     'tid', 'customer', 'notes', 'total', 'lead_tid', 'invoice_tid', 'quote_budget'
                ].map(v => ({data: v, name: v})),
                {data: 'stats', name: 'stats', searchable: false, sortable: false}
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: 5 },
                { type: "custom-date-sort", targets: 1 }
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }    

    /**Fetch budget */
    function budgets() {
        if ($('#budgetsTbl tbody tr').length) return;        
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        quoteIds = quoteIds.join(',');

        $('#budgetsTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.project_budget') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds},
                dataSrc: ({data}) => {
                    data = data.map(v => {
                       
                        return v;
                    });
                    return data;
                }
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'tid', 'customer', 'quote_total', 'budget_total'
                ].map(v => ({data: v, name: v})),
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: 5 },
                { type: "custom-date-sort", targets: 1 }
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
    $('#budgetsTbl tbody').on('click', '.view',  function(e){
        var id = e.target.getAttribute('data-id');

        $.ajax({
            method: "GET",
            url: "{{ route('biller.projects.view_budget')}}",
            data: {
                id : id,
            },
            
            success: function (response) {
               if (response.id) {
                swal({
                    title: 'Quote is Not Budgeted?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () =>{ return;}); 
                //data-toggle="modal" data-target="#AddBudgetModal"
                //
                return;
               }
               $('#AddBudgetModal').modal('show');
               $('#customer').val(response.customer);
               $('#branch').val(response.branch);
               if(response.quote.approval_note)
                    $('#subject').val(response.quote.approval_note);
               $('#date').val(response.quote.date);
               $('#tid').val('QT-'+response.quote.tid);
               $('#client_ref').val(response.quote.client_ref);
               $('#quote_total').val(response.budget.quote_total)
               $('#budget-total').val(response.budget.budget_total);
               //Budget Items modal view
               $.each(response.budget_items, function(key, value) {
                var row = $('<tr>');
                var numbering = $('<td>').text(value.numbering);
                var product_name = $('<td>').text(value.product_name);
                var approved_qty = $('<td>').text(value.product_qty);
                var unit = $('<td>').text(value.unit);
                var new_qty = $('<td>').text(value.new_qty);
                var price = $('<td>').text(value.price);
                var amount = $('<td>').text(value.price * value.new_qty);
                
                row.append(numbering);
                row.append(product_name);
                row.append(approved_qty);
                row.append(unit);
                row.append(new_qty);
                row.append(price);
                row.append(amount);
                $('#AddBudgetModal').find('#budgetviewTbl tbody').append(row);
                });
                //Budget Skillset modal View
                let i = 1;
                $.each(response.skillset, function(key, value) {
                var row = $('<tr>');
                var numbering = $('<td>').text(i);
                var skill = $('<td>').text(value.skill);
                var charge = $('<td>').text(value.charge);
                var hours = $('<td>').text(value.hours);
                var no_technician = $('<td>').text(value.no_technician);
                var amount = $('<td class="total">').text(value.charge * value.hours * value.no_technician);
                
                row.append(numbering);
                row.append(skill);
                row.append(charge);
                row.append(hours);
                row.append(no_technician);
                row.append(amount);
                i++
                $('#AddBudgetModal').find('#budgetviewskillsetTbl tbody').append(row);
                });
                var total = 0;
                $('#budgetviewskillsetTbl tbody tr .total').each(function(){
                    total += parseFloat($(this).text());
                })
                $('#labour-total').val(total);
            }
        });
    });       

    /** Purchase Table Summary */
    function purchase() {
        if ($('#purchaseTbl tbody tr').length) return;        

        $('#purchaseTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.bill_stock_items') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id)},
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'bill_id', 'type', 'description', 'uom','qty'
                ].map(v => ({data: v, name: v})),
                {data: 'amount', name: 'amount', searchable: false, sortable: false}
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /** Expense Table Summary */
    function expense() {
        if ($('#expenseTbl tbody tr').length) return;        

        $('#expenseTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.project_expense') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id)},
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'bill_id', 'type', 'description', 'uom','qty'
                ].map(v => ({data: v, name: v})),
                {data: 'amount', name: 'amount', searchable: false, sortable: false}
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Issued Stock to Project */
    function stocks() {
        if ($('#stockTbl tbody tr').length) return;        
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        quoteIds = quoteIds.join(',');

        $('#stockTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.issued_items') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds},
                dataSrc: ({data}) => {
                    data = data.map(v => {
                       
                        return v;
                    });
                    return data;
                }
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'tid', 'description','uom','qty','warehouse'
                ].map(v => ({data: v, name: v})),
                {data: 'amount', name: 'amount', searchable: false, sortable: false}
            ],
            
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /** Service Items */
    function service() {
        if ($('#serviceTbl tbody tr').length) return;        
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        quoteIds = quoteIds.join(',');

        $('#serviceTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.quotes_service_items') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds},
                dataSrc: ({data}) => {
                    data = data.map(v => {
                       
                        return v;
                    });
                    return data;
                }
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'tid', 'description','uom','qty'
                ].map(v => ({data: v, name: v})),
                {data: 'amount', name: 'amount', searchable: false, sortable: false}
            ],
            
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }


    /**SkillSet Labour */
    function skillset() {
        if ($('#labourTbl tbody tr').length) return;        
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        quoteIds = quoteIds.join(',');

        $('#labourTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.projects.labour_skillsets') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds},
                dataSrc: ({data}) => {
                    data = data.map(v => {
                       
                        return v;
                    });
                    return data;
                }
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...[
                    'tid', 'skill','charge', 'hours','no_technician'
                ].map(v => ({data: v, name: v})),
                {data: 'amount', name: 'amount', searchable: false, sortable: false}
            ],
            
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Fetch Invoices */
    function invoices() {
        if ($('#invoices-table_p tbody tr').length) return;
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        quoteIds = quoteIds.join(',');
        $('#invoices-table_p').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: "{{ route('biller.projects.invoices') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds}
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'tid', name: 'tid'},
                {data: 'customer', name: 'customer'},
                {data: 'invoicedate', name: 'invoicedate'},
                {data: 'total', name: 'total'},
                {data: 'status', name: 'status'},
                {data: 'invoiceduedate', name: 'invoiceduedate'}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }    

    /**Fetch Notes */
    function notes() {
        if ($('#notes-table tbody tr').length) return;
        $('#notes-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings') },
            ajax: {
                url: '{{ route("biller.notes.get") }}',
                type: 'POST',
                data: {project_id: @json(@$project->id)},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'title', name: 'title'},
                {data: 'created_at', name: 'created_at'},
                {data: 'user', name: 'user'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Fetch Tasks */
    function tasks() {
        if ($('#tasks-table tbody tr').length) return;
        $('#tasks-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.tasks.get") }}',
                type: 'POST',
                data: {project_id: @json(@$project_id)}
            },
            columns: [
                {data: 'tags', name: 'tags'},
                {data: 'start', name: 'start'},
                {data: 'duedate', name: 'duedate'},
                {data: 'status', name: 'status'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }

    /**Fetch activity Logs*/
    function project_log() {
        if ($('#log-table tbody tr').length) return;
        $('#log-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.projects.log_history") }}',
                type: 'post',
                data: {project_id: @json(@$project->id)},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'created_at', name: 'created_at'},
                {data: 'user', name: 'user'},
                {data: 'value', name: 'value'},
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>