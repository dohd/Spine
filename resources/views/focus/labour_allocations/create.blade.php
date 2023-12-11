@extends ('core.layouts.app')

@section ('title', 'Create | Labour Allocation Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Labour Creation</h4>
            </div>
            <div class="col-6">
                <div class="btn-group float-right">
                    @include('focus.labour_allocations.partials.labour_allocation-header-buttons')
                </div>
            </div>
        </div>
        
        <div class="content-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-12">
                                    <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                        <option value=""></option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-12">
                                    <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                        <option value=""></option>
                                        @foreach ([] as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> 
                        </div>
                    </div>    
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="labour_allocationTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Project No.</th>
                                        <th>Project Title</th>
                                        <th>#QT/PI No.</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>Deadline</th>
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
        </div>
        <!-- End Content -->
    </div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    @include('focus.labour_allocations.partials.attach-employee')
    {{-- <input type="hidden" id="loader_url" value="{{route('biller.labour_allocation.load')}}"> --}}
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) };
                },
            }
        }
    };

    // form submit callback
    function trigger(res) {
        // $(data.row).prependTo("table > tbody");
        // $("#data_form_project").trigger('reset');
        $('#labour_allocation-table').DataTable().destroy();
        Index.drawDataTable();
    }

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

                    let selectedOptionValue = "{{ @$purchase->project_milestone }}";
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

    function checkMilestoneBudget(milestoneString){

        // Get the value of the input field
        let selectedMilestone = milestoneString;

        // Specify the start and end strings
        let startString = 'Balance: ';
        let endString = ' | Due on';

        // Find the index of the start and end strings
        let startIndex = selectedMilestone.indexOf(startString);
        let endIndex = selectedMilestone.indexOf(endString, startIndex + startString.length);

        // Extract the string between start and end
        let milestoneBudget = parseFloat(selectedMilestone.substring(startIndex + startString.length, endIndex)).toFixed(2);

        // console.log("Milestone Budget is " + milestoneBudget + " and purchase total is " + purchaseGrandTotal);

        if(purchaseGrandTotal > milestoneBudget){

            // console.log( "Milestone Budget is " + milestoneBudget );
            // console.log( "Milestone Budget Exceeded" );
            $("#milestone_warning").text("Milestone Budget of " + milestoneBudget + " Exceeded!");
        }
        else {

            $("#milestone_warning").text("");
        }

    }



    const Index = {

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            
            $("#submit-data_project").on("click", Index.onSubmitProject);
            $("#customerFilter").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#branchFilter").select2(config.branchSelect).change(Index.onChangeBranch);
            $('#AddEmployeeModal').on('shown.bs.modal', Index.onShowAttachLabourModal);
            $('#labour_allocationTbl').on('click', '.labour', Index.onClickAddLabour);
            Index.drawDataTable();
        },
        
        onSubmitProject() {
            e.preventDefault();
            let form_data = {};
            form_data['form'] = $("#data_form_project").serialize();
            form_data['url'] = $('#action-url').val();
            $('#AddEmployeeModal').modal('toggle');
            addObject(form_data, true);
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#labour_allocationTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeBranch() {
            $('#labour_allocationTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },
        
        onChangeProject(){
            $('#labour').removeAttr('data-id');
        },
        
        onClickAddLabour() {
            const projectId = $(this).attr('data-id');
            $('#project_id').val(projectId);
            // fetch expected hours
            $('#expectedHrs').html(`(Rem: ${0})`);
            $.get("{{ route('biller.labour_allocations.expected_hours') }}?project_id=" + projectId, function(data) {
                $('#expectedHrs').html(`(Rem: ${data.hours})`);
                $('#project_name').html(`${data.project_tid}: <span class="text-primary">${data.quote_tid} EGG</span>`);

                getProjectMilestones(data.project_id)
            });


        },

        onShowAttachLabourModal() {
            $("#employee").select2();
            $("#person").select2({allowClear: true, dropdownParent: $('#AddEmployeeModal .modal-body')});
            
            // job type change
            $('#type').change(function() {
                if (this.value == 'diagnosis') $('#is_payable').val(0);
                else $('#is_payable').val(1);
            });
        },

        drawDataTable() {
            $('#labour_allocationTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.labour_allocations.get_labour') }}",
                    type: 'POST',
                    data: {
                        customer_id: $("#customerFilter").val(),
                        branch_id: $("#branchFilter").val(),
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['tid', 'name','main_quote_id', 'priority', 'status','start_date'].map(v => ({data: v, name: v})),
                    {data: 'end_date', name: 'end_date', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init);
</script>
@endsection
