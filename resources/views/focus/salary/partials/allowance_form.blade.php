
<div class="table-responsive">
    <table class="table text-center tfr my_stripe_single" id="productsTbl">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white ">
                <th width="20%" class="text-center">Name</th>
                <th width="10%" class="text-center">Amount</th>
                <th width="10%" class="text-center">Actions</th>               
            </tr>
        </thead>
        <tbody>
            <!-- layout -->
            <tr>
                <td>
                    
                    <select class="form-control deduct" name="allowance_id[]" id="deductname" data-placeholder="Select Allowance Type">
                        <option value="">Select Allowance Type</option>
                        <option value="1">House</option>
                        <option value="2">Medical</option>
                        <option value="3">Transport</option>
                    </select>
                </td>
                
                <td><input type="text" class="form-control from" id="amount-0" name="amount[]"></td> 
               
                <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>


                {{-- @isset ($deductions)
                <tr>
                    <td>
                        
                        <select class="form-control round deduct" name="allowance_id[]" id="deductname" data-placeholder="Select Allowance Type">
                            <option value="">Default</option>
                            <option value="NHIF" @isset($deductions)
                                {{$deductions->allowance_id == 'NHIF' ? 'selected':''}}
                            @endisset>NHIF</option>
                            <option value="NSSF" @isset($deductions)
                                    {{$deductions->allowance_id == 'NSSF' ? 'selected':''}}
                             @endisset>NSSF</option>
                            <option value="PAYE" @isset($deductions)
                                {{$deductions->allowance_id == 'PAYE' ? 'selected':''}}
                            @endisset>PAYE</option>
                        </select>
                    </td>
                    
                    <td><input type="text" class="form-control from" id="amount-0" value="{{ $deductions->amount}}" name="amount[]"></td> 
                 
                    <td><input type="text" class="form-control rate" name="rate[]" id="rate-0" value="{{$deductions->rate}}"></td> 
                  
                    <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    
                </tr>
                @endisset --}}
            </tr>
        </tbody>
    </table>
</div>
<a href="javascript:" class="btn btn-success" aria-label="Left Align" id="addstock"><i class="fa fa-plus-square"></i> Add Row</a>

