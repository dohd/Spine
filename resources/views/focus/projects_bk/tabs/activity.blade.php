
                        <div class="tab-pane" id="tab_data4" aria-labelledby="tab4" role="tabpanel">
                            @if (project_access($project->id))
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#AddLogModal">
                                    {{ trans('general.new') }}
                                </button>

                                <div class="card-body">
                                    <table id="log-table" class="table table-striped table-bordered zero-configuration"
                                        cellspacing="0" width="100%">

                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ trans('general.date') }}</th>
                                                <th>{{ trans('projects.users') }}</th>
                                                <th>{{ trans('general.description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            @endif

                        </div>