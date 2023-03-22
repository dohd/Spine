<div class="modal" id="AddBudgetModal" tabindex="-1" role="dialog" aria-labelledby="AddBudgetLabel"
     aria-hidden="true">
    <div class="modal-dialog mw-100" role="document">
        <div class="modal-content">
            <section class="todo-form">
                <form id="data_form_budget" class="budget-input">
                    <div class="modal-header">
                        <h5 class="modal-title" id="AddBudgetLabel">Budget Quote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body px-5">
                        <div class="row">
                            <div class="col-12 cmp-pnl">
                                <div id="customerpanel" class="inner-cmp-pnl">                        
                                    <div class="form-group row"> 
                                        <div class="col-5">
                                            <label for="customer" class="caption">Customer</label>                                       
                                            {{ Form::text('customer', @$quote->customer? $quote->customer->company : '', ['class' => 'form-control', 'disabled', 'id'=>'customer']) }}
                                        </div> 
                                        <div class="col-3">
                                            <label for="branch" class="caption">Branch</label>                                       
                                            {{ Form::text('branch', @$quote->branch? $quote->branch->name : '', ['class' => 'form-control','id'=>'branch', 'disabled']) }}
                                        </div> 
                                        <div class="col-2">
                                            <label >Serial No</label>
                                            <div class="input-group">
                                                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                                {{ Form::text('tid', gen4tid(@$quote->bank_id ? 'PI-' : 'QT-', @$quote->tid), ['class' => 'form-control round','id'=>'tid', 'disabled']) }}
                                            </div>
                                        </div>
                                        <div class="col-2"><label for="invoicedate" class="caption">{{trans('general.date')}}</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                                {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date', 'disabled']) }}
                                            </div>
                                        </div>                                                               
                                    </div> 
                                </div>
                            </div>
                        </div>        
                        
                        <div class="form-group row">
                            <div class="col-10">
                                <label for="subject" class="caption">Subject / Title</label>
                                {{ Form::text('notes', null, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                            </div>
                            <div class="col-2">
                                <label for="client_ref" class="caption">Client Ref / Callout ID</label>                                       
                                {{ Form::text('client_ref', null, ['class' => 'form-control', 'id' => 'client_ref', 'disabled']) }}
                            </div> 
                        </div>
                        <div class="content-body mt-5">
                            <div class="card">
                                <table id="budgetviewTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="80%">
                                    <thead>
                                        <tr class="item_header bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th width="35%" >Product</th>
                                            <th>Approved Qty</th>
                                            <th>UoM</th>
                                            <th>Qty</th>
                                            <th>Buy Price</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>            
                            </div>
                        </div>

                        <div class="content-body mt-5">
                            <div class="card">
                                <div class="form-group row">
                                    <div class="col-8">
                                        <table id="budgetviewskillsetTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="50%">
                                            <thead>
                                                <tr class="item_header bg-gradient-directional-blue white">
                                                    <th>#</th>
                                                    <th>Skill Type</th>
                                                    <th>Charge</th>
                                                    <th>Work Hrs</th>
                                                    <th>Count Technician</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table> 
                                        <div class="form-group float-right mt-1">
                                            <div><label for="budget-total">Total Amount</label></div>
                                            <div><input type="text" value="0" class="form-control" id="labour-total" name="labour_total" readonly></div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group">
                                            <div><label for="tool">Extra Note</label></div>
                                            <textarea name="note" id="note" cols="45" rows="6" class="form-control html_editor" readonly>
                                                @isset($budget)
                                                    {{ $budget->note }}
                                                @endisset
                                            </textarea>   
                                        </div>  

                                        <div class="form-group">
                                            <div>
                                                <label for="quote_total">Quote Total</label>
                                                <span class="text-danger">(VAT Exc)</span>
                                            </div>
                                            {{ Form::text('quote_total', null, ['class' => 'form-control', 'id' => 'quote_total', 'readonly']) }}
                                        </div>
                                        <div class="form-group">
                                            <div>
                                                <label for="budget-total">Budget Total</label>&nbsp;
                                                <span class="text-primary font-weight-bold">
                                                    (E.P: &nbsp;<span class="text-dark profit">0</span>)
                                                </span>
                                            </div>
                                            <input type="text" value="0" class="form-control" id="budget-total" name="budget_total" readonly>
                                        </div>     
                                    </div>
                                </div>           
                            </div>
                        </div>
                        <div class="content-body">
                           <div class="col-4 float-right">
                            
                                                  
                           
                            </div> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_budget" class="btn btn-info add-quote-item"
                                    data-dismiss="modal"><i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">{{trans('general.add')}}</span></button>
                        </fieldset> --}}
                    </div>

                    <input type="hidden" value="{{route('biller.projects.store_meta')}}" id="action-url_8">
                    <input type="hidden" value="{{$project->id}}" name="project_id">
                    <input type="hidden" value="8" name="obj_type">
                </form>
            </section>
        </div>
    </div>
</div>
@section('extra-scripts')
@include('focus.projects.budget-js')
@endsection