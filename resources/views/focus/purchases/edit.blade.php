@extends('core.layouts.app')

@section('title', 'Direct Purchase | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Direct Purchases Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::model($purchase, ['route' => ['biller.purchases.update', $purchase], 'method' => 'PATCH']) }}
                    @include('focus.purchases.form')
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.purchases.form_js')
<script>
    // reference and tax
    $('#ref_type').val("{{ $purchase->doc_ref_type }}");
    $('#tax').val("{{ $purchase->tax }}");

    // supplier type
    const supplierType = "{{ $purchase->supplier_type }}";
    if (supplierType == 'supplier') $('#colorCheck3').attr('checked', true);

    // date
    $('#date').datepicker('setDate', new Date("{{ $purchase->date }}"));
    $('#due_date').datepicker('setDate', new Date("{{ $purchase->due_date }}"));

    // supplier
    const supplierText = "{{ $purchase->suppliername? $purchase->suppliername : $purchase->supplier->name }} : ";
    const supplierVal = "{{ $purchase->supplier_id }}-{{ $purchase->supplier_taxid? $purchase->supplier_taxid : $purchase->supplier->taxid }}";
    if (supplierType == 'supplier') $('#supplierbox').append(new Option(supplierText, supplierVal, true, true)).change();

    // project
    const projectName = "{{ $purchase->project? $purchase->project->name : '' }}";
    const projectId = "{{ $purchase->project_id }}";
    $('#project').append(new Option(projectName, projectId, true, true)).change();

    // if amount is tax exclusive
    const isTaxExc =  @json($purchase->is_tax_exc);
    if (isTaxExc) {
        $('#tax_exc').change();
    } else {
        $('#tax_exc').prop('checked', false);
        $('#tax_inc').prop('checked', true).change();
    }
</script>
@endsection
