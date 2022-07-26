@extends('core.layouts.app')

@section('title', 'Contract Service Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Contract Service Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::model(['route' => ['biller.contractservices.update', $contractservice], 'method' => 'PATCH']) }}
                                @include('focus.contractservices.formA')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>  
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // customer select2
    $('#customer').select2({
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
        allowClear: true
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

    // on delete row
    $('#equipTbl').on('click', '.del', function() {
        $(this).parents('tr').remove();
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
                        if (!$('#customer').val()) return;
                        return response(data.map(v => ({
                            label: `Eq-${v.tid} - ${[v.make_type, v.capacity, v.location].join('; ')}`,
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
                console.log(data, i)
                $('#equipmentid-'+i).val(data.id);
                $('#tid-'+i).text(data.tid);
                $('#location-'+i).text(data.location);
                let rate = parseFloat(data.service_rate);
                $('#rate-'+i).text(rate.toLocaleString());
            }
        };
    }    
</script>
@endsection