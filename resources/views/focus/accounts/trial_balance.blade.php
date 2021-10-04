@extends ('core.layouts.app')
@section ('title', trans('accounts.balance_sheet').' | ' . trans('labels.backend.accounts.management'))

@section('page-header')
    <h1>
        {{ trans('labels.backend.accounts.management') }}
        <small>{{ trans('labels.backend.accounts.create') }}</small>
    </h1>
@endsection
@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="mb-0"> Trial Balance <a class="btn btn-success btn-sm"
                                                                             href="{{ route( 'biller.accounts.balance_sheet',['p']) }}">
                            <i class="fa fa-print"></i> {{ trans('general.print') }}</a></h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.accounts.partials.accounts-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            
                                <div class="card-content print_me">
                                    <h5 class="title bg-gradient-x-info  p-1 white">
                                       Trial Balance
                                    </h5>
                                    <p>&nbsp;</p>
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{trans('accounts.holder')}}</th>
                                            <th>{{trans('accounts.account')}}</th>
                                            <th>Debit ({{config('currency.symbol')}})</th>
                                            <th>Credit ({{config('currency.symbol')}})</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php $i = 1;
                    $gross = 0;
                    $totaldebitbalance=0;
                    $totalcreditbalance=0;
                    foreach ($account as $row) {

                    $debit=$row->transactions->sum('debit');
                    $credit=$row->transactions->sum('credit');

                     if ($debit >0 || $credit >0 ) {

                    

                    $balance=$debit - $credit;

                    if($balance >0){
                    $debitbalance=$balance;
                 $totaldebitbalance+=$debitbalance;
                }else{
                   
                $debitbalance=0;
                 $totaldebitbalance+=0;
            }


              if($balance < 0){

               $creditbalance=$credit-$debit;
              $totalcreditbalance+=$creditbalance;

          }else{

           $creditbalance=0;
            $totalcreditbalance+=0;


      }


                      
                            echo "<tr>
                    <td>$i</td>
                    <td>".strip_tags($row->number)."</td>
                    <td>".strip_tags($row->holder)."</td>

                    <td>" . numberFormat($debitbalance) . "</td>
                     <td>" . numberFormat($creditbalance) . "</td>
                    </tr>";
                            
$i++;


                  }
                        }
                  
                                        @endphp
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>

                                            <th> </th>
                                              <th><h3 class="text-xl-left">{{ amountFormat($totaldebitbalance)}}</h3></th>

                                            <th>
                                                <h3 class="text-xl-left">{{ amountFormat($totalcreditbalance)}}</h3>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                               

                                  
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-scripts')
    <script type="text/javascript">
        $(function () {
            // $(".print_me").printThis();
        });
    </script>
@endsection