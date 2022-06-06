@extends ('core.layouts.app')

@section ('title', 'Edit | Contract Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Contract Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contracts.partials.contracts-header-buttons')
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
                            {{ Form::model($contract, ['route' => ['biller.contracts.update', $contract], 'method' => 'PATCH']) }}
                                @include('focus.contracts.form')
                            {{ Form::close() }}
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
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    // form submit
    $('form').submit(function(e) {
        const schedules = $('#scheduleTbl tbody tr').length;
        const equipments = $('#equipmentTbl tbody tr').length;
        if (schedules < 1 || equipments < 1) {
            e.preventDefault();
            alert('Include at least one schedule task or equipment!');
        }
    });

    function renderDatepicker() {
        return $('.datepicker')
            .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});    
    }
    renderDatepicker();

    // customer select config
    $("#customer").select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({ search: term }),
            processResults: data => {
                return {
                    results: data.map(v => ({ 
                        id: v.id, 
                        text: `${v.name} - ${v.company}`,
                    }))
                };
            },
        }
    });        

    const contract = @json($contract);

    $('#start_date').datepicker('setDate', new Date(contract.start_date));
    $('#end_date').datepicker('setDate', new Date(contract.end_date));
    
    // add schedule row
    const scheduleRow = $('#scheduleTbl tbody tr:first').html();
    $('#scheduleTbl tbody tr:first').remove();
    let rowId = $('#addSchedule tbody').length;
    $('#addSchedule').click(function() {
        rowId++;
        let html = scheduleRow.replace(/-0/g, '-'+rowId);
        $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
        renderDatepicker();
        $('#startdate-'+ rowId).datepicker('setDate', new Date());
        $('#enddate-'+ rowId).datepicker('setDate', new Date());
    });
    // remove schedule row
    $('#scheduleTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
    });

    // remove equipmentTbl row
    $('#equipmentTbl tbody tr:first').remove();
    $('#equipmentTbl').on('click', '.remove', function() {
        const row = $(this).parents('tr');
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => row.remove());          
    });
</script>
@endsection