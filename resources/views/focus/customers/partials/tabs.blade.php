<ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Customer Info</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Transactions</a>
    </li>
    <li class="nav-item">
        <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Invoices</a>
    </li> 
    <li class="nav-item">
        <a class="nav-link " id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Statement on Invoice</a>
    </li> 
</ul>
<div class="tab-content px-1 pt-1">
    <!-- Customer Info -->
    <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
            @php
                $labels = [
                    'Name', 'Phone', 'Email', 'Address', 'Company', 'City', 'Region', 'Country', 'PostBox',
                    'Tax ID' => 'taxid',  
                ];
            @endphp
            <tbody> 
                @foreach ($labels as $key => $val)
                    <tr>
                        <th>{{ is_numeric($key) ? $val : $key }}</th>
                        <td>{{ $customer[strtolower($val)] }}</td>
                    </tr>
                @endforeach      
            </tbody>
        </table>
    </div>
                
    <!-- Transactions -->
    <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
        <div class="row">
            <div class="col-2">Search Date Between</div>
            <div class="col-2">
                <input type="text" class="form-control form-control-sm datepicker start_date">
            </div>
            <div class="col-2">
                <input type="text" class="form-control form-control-sm datepicker end_date">
            </div>
            <div class="col-2">
                <input type="button" id="search2" value="Search" class="btn btn-info btn-sm search">
                <button type="button" id="refresh2" class="btn btn-success btn-sm refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
            </div>
        </div>
        <table id="transTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>                                            
                    @foreach (['#', 'Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Account Balance'] as $val)
                        <th>{{ $val }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <!-- Aging -->
        <div class="mt-2 aging">
            <h5>Aging Report</h5>
            <table class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>                                                    
                        @foreach ([30, 60, 90, 120] as $val)
                            <th>{{ $val }} Days</th>
                        @endforeach
                        <th>Total</th>
                        <th style="border-top: 1px solid white; border-bottom: 1px solid white;"></th>
                        <th>On Account</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @php
                            $total = 0;
                        @endphp
                        @for ($i = 0; $i < count($aging_cluster); $i++) 
                            <td>{{ numberFormat($aging_cluster[$i]) }}</td>
                            @php
                                $total += $aging_cluster[$i];
                            @endphp
                        @endfor
                        <td>{{ numberFormat($total) }}</td>
                        <td style="border-top: 1px solid white; border-bottom: 1px solid white;"></td>
                        <td>0.00</td>
                    </tr>
                </tbody>                     
            </table>  
        </div>
    </div>

    <!-- Invoices -->
    <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
        <table id="invoiceTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>                                                    
                    @foreach (['#', 'Date', 'Status', 'Note', 'Amount', 'Paid'] as $val)
                        <th>{{ $val }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody></tbody> 
        </table>                                            
    </div>

    <!-- Statement on Invoice  -->
    <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
        <div class="row mb-1">
            <div class="col-2">Search Date Between</div>
            <div class="col-2">
                <input type="text" class="form-control form-control-sm datepicker start_date">
            </div>
            <div class="col-2">
                <input type="text" id="end_date" class="form-control form-control-sm datepicker end_date">
            </div>
            <div class="col-2">
                <input type="button" id="search4" value="Search" class="btn btn-info btn-sm search">
                <button type="button" id="refresh4" class="btn btn-success btn-sm refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
            </div>
        </div>
        <table id="stmentTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>                                            
                    @foreach (['#', 'Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Invoice Balance'] as $val)
                        <th>{{ $val }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody></tbody>  
        </table>   
        <!-- Aging -->
    </div>
</div>