<div class="tab-pane" id="tab_data5" aria-labelledby="tab5" role="tabpanel">
    {{-- @if(project_access($project->id)) --}}
        <div class="card-body">
            <div class="row mt-2">
                <div class="col-12">
                    <p class="lead">{{trans('general.attachment')}}</p>
                    <pre>{{trans('general.allowed')}}:   {{@$features['value1']}} </pre>
                    <!-- The fileinput-button span is used to style the file input field as button -->
                    <div class="btn btn-success fileinput-button display-block col-4">
                        <i class="glyphicon glyphicon-plus"></i>
                        <span>Select files...</span>
                        <!-- input target for the file upload widget -->
                        <input id="fileupload" type="file" name="files">
                    </div>
                </div>
            </div>
        </div>
    {{-- @endif --}}
    <table id="files" class="files table table-striped mt-2">
        @foreach($project->attachment as $row)
            <tr>
                <td width="5%">
                    <a href="{{ route('biller.project_attachment') }}?op=delete&meta_id={{ $row['id'] }}" class="file-del red">
                        <i class="btn-sm fa fa-trash"></i>
                    </a> 
                </td>
                <td>
                    <a href="{{ asset('storage/app/public/files/' . $row['value']) }}" target="_blank" class="purple">
                        <i class="btn-sm fa fa-eye"></i> {{ $row['value'] }}
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
</div>