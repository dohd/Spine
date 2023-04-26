@extends ('core.layouts.app')

@section ('title', 'Edit Project Mileston')

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title">Edit Project Milestone</h4>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body mb-2">
                                    {{ Form::model($milestone, ['route' => ['biller.projects.update_project_milestone', $milestone], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-task']) }}
                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.projects.milestone.form")
                                        <div class="edit-form-btn float-right">
                                            
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->
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
    {{ Html::style('core/app-assets/css-'.visual().'/pages/app-todo.css') }}
    {{ Html::style('core/app-assets/css-'.visual().'/plugins/forms/checkboxes-radios.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}

    <script>
        var project_id = @json($milestone->project_id);
        $.ajax({
            methode: "GET",
            url: "{{ route('biller.projects.get_extimated_milestone') }}",
            data: {
                project_id : project_id,
            },
            success: function (response) {
                if(response == -1){
                    $('.extimate').text('No Limit');
                    $('#limit').val(-1);
                }
                else{
                    $('.extimate').text(response);
                    $('#limit').val(response);
                }
                
            }
        });
        $('[data-toggle="datepicker"]').datepicker({autoHide: true, format: '{{config('core.user_date_format')}}'});
        $('.from_date').datepicker('setDate', '{{dateFormat(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d')))))}}');
        $('.from_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
        $('.to_date').datepicker('setDate', 'today');
        $('.to_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
        $('#color').colorpicker();
        $('#extimated-milestone').on('key up change', function () {
        var extimate = accounting.unformat($('.extimate').text());
        var limit = accounting.unformat($('#limit').val());
        var extimated_milestone_amount = accounting.unformat($(this).val());
        if(limit <= -1){
            $('#extimated-milestone').val();
            
        }
       else if (limit == 0) {
            swal({
                    title: 'Adjust Your Budget?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () =>{ 
                    $('#extimated-milestone').val('');
                    $('#AddMileStoneModal').modal('hide')
                });
                
        }
        else if(extimated_milestone_amount > extimate){
            $('#extimated-milestone').val(extimate).change();
            
        }
    });
    </script>

@endsection