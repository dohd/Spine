{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
<script>
    const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {autoHide: true, format: '{{config('core.user_date_format')}}'},
    };
    // ajax header set up
    $.ajaxSetup(config.ajax);

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

    // on document load
    $(() => {
        // on show tab
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('project_tab', $(e.target).attr('href'));
            switch ($(e.target).attr('href')) {
                case '#tab_data3': tasks(); break;
                case '#tab_data4': project_log(); break;
                case '#tab_data6': notes(); break;
                case '#tab_data10': invoices(); break;
                case '#tab_data7': quotes(); break;
                case '#tab_data8': budgets(); break;
                case '#tab_data9': 
                    skillset();
                    service();
                    stocks();
                    expense();
                    purchase(); break;    
            }
        });
        const projectTab = localStorage.project_tab;
        if (projectTab) $('a[href="' + projectTab + '"]').tab('show');

        // project progress slider
        $('#prog').text($('#progress').val() + '%');
        $(document).on('change', '#progress', function (e) {
            e.preventDefault();
            $('#prog').text($('#progress').val() + '%');
            $.ajax({
                url: "{{ route('biller.projects.update_status') }}",
                type: 'POST',
                data: {
                    project_id: "{{ $project->id }}", 
                    r_type: '1', 
                    progress: $('#progress').val(),
                },
                success: function(data) {
                    ['description', 'employee', 'assign', 'priority'].forEach(v => $('#'+v).html(data[v]));
                    $('#task_title').html(data.name);
                }
            });
        });
        
        // file attachment upload 
        $('#fileupload').fileupload({
            url: @json(route('biller.project_attachment')),
            dataType: 'json',
            formData: {_token: "{{ csrf_token() }}", project_id: '{{$project['id']}}', 'bill': 11},
            done: function (e, data) {
                $.each(data.result, function (index, file) {
                    const del_url = @json(route('biller.project_attachment', '?op=delete&meta_id='));
                    const view_url = @json(asset('storage/app/public/files'));
                    const row = `
                    <tr>
                        <td width="5%">
                            <a href="${del_url}${file.id}" class="file-del red">
                                <i class="btn-sm fa fa-trash"></i>
                            </a> 
                        </td>
                        <td>
                            <a href="${view_url}/${file.name}" target="_blank" class="purple">
                                <i class="btn-sm fa fa-eye"></i> ${file.name}
                            </a>
                        </td>
                    </tr>`;

                    $('#files').append(row);
                });
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css('width', progress + '%');
            }
        })
        .prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

        // on delete file attachment
        $(document).on('click', ".file-del", function (e) {
            e.preventDefault();
            const obj = $(this);
            $.ajax({
                url: $(this).attr('href'),
                type: 'POST',
                success: (data)  => obj.parents('tr').remove(),
            });
        });   
    });

    // milestone show modal
    $('#AddMileStoneModal').on('shown.bs.modal', function() {
        // fetch project budget amount
        $.get("{{ route('biller.projects.budget_limit', $project) }}", ({data}) => {
            const milestoneBudget = accounting.formatNumber(data.milestone_budget);
            $('.milestone-limit').text(milestoneBudget);
        });

        $('[data-toggle="datepicker"]').datepicker(config.date);
        $('.from_date').datepicker(config.date).datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
        $('.to_date').datepicker(config.date).datepicker('setDate', 'today');
        $('#color').colorpicker();        

        $('#milestone-amount').change(function() {
            const milestoneBudget = accounting.unformat($('.milestone-limit').text());
            if (this.value > milestoneBudget) this.value = milestoneBudget;
            this.value = accounting.formatNumber(this.value);
        });
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
    $("#submit-data_mile_stone").on("click", function (e) {
        e.preventDefault();
        const form_data = {};
        form_data['form'] = $("#data_form_mile_stone").serialize();
        form_data['url'] = $('#action-url').val();
        addObject(form_data, true);
        $('#AddMileStoneModal').modal('toggle');

    //     var num = $('#estimated-milestone').val();
    //     if (num == '') {
    //         swal({
    //                 title: 'Enter Estimated Milestone Amount?',
    //                 icon: "warning",
    //                 buttons: true,
    //                 dangerMode: true,
    //                 showCancelButton: true,
    //             }, () =>{ return;});
            
    //     }
    //    else{
    
    //     const form_data = {};
    //         form_data['form_name'] = 'data_form_quote';
    //         form_data['form'] = $("#data_form_mile_stone").serialize();
    //         form_data['url'] = $('#action-url').val();
    //         $('#AddMileStoneModal').modal('toggle');
    //         addObject(form_data, true);
    //    }
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
    
    // Fetch quotes
    function quotes() {
        if ($('#quotesTbl tbody tr').length) return;        
        let quoteIds = @json(@$project->quotes->pluck('id')->toArray());
        
        $('#quotesTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.quotes.get') }}",
                type: 'POST',
                data: {project_id: @json(@$project->id), quote_ids: quoteIds.join(',')},
                dataSrc: ({data}) => {
                    data = data.map(v => {
                        if (v.budget_status.includes('budgeted')) {
                            v.actions = '';
                            return v;
                        }
                        const create_budget_url = @json(route('biller.budgets.create', 'quote_id=')) + v.id;
                        const detach_quote_url = @json(route('biller.projects.detach_quote', ['project_id' => $project->id])) + '&quote_id=' + v.id;
                        v.actions = `
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item create" href="${create_budget_url}"><i class="fa fa-plus-square-o" aria-hidden="true"></i> Budget</a>
                                        <a class="dropdown-item qt-detach text-danger" href="${detach_quote_url}"><i class="fa fa-trash text-danger" aria-hidden="true"></i> Detach</a>
                                    </div>
                                </div> 
                        `;
                        return v;
                    });

                    return data;
                }
            },
            columns: [
                {data: 'DT_Row_Index',name: 'id'},
                ...['tid', 'customer', 'notes', 'total', 'invoice_tid', 'budget_status']
                .map(v => ({data: v, name: v})),
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
    // detach quote
    $(document).on('click', ".qt-detach", function(e) {
        e.preventDefault();
        addObject({form: '', url: $(this).attr('href')}, true);
        $(this).parents('tr').remove();
    });    

    // Fetch budget
    function budgets() {
        if ($('#budgetsTbl tbody tr').length) return;        
        $('#budgetsTbl').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.budgets.get') }}",
                type: 'POST',
                data: {project_id: "{{ $project->id }}"},
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['tid', 'customer', 'quote_total', 'budget_total'].map(v => ({data: v, name: v})),
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
    // detach budget
    $(document).on('click', ".budget_delete", function (e) {
        var pro_id = e.target.getAttribute('data-pro');
        var budget_id = e.target.getAttribute('data-id');
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
        // e.preventDefault();
        // addObject({form: '', url: $(this).attr('href')}, true);
        // $(this).closest('tr').remove();
    });
    // budget view
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
    @include('focus.projects.adt.new_task_js');
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
                {data: 'DT_Row_Index', name: 'id'},
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