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
                    <th>Hours</th>
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
                            <td><input type="text" class="form-control day col round" value="{{$day}}" name="day[]" placeholder="eg. Monday" id="day-0">
                            </td>
                            
                            <td><input type="time" class="form-control clock_in" name="clock_in[]" id="clock_in-0"></td>
                            <td><input type="number" class="form-control hours" onchange="handleChange(this);" name="hours[]" id="hours-0"></td>
                            <td><input type="time" class="form-control clock_out" name="clock_out[]" id="clock_out-0"></td>
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    @endforeach

                    @isset ($workshift_items)
                    @php ($i = 0)
                    @foreach ($workshift_items as $item)
                        @if ($item)
                            <tr>
                                <td>
                                    <select class="form-control day col round day" name="weekday[]" id="day-{{$i}}">
                                        <option value="{{$item->weekday}}">{{$item->weekday}}</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                        <option value="Sunday">Sunday</option>
                                    </select>
                                </td>
                                <td><input type="time" class="form-control clock_in" name="clock_in[]" value="{{$item->clock_in}}" id="clock_in-{{$i}}}"></td>
                                <td><input type="number" class="form-control hours" onchange="handleChange(this);" name="hours[]" value="{{$item->hours}}" id="hours-{{$i}}"></td>
                                <td><input type="time" class="form-control clock_out" name="clock_out[]" value="{{$item->clock_out}}" id="clock_out-{{$i}}"></td>
                                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-trash"></i></button></td>
                                <input type="hidden" class="id" name="id[]" value="{{$item->id}}" id="id-{{$i}}">
                                
                            </tr>
                            @php ($i++)
                        @endif
                    @endforeach
                @endisset
                </tr>
            </tbody>
        </table>
    </div>
    <div class="form-group row">
        <div class="col-2 ml-2">
            <button type="button" class="btn btn-success" id="addtool">Add Days</button>
        </div>
    </div>
</div>
</div>