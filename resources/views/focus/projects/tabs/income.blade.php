<div class="tab-pane" id="tab_data10" aria-labelledby="tab10" role="tabpanel">
    @if($project->creator->id == auth()->user()->id)
        <div class="card-body">
            <table id="invoices-table_p"
                    class="table table-striped table-bordered zero-configuration"
                    cellspacing="0"
                    width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No.</th>
                        <th>{{ trans('customers.customer') }}</th>
                        <th>{{ trans('invoices.invoice_date') }}</th>
                        <th>{{ trans('general.amount') }}</th>
                        <th>{{ trans('general.status') }}</th>
                        <th>{{ trans('invoices.invoice_due_date') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endif
</div>
