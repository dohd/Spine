@extends ('core.layouts.app')

@section ('title', 'Create | Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Equipment Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.equipments.partials.equipments-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.equipments.store', 'method' => 'post', 'id' => 'create-productcategory']) }}                                
                                @include("focus.equipments.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.equipments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}                                            
                                </div>                                    
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });


    $('[data-toggle="datepicker"]').datepicker({
        autoHide: true,
        format: "{{ config('core.user_date_format')}}"
    }).datepicker('setDate', new Date());

    $("#person").select2({
        tags: [],
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({search: term}),
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name+' - '+item.company,
                            id: item.id
                        }
                    })
                };
            },
        }
    });


    $("#person").on('change', function () {
        $("#branch").val('').trigger('change');
        var tips = $('#person :selected').val();

        $("#branch").select2({
            ajax: {
                url: "{{ route('biller.branches.select') }}?customer_id=" + tips,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                params: {'cat_id': tips},
                data: product => ({product}), 
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });
    });


    $("#unit_type").on('change', function () {
        $("#indoor").val('').trigger('change');
        var tips = $('#unit_type :selected').val();

        $("#indoor").select2({
            ajax: {
                url: "{{ route('biller.equipments.equipment_load') }}?id=" + tips,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                params: {'cat_id': tips},
                data: product => ({product}),
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });
    });
</script>
@endsection