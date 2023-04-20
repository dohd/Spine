<div class="modal" id="AddProjectModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title content-header-title" id="data_project">Project Management</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"> 
                {{ Form::open(['route' => 'biller.projects.store', 'id' => 'data_form_project']) }}   
                {{-- 
                <div class="row">
                    <fieldset class="form-group position-relative has-icon-left  col-md-4">
                        <div><label for="customer">Search Customer</label></div>
                        <select id="person" name="customer_id" class="form-control select-box" data-placeholder="{{trans('customers.customer')}}" required>
                        </select>
                    </fieldset>
                    <fieldset class="form-group position-relative has-icon-left  col-md-4">
                        <div><label for="branch">Branch</label></div>
                        <select id="branch_id" name="branch_id" class="form-control select-box" data-placeholder="Branch">
                        </select>
                    </fieldset>
                    <fieldset class="form-group col-md-4">
                        <div><label for="projectType">Project Type / Sales Account</label></div>
                        <select class="form-control select-box" name="sales_account" id="sales_account" data-placeholder="Project Type/Sales Account" required>
                            <option value="">-- Select Project Type --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account['id'] }}">
                                    {{ $account['number'] }} {{ $account['holder'] }}
                                </option>
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
                        <input type="text" class="new-todo-item form-control" placeholder="{{trans('projects.name')}}" name="name" id="project-name">
                    </fieldset>
                    <fieldset class="form-group col-4">
                        <label for="projectNumber">Project No</label>
                        <div class="input-group">
                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                            <input type="text" value="{{ gen4tid('Prj-', @$last_tid+1) }}"  class="form-control" disabled>
                            <input type="hidden" name="tid" value="{{ $last_tid+1 }}">
                        </div>
                    </fieldset>
                </div>

                <div><label for="Description">Description</label></div>                
                <fieldset class="form-group">
                    <textarea class="new-todo-item form-control" placeholder="{{trans('tasks.description')}}" rows="6" name="note"></textarea>
                </fieldset>
                 --}} 
                 @include('focus.projects.form-main')

                <div class="modal-footer">
                    <button type="submit" class="btn btn-info" id="submit-data_project">
                        Create Project
                    </button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>