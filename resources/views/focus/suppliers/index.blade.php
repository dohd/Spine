@extends ('core.layouts.app')

@section ('title', trans('labels.backend.suppliers.management'))

@section('page-header')
    <h1>{{ trans('labels.backend.suppliers.management') }}</h1>
@endsection

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-detached content-right">
            <div class="content-body">
                <div class="content-overlay"></div>


                <section class="row all-contacts">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">
                                <div class="card-body">
                                    <!-- Task List table -->
                               
                                    <div class="card-body">

                                        <ul class="nav nav-tabs nav-top-border no-hover-bg "
                                            role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="active-tab1" data-toggle="tab"
                                                   href="#active1" aria-controls="active1" role="tab"
                                                   aria-selected="true">Supplier Info</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link " id="active-tab3" data-toggle="tab"
                                                   href="#active3" aria-controls="active3"
                                                   role="tab">Transactions</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link " id="active-tab3" data-toggle="tab"
                                                   href="#active4" aria-controls="active3"
                                                   role="tab">Purchase Orders</a>
                                            </li>
                                          


                                        </ul>
                                        <div class="tab-content px-1 pt-1">
                                            <div class="tab-pane active in" id="active1"
                                                 aria-labelledby="active-tab1" role="tabpanel">
                                                 <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5 ">
                                                        <p>Supplier No</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5 ">
                                                        <p>Name</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>{{trans('customers.phone')}}</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5   font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>{{trans('customers.email')}}</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5   font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>


                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Address</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>City</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5   font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Region</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Country</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5 ">
                                                        <p>{{trans('customers.postbox')}}</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>

                                                
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>{{trans('customers.taxid')}}</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Bank </p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Account Number</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Account Name</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                             
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Bank Code</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  ">
                                                        <p>Mpesa  Account</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane" id="active3" aria-labelledby="link-tab3"
                                                 role="tabpanel">

                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                        <p>{{trans('customers.docid')}}</p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                                        <p></p>
                                                    </div>
                                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                                        <p></p>
                                                    </div>
                                                </div>


                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="sidebar-detached sidebar-left">
            <div class="sidebar">
                <div class="bug-list-sidebar-content">
                    <!-- Predefined Views -->
                    <div class="card">
                       

                        <div class="card-body">
                          
                            <table id="suppliers-table"
                            class="table table-striped table-bordered zero-configuration" cellspacing="0"
                            width="100%">
                         <thead>
                         <tr>
                             <th>{{ trans('customers.name') }}</th>
                            
                         </tr>
                         </thead>


                         <tbody>
                         <tr>
                             <td colspan="100%" class="text-center text-success font-large-1"><i
                                         class="fa fa-spinner spinner"></i></td>
                         </tr>
                         </tbody>
                     </table>
                        </div>
                        <!--/ Groups-->


                    </div>
                    <!--/ Predefined Views -->

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        $(function () {
            setTimeout(function () {
                draw_data()
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('click', ".customer_active", function (e) {
                var cid = $(this).attr('data-cid');
                var active = $(this).attr('data-active');
                if (active == 1) {
                    $(this).removeClass('checked');
                    $(this).attr('data-active', 0);
                } else {
                    $(this).addClass('checked');
                    $(this).attr('data-active', 1);
                }

                $.ajax({
                    url: '{{ route("biller.suppliers.active") }}',
                    type: 'post',
                    data: {'cid': cid, 'active': active}
                });
            });

            var dataTable = $('#suppliers-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.suppliers.get") }}',
                    type: 'post'
                },
                columns: [
                  
                    {data: 'name', name: 'name'},
                
                  
                   
                  
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'frt',
            
            });
            $('#suppliers-table_wrapper').removeClass('form-inline');

        }
    </script>
@endsection
   

