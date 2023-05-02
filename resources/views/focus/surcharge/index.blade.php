@extends ('core.layouts.app')

@section ('title', 'Create | Surcharges')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Surcharges</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        {{-- @include('focus.pricelistsSupplier.partials.pricelists-header-buttons') --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                
                    <div class="col-12">
                        <form action="{{route('biller.surcharges.store')}}" method="post">
                            @csrf
                        @include('focus.surcharge.form')
                    </form>
                    </div>
                    
               
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
                    $('<td>').html(`${response.cost}`),
                    $('<td style="display: none;">').text(response.cost).html(`<input type="text" readonly name="cost" value="${response.cost}" id="cost">`),
                    $('<td>').html(`${response.payable}`),
                    $('<td style="display: none;">').text(response.payable).html(`<input type="text" readonly name="payable" value="${response.payable}" id="payable">`),
                ).appendTo('#issueTbl tbody');
            }
        });
    });
    $('#submit').click(function () { 
       // console.log();
       $('#monthTbl tbody tr').html('');
        var cost = $('#cost').val();
        var payable = $('#payable').val();
        var months = $('#months').val();
        var cost_type = $('#cost_type').find(":selected").val();
        if (cost_type == "0") {
            var CostperMonth = cost / months;
            CostperMonth.toFixed();
        }else{
            var CostperMonth = payable / months;
            CostperMonth.toFixed();
        }
        var dateStr = $('#date').val();
        var date = new Date(dateStr);
        //date.setMonth(date.getMonth() + 1);
        for (let month = 0; month < months; month++) {
            date.setMonth(date.getMonth() + 1);
            let mnth = ("0" + (date.getMonth() + 1)).slice(-2);
            let day = ("0" + date.getDate()).slice(-2);
            let combined =  [day,mnth, date.getFullYear()].join("-");
            var $tr = $('<tr>').append(
                    $('<td>').html(`${combined}`),
                    $('<td style="display: none;">').html(`<input type="text" name="datepermonth[]" value="${combined}">`),
                    // $('<td>').html(`${CostperMonth}`),
                    $('<td>').html(`<input type="text" class="month form-control" name="costpermonth[]" value="${CostperMonth}" id="cost-${month}">`),
                ).appendTo('#monthTbl tbody');
        }

        $("#monthTbl").on('input', '.month', function () {
       var calculated_total_sum = 0;
     
       $("#monthTbl .month").each(function () {
           var get_textbox_value = $(this).val();
           if ($.isNumeric(get_textbox_value)) {
              calculated_total_sum += parseFloat(get_textbox_value);
              }                  
            });
              $("#sum").html(calculated_total_sum);
              var sum = $("#sum").text();
              var costing = $('#cost').val();
              var payable = $('#payable').val();
              var cost_type = $('#cost_type').find(":selected").val();
              if (cost_type == "0") {
                    if (costing < sum) {
                    $('.month').prop('readonly', true);
                    console.log(costing);
                }
              }
              else if (cost_type == "1") {
                    if (payable < sum) {
                    $('.month').prop('readonly', true);
                    console.log(costing);
                }
              }
              
       });
       
    });
    $('.datepicker').datepicker({autoHide: true})
    $('#date').datepicker('setDate', new Date());
    // $('#issue_type').select2(getIssuance(getIssuanceUrl, issuanceData));
</script>

@endsection
