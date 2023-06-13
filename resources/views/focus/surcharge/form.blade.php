<div class="card">
        <div class="card-body">
            <div class="form-group row">
                <div class="col-3">
                    <label for="payer" class="caption">Select Employee</label>                                       
                    <select class="form-control" id="employeebox" data-placeholder="Search Employee"></select>
                    <input type="hidden" name="employee_id" value="{{ @$purchase->employee_name ?: 1 }}" id="employeeid">
                    <input type="hidden" name="employee_name" value="{{ @$purchase->employee_name ?: 1 }}" id="employee">
                </div> 
                <div class="col-3">
                    <label for="payer" class="caption">Category of Issue</label>                                       
                    <select id="issue_type" name="issue_type" class="custom-select" disabled>
                        <option value="0">Default </option>
                        <option value="lost_broken">Lost/Broken Items </option>
                    </select>
                </div>
                <div class="col-3">
                    {{ Form::label('date', 'Select Start Date',['class' => 'col control-label']) }}
                    {{ Form::text('date', null, ['class' => 'form-control box-size datepicker', 'id'=>'date']) }}
                </div>
                <div class="col-3">
                    <label for="month">Enter Number Month</label>
                    <input type="number" value="1" class="form-control" name="months" id="months" >
                </div>
            </div>
            <div class="form-group row">
                <div class="col-3">
                    <label for="payer" class="caption">Select Cost</label>                                       
                    <select id="cost_type" name="cost_type" class="custom-select">
                        <option value="total_cost">Total Cost</option>
                        <option value="payable">Payable</option>
                    </select>
                </div> 
                <div class="col-2 float-right">
                    <button type="button" id="submit" class="btn btn-success mt-2 process">Process</button>
                </div>   
            </div>
            
            {{-- <div class="table-responsive mt-5">
                <table class="table text-center tfr my_stripe_single" id="issueTbl">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white ">
                            <th class="text-center">Issue Type Name</th>
                            <th class="text-center">Total Cost</th> 
                            <th class="text-center">Total Payable</th>               
                        </tr>
                    </thead>
                    <tbody>
                        <!-- layout -->
                        <td id="td"></td>
                        <td id="td1"></td>
                        @isset ($surcharge)
                            @php ($i = 0)
                           
                                @if ($surcharge)
                                    <tr>
                                        <td>{{ $surcharge->issue_type == '1' ? 'Lost/Broken items':$surcharge->issue_type }}</td>
                                        <td>{{ $surcharge->cost }}</td>  
                                        <input type="hidden" id="" value="{{$surcharge->cost}}">                  
                                        
                                    </tr>
                                    @php ($i++)
                                @endif
                        @endisset
                    </tbody>
                </table>
            </div> --}}
        </div>
</div>
 

<div class="card">
    <div class="card-body">
        <table class="table text-center" id="monthTbl" width="50%">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white ">
                    <th>#</th>
                    <th class="text-center">Installment Dates</th>
                    <th class="text-center">Monthly Cost</th>               
                </tr>
            </thead>
            <tbody>
    
                @isset ($surcharge_items)
                @php ($i = 0)
                @foreach ($surcharge_items as $item)
                    @if ($item)
                        <tr>
                            <td><input type="text" class="form-control" readonly name="datepermonth[]" value="{{ $item->datepermonth }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="text" class="form-control" name="costpermonth[]" value="{{ $item->costpermonth }}"></td>  
                            <input type="hidden" name="id[]" value="{{ $item->id }}">                  
                            
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
                
            </tbody>
        </table>
    </div>
</div>
<h6>Total: <span class="float-right sum" id="sum"></span></h6>
<div>
    <button type="submit" class="btn btn-primary float-right">Submit</button>
</div>