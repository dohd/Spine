<div class="modal" id="AddProjectModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content ">
            <section class="todo-form">
                <form id="data_form_project" class="todo-input">
                    <div class="modal-header">
                        <h5 class="modal-title" id="data_project">{{trans('projects.new_project')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">     
                        <div class="row">
                            <fieldset class="form-group position-relative has-icon-left  col-md-4">
                                <div><label for="customer">Search Customer</label></div>
                                <select id="person" name="customer_id" class="form-control required select-box" data-placeholder="{{trans('customers.customer')}}">
                                </select>
                            </fieldset>
                            <fieldset class="form-group position-relative has-icon-left  col-md-4">
                                <div><label for="branch">Branch</label></div>
                                <select id="branch_id" name="branch_id" class="form-control  select-box" data-placeholder="Branch">
                                </select>
                            </fieldset>
                            <fieldset class="form-group col-md-4">
                                <div><label for="projectType">Project Type / Sales Account</label></div>
                                <select class="form-control  select-box" name="sales_account" id="sales_account" data-placeholder="Project Type/Sales Account" required>
                                    <option value="">Project Type/Sales Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{$account['id']}}">{{$account['code']}} {{$account['holder']}}</option>
                                    @endforeach
                                </select>
                            </fieldset>
                        </div>
                   
                        <div class="row">
                            <fieldset class="form-group position-relative has-icon-right  col-md-6">
                                <div><label for="quote">Primary / Main Quote</label></div>
                                <select required id="main_quote" name="main_quote" class="form-control required select-box" data-placeholder="Primary / Main Quote">
                                </select>
                            </fieldset>
                            <fieldset class="form-group position-relative has-icon-right  col-md-6">
                                <div><label for="quote">Secondary / Other Quotes</label></div>
                                <select multiple id="other_quote" name="other_quote[]" class="form-control required select-box" data-placeholder="Seconday / Other Quotes">
                                </select>
                            </fieldset>
                        </div>
                        
                        <div class="row">
                            <fieldset class="form-group col-8">
                                <div><label for="projectTitle">Project Title</label></div>
                                <input type="text" class="new-todo-item form-control required" placeholder="{{trans('projects.name')}}" name="name" id="project-name">
                            </fieldset>
                            <fieldset class="form-group col-4">
                                <div><label for="projectNumber">Project No.</label></div>
                                <input type="text" class="new-todo-item form-control required" placeholder="Project Number" name="project_number" id="project-number" required>
                            </fieldset>
                        </div>

                        <div class="row">
                            <fieldset class="form-group col-md-4">
                                <div><label for="status">Status</label></div>
                                <select class="custom-select" id="todo-select" name="status">
                                    @foreach($mics->where('section','=',2) as $row)
                                        <option value="{{$row['id']}}">{{$row['name']}}</option>
                                    @endforeach
                                </select>
                            </fieldset>
                            <fieldset class="form-group col-md-4">
                                <div><label for="projectPriority">Project Priority</label></div>
                                <select class="custom-select" id="todo-select" name="priority">
                                    <option value="Medium" selected>{{trans('projects.priority')}}</option>
                                    <option value="Low">{{trans('tasks.Low')}}</option>
                                    <option value="Medium">{{trans('tasks.Medium')}}</option>
                                    <option value="High">{{trans('tasks.High')}}</option>
                                    <option value="Urgent">{{trans('tasks.Urgent')}}</option>
                                </select>
                            </fieldset>
                            <fieldset class="form-group col-md-4">
                                <div><label for="tags">Tags</label></div>
                                <select class="form-control select-box" name="tags[]" id="tags" data-placeholder="-- {{ trans('tags.select') }} --" multiple required>
                                    @foreach($mics->where('section','=',1) as $tag)
                                        <option value="{{$tag['id']}}">{{$tag['name']}}</option>
                                    @endforeach
                                </select>
                            </fieldset>
                        </div>

                        <div><label for="Description">Description</label></div>
                        <fieldset class="form-group position-relative has-icon-left col-12">                            
                            <div class="form-control-position"><i class="icon-emoticon-smile"></i></div>
                            <input type="text" id="new-todo-desc" class="new-todo-desc form-control required" placeholder="{{trans('tasks.short_desc')}}" name="short_desc">
                        </fieldset>
                        <fieldset class="form-group col-12">
                            <textarea class="new-todo-item form-control required" placeholder="{{trans('tasks.description')}}" rows="6" name="note"></textarea>
                        </fieldset>

                        <div class="form-group row">
                            <div class="col-md-4 col-xs-12 mt-1">
                                <div class="row">
                                    <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{trans('meta.from_date')}}</label>
                                    <div class="col-sm-6 col-xs-6">
                                        <input type="text" class="form-control from_date required" placeholder="Start Date" name="start_date" autocomplete="false" data-toggle="datepicker">
                                        <input type="time" name="time_from" class="form-control" value="00:00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-xs-12 mt-1">
                                <div class="row">
                                    <label class="col-sm-4 col-xs-6  control-label" for="sdate">{{trans('meta.to_date')}}</label>
                                    <div class="col-sm-6 col-xs-6 ">
                                        <input type="text" class="form-control required to_date" placeholder="End Date" name="end_date" data-toggle="datepicker" autocomplete="false">
                                        <input type="time" name="time_to" class="form-control" value="23:59">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-xs-12 mt-1">
                                <div class="row">
                                    <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{trans('tasks.link_to_calender')}}</label>
                                    <div class="col-sm-6 col-xs-6">
                                        <input type="checkbox" class="form-control" name="link_to_calender">
                                        {{ Form::text('color', '#0b97f4', ['class' => 'form-control round', 'id'=>'color','placeholder' => trans('miscs.color'),'autocomplete'=>'off']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <fieldset class="form-group col-md-6">
                                <div><label for="projectPlanningPhase">Project Planning Phase</label></div>
                                <input type="text" class="new-todo-item form-control" placeholder="{{trans('projects.phase')}}" name="phase">
                            </fieldset>
                            <fieldset class="form-group col-md-3">
                                <div><label for="projectBudget">Project Budget</label></div>
                                <input type="text" class="new-todo-item form-control" placeholder="{{trans('projects.worth')}}" name="worth" required>
                            </fieldset>
                            <fieldset class="form-group col-md-3">
                                <div><label for="projectShare">Project Share</label></div>
                                <select class="form-control select-box" name="project_share" data-placeholder="{{trans('projects.project_share')}}">
                                    <option value="0">{{trans('projects.private')}}</option>
                                    <option value="1">{{trans('projects.internal')}}</option>
                                    <option value="2">{{trans('projects.external')}}</option>
                                    <option value="3" selected>{{trans('projects.internal_participate')}}</option>
                                    <option value="4">{{trans('projects.external_participate')}}</option>
                                    <option value="5">{{trans('projects.global_participate')}}</option>
                                    <option value="6">{{trans('projects.global_view')}}</option>
                                </select>
                            </fieldset>
                        </div>
                        <div class="row">
                            <fieldset class="form-group position-relative has-icon-left col-md-4">
                                <div><label for="taskAssign">Assign To</label></div>
                                <select class="form-control  select-box" name="employees[]" id="employee" data-placeholder="{{ trans('tasks.assign') }}" multiple>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee['id'] }}">{{ $employee['first_name'] }} {{ $employee['last_name'] }}</option>
                                    @endforeach
                                </select>
                            </fieldset>                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_project" class="btn btn-info add-todo-item" data-dismiss="modal"><i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">{{trans('projects.new_project')}}</span></button>
                        </fieldset>
                    </div>
                    <input type="hidden" value="{{ route('biller.projects.store') }}" id="action-url">
                </form>
            </section>
        </div>
    </div>
</div>