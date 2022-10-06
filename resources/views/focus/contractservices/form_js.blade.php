<script>  
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // customer select2
    $('#customer').select2({
        allowClear: true,
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({search: term}),
            processResults: result => {
                return { results: result.map(v => ({text: `${v.company}`, id: v.id }))};
            }      
        },
    }).change(function() {
        $("#branch").html('').select2({
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id: $(this).val()}),                                
                processResults: data => {
                    data = data.filter(v => v.name != 'All Branches');
                    return { results: data.map(v => ({ text: v.name, id: v.id })) };
                },
            }
        });
        $("#contract").html('').select2({
            ajax: {
                url: "{{ route('biller.contracts.customer_contracts')  }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id: $(this).val()}),                                
                processResults: data => {
                    return { results: data.map(v => ({ text: v.title, id: v.id })) };
                },
            }
        });
        
    });

    // on contract change
    $('#contract').change(function() {
        $("#schedule").html('').select2({
            ajax: {
                url: "{{ route('biller.contracts.task_schedules')  }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({search: term, contract_id: $(this).val()}),                                
                processResults: data => {
                    return { results: data.map(v => ({ text: v.title, id: v.id })) };
                },
            }
        });
    });

    // on add equipment
    let rowIndx = 1;
    const rowHtml = $('#equipTbl tbody tr:eq(0)').html();
    $('#descr-0').autocomplete(completeEquip());
    $('#add_equip').click(function() {
        const i = rowIndx;
        let html = rowHtml.replace(/-0/g, '-'+i);
        $('#equipTbl tbody').append('<tr>' + html + '</tr>');
        $('#descr-'+i).autocomplete(completeEquip(i));
        rowIndx++;
    });

    //  on change bill
    $('#equipTbl').on('change', '.bill', function() {
        calcTotal();
    });    

    // on delete row
    $('#equipTbl').on('click', '.del', function() {
        $(this).parents('tr').remove();
        calcTotal();
    });
    
    // autocomplete equipment properties
    function completeEquip(i = 0) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    method: 'POST',
                    data: {
                        keyword: request.term, 
                        client_id: $('#customer').val(),
                        branch_id: $('#branch').val()
                    },
                    success: data => {
                        return response(data.map(v => ({
                            label: `Eq-${(''+v.tid).length < 4 ? ('000'+v.tid).slice(-4) : v.tid} - 
                                ${[v.make_type, v.capacity, v.location].join('; ')}`,
                            value: `${[v.make_type, v.capacity].join('; ')}`,
                            data: v
                        })))
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#equipmentid-'+i).val(data.id);
                $('#location-'+i).text(data.location);

                let tid = (''+data.tid).length < 4 ? ('000'+data.tid).slice(-4) : data.tid;
                $('#tid-'+i).text('Eq-' + tid);
                
                $('#rate-'+i).text(accounting.formatNumber(data.service_rate));
                calcTotal();
            }
        };
    }    

    // compute totals
    function calcTotal() {
        let rateTotal = 0;
        let billTotal = 0;
        $('#equipTbl tbody tr').each(function() {
            let isBill = $(this).find('.bill').val(); 
            let rate = accounting.unformat($(this).find('.rate').text());
            if (isBill == 1) billTotal += rate;
            rateTotal += rate;
        });
        $('#rate_ttl').val(accounting.formatNumber(rateTotal));
        $('#bill_ttl').val(accounting.formatNumber(billTotal));
    }
</script>