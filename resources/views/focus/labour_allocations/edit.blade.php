@extends('core.layouts.app')

@section('title', 'Edit | Labour Allocation Management')

@section('content')
<div class="content-wrapper">

    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Allocation Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">                    
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($labour_allocation, ['route' => ['biller.labour_allocations.update', $labour_allocation], 'method' => 'PATCH']) }}
                    @include('focus.labour_allocations.form')
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#labour_date').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());
    getProjectMilestones({{ $labour_allocation->project_id  }});
    
    const date = "{{ $labour_allocation->date }}";
    if (date) $('#labour_date').datepicker('setDate', new Date(date));
    
    // add row
    let tableRow = $('#employeeTbl tbody tr:first').html();
    $("#employee_id-0").select2();
     $('#employeeTbl tbody tr:first').remove();
    let rowIds = 0;
    $('#addstock').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);
        $('#employeeTbl tbody').append('<tr>' + html + '</tr>');
         $("#employee_id-"+i).select2();
    });
    // remove row
    $('#employeeTbl').on('click', '.remove', function() {
        $(this).parents('tr:first').remove();
    });
    
    // job type change
    $('#type').change(function() {
        if (this.value == 'diagnosis') $('#is_payable').val(0);
        else $('#is_payable').val(1);
    });


    function getProjectMilestones(projectId){

        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId},
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful
                var select = $('#project_milestone');

                // Clear any existing options
                select.empty();

                if(data.length === 0){

                    select.append($('<option>', {
                        value: null,
                        text: 'No Milestones Created For This Project'
                    }));

                } else {

                    select.append($('<option>', {
                        value: null,
                        text: 'Select a Budget Line'
                    }));

                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {

                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].due_date);

                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));
                    }

                    let selectedOptionValue = "{{ @$labour_allocation->project_milestone }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }

                    // checkMilestoneBudget(select.find('option:selected').text());

                }

            },
            error: function() {
                // Handle errors here
                console.log('Error loading data');
            }
        });

    }


</script>
@endsection