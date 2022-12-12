<div class="row">
    <div class="col-12 cmp-pnl">
        <div id="employee_namepanel" class="inner-cmp-pnl">                        
            <div class="form-group row"> 
                <div class="col-5">
                    <label for="payer" class="caption">Search Employee</label>                                       
                    <select class="form-control" name="employee_name" id="employeebox" data-placeholder="Search Employee"></select>
                    <input type="hidden" name="employee_id" value="{{ @$purchase->employee_id || 1 }}" id="employeeid">
                </div> 
                <div class="col-3">
                    {{ Form::label( 'issue_date', 'Date Issued',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('issue_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'issue_date']) }}
                    </div>
                </div> 
                <div class="col-2">
                    
                    {{ Form::label( 'return_date', 'Expected Return Date',['class' => 'col control-label']) }}
                        <div class='col'>
                            {{ Form::text('return_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'return_date']) }}
                        </div>
                </div>    
                <div class="col-2">
                    <label for="toAddInfo" class="caption">Requisition Number*</label>
                        {{ Form::text('acquisition_number', null, ['class' => 'form-control', 'placeholder'=>'Requisition Number', 'rows'=>'1', 'required']) }}
                    </div>
                </div>                                                           
            </div> 
        </div>
    </div>
</div> 

<div class="form-group row">
    <div class="col-10">
        <label for="toAddInfo" class="caption">Note*</label>
            {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => 'Note', 'rows'=>'3', 'required']) }}
        </div>
    </div>
    
     
</div>

<div class="tab-content px-1 pt-1">
    <!-- tab1 -->
    @include('focus.assetissuance.partials.stock_tab')
</div>
<div class="px-2 float-right mb-3">
    {{ Form::submit('Issue Product', ['class' => 'btn btn-primary sub-btn btn-lg']) }}
</div>
