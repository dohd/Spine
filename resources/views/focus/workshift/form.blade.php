<div class="card">
   <div class="card-content">
    <div class="card-body">
        <div class='form-group'>
            {{ Form::label( 'name','WorkShift Name',['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-10'>
                {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' =>'WorkShift Name','id'=>'name']) }}
            </div>
        </div>
        
    </div>
    <div class="table-responsive">        
        <table id="itemTbl" class="table">
            <thead>
                <tr class="bg-gradient-directional-blue white round">
                    <th width="40%">Day of Week</th>
                    <th>Clock In</th>
                    {{-- <th>Hours</th> --}}
                    <th>Clock Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $days = ['Monday', 'Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                @endphp
                <tr>
                        @foreach ($days as $day)
                            <tr>
                                <td><input type="text" class="form-control day col round" value="{{$day}}" name="weekday[]" placeholder="eg. Monday" id="day-0">
                                </td>
                                
                                <td><input type="time" class="form-control clock_in" name="clock_in[]" id="clock_in-0"></td>
                                {{-- <td><input type="number" class="form-control hours" onchange="handleChange(this);" name="hours[]" id="hours-0"></td> --}}
                                <td><input type="time" class="form-control clock_out" name="clock_out[]" id="clock_out-0"></td>
                                <td><input type="checkbox" class="form-control remove" value="1" name="is_checked[]" id="">
                                </td>
                                <input type="hidden" class="status" name="status[]" id="status-0">
                                
                            </tr>
                        @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>