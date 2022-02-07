@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.management') . ' | ' . trans('labels.backend.projects.edit'))

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ trans('labels.backend.projects.edit') }}</h4>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            @include('focus.projects.partials.projects-header-buttons')
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
                                    {{ Form::model($project, ['route' => ['biller.projects.update', $project], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-project']) }}
                                        <div class="form-group">
                                            @include("focus.projects.form")
                                            <div class="edit-form-btn float-right mb-2">
                                                {{ link_to_route('biller.projects.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-lg']) }}
                                                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-lg']) }}
                                            </div>
                                        </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-styles')
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
{{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    // initialize datepicker
    $('[data-toggle="datepicker"]').datepicker({ format: "{{config('core.user_date_format')}}" });
    $('.from_date').datepicker('setDate', "{{ dateFormat($project->start_date) }}");
    $('.from_date').datepicker({ format: "{{ config('core.user_date_format') }}" });
    $('.to_date').datepicker('setDate', "{{ dateFormat($project->end_date) }}");
    $('.to_date').datepicker({ format: "{{ config('core.user_date_format') }}"});
    // $('#color').colorpicker();
    // initialize select2
    $("#tags").select2();
    $("#employee").select2();
    $("#main_quote").select2();
    $("#other_quote").select2();
    $("#branch_id").select2();

    // customer
    const customer = @json($project->customer_project);
    $("#person").append(new Option(customer.name, customer.id, 'selected', true));
    // branch
    const branch = @json($branch);
    $("#branch_id").append(new Option(branch.name, branch.id, 'selected', true));
    // quotes
    const quotes = @json($project->quotes);
    const qt_id = @json($project->main_quote_id);

    const mainqt = quotes.filter(v => v.id === qt_id)[0];
    const tid = String(mainqt.tid).length < 4 ? ('000'+mainqt.tid).slice(-4) : mainqt.tid;
    const text = `${mainqt.bank_id? '#PI-' : '#QT-'}${tid} - ${mainqt.notes}`;
    $("#main_quote").append(new Option(text, mainqt.id, 'selected', true));

    const otherqt = quotes.filter(v => v.id !== qt_id);
    otherqt.forEach(v => {
        const tid = String(v.tid).length < 4 ? ('000'+v.tid).slice(-4) : v.tid;
        const text = `${v.bank_id? '#PI-' : '#QT-'}${tid} - ${v.notes}`;
        $("#other_quote").append(new Option(text, v.id, 'selected', true));
    });

    $("#person").select2({
        tags: [],
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function (person) {
                return { person };
            },
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
    // on selecting customer
    $("#person").on('change', function() {
        var id = $('#person :selected').val();
        // fetch customer branches
        $("#branch_id").html('').select2({
            ajax: {
                url: "{{route('biller.branches.branch_load')}}?id=" + id,
                dataType: 'json',
                quietMillis: 50,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });

        // fetch customer quotes
        const quoteData = [];
        $("#main_quote").html('').select2({
            ajax: {
                url: "{{route('biller.quotes.customer_quotes')}}?id=" + id,
                dataType: 'json',
                quietMillis: 50,
                processResults: function(data) {
                    const results = $.map(data, function(item) {
                        const tid = String(item.tid).length < 4 ? ('000'+item.tid).slice(-4) : item.tid;
                        return {
                            text: `${item.bank_id ? '#PI-' : '#QT-'}${tid} - ${item.notes}`,
                            id: item.id
                        };
                    });
                    // replace array data
                    quoteData.length = 0;
                    quoteData.push.apply(quoteData, results);

                    return { results };
                },
            }
        });
    });

    // On selecting Main Quote
    $("#main_quote").change(function(e) {
        // set Other Quote select options 
        const data = quoteData.filter(v => v.id !== Number($(this).val()));
        $("#other_quote").html('').select2({ data });
        // set project title
        const name = $(this).find(':selected').text().split(' - ')[1];
        $('#project-name').val(name);
    });
</script>
@endsection
