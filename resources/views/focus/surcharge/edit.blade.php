@extends('core.layouts.app')

@section('title', 'Surcharges | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Surcharges Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    {{-- @include('focus.assetissuance.partials.assetissuance-header-buttons') --}}
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::model($surcharge, ['route' => ['biller.surcharges.update', $surcharge], 'method' => 'PATCH']) }}
                    <div class="form-group">
                        @include('focus.surcharge.form')
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
     // $('form').submit(function (e) { 
    //     e.preventDefault();
    //     console.log($(this).serializeArray());
    // });
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    // On searching supplier
    $('#employeebox').change(function() {
        const name = $('#employeebox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#employeeid').val(id);
        $('#employee').val(name);
        $('#issue_type').prop("disabled", false);
    });


    // load employees
    const employeeUrl = "{{ route('biller.surcharge.select') }}";
    function employeeData(data) {
        return {results: data.map(v => ({id: v.id, text: v.first_name+' : '+v.email}))};
    }
    $('#employeebox').select2(select2Config(employeeUrl, employeeData));
    // select2 config
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            }
        }
    }

    const employeeText = "{{ $surcharge->employee_name }} ";
    const employeeVal = "{{ $surcharge->employee_id }}";
    $('#employeebox').append(new Option(employeeText, employeeVal)).prop('readonly', true).change();
    $('#issue_type').prop('disabled', false);
    const months = "{{ $surcharge->months }}";
    $('#months').val(months).attr('readonly', true);
    const date_from = "{{ $surcharge->date }}";
    $('#date').val(date_from).attr('readonly', true);
    const issue = "{{ $surcharge->issue_type }}";
    if (issue == '1') {
        $('#issue_type').append(new Option(issue, true)).prop('readonly', true);
    }

    //Select Issued
    const getIssuanceUrl = "{{ route('biller.surcharge.get_issuance') }}";
    function getIssuance(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: {value: issue_type, employee_id: $('employee_id').val()},
                processResults: callback
            }
        }
    }
    function issuanceData(data) {
        return {results: data};
    }
    $('#issue_type').change(function() {
        //console.log($('#issue_type').find(":selected").val(), ': ',$('#employeeid').val());
        var issue_type = $('#issue_type').find(":selected").val();
        var employee_id = $('#employeeid').val();
        var urlData = "{{ route('biller.surcharges.load', ':employeeId')}}";
        urlData = urlData.replace(':employeeId', employee_id);
        
        $.ajax({
            method: "POST",
            url: getIssuanceUrl,
            data: {value: issue_type, employee_id: employee_id},
            success: function (response) {
                var $tr = $('<tr>').append(
                    $('<td>').html(`<a href="${urlData}">${response.name}</a>`),
                    $('<td>').text(response.cost).html(`<input type="text" readonly name="cost" value="${response.cost}" id="cost">`),
                ).appendTo('#issueTbl tbody');
                // $.each(response, function (index, value) { 
                //     console.log(index, value);
                    
                // });
            }
        });
    });
    $('#submit').click(function () { 
       // console.log();
       $('#monthTbl tbody tr').html('');
        var cost = $('#cost').val();
        var months = $('#months').val();
        var CostperMonth = cost / months;
        console.log(CostperMonth.toFixed());
        var dateStr = $('#date').val();
        var date = new Date(dateStr);
        //date.setMonth(date.getMonth() + 1);
        for (let month = 0; month < months; month++) {
            date.setMonth(date.getMonth() + month);
            var $tr = $('<tr>').append(
                    $('<td>').html(`${date}`),
                    $('<td style="display: none;">').html(`<input type="text" name="datepermonth[]" value="${date}">`),
                    $('<td>').html(`${CostperMonth}`),
                    $('<td style="display: none;">').html(`<input type="text" class="month" name="costpermonth[]" value="${CostperMonth}" id="cost-${month}">`),
                ).appendTo('#monthTbl tbody');
        }
        // $('#monthTbl tbody tr').each(function() {
        //     var value = $(this).find(".month").val(); 
        //     console.log(value);   
        // });
    });
    $('.datepicker').datepicker({autoHide: true})
    $('#date').datepicker('setDate', new Date());
</script>
@endsection
