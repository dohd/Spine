<div class="form-group row">
    <div class="col-2">
        <label for="month">Monthly Calendar Days</label>
        <select name="month" id="month" class="custom-select">
            @for ($i = 1; $i < 13; $i++)
                <option value="{{ $i }}">
                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                </option>
            @endfor
        </select>
        {{ Form::hidden('day', null, ['id' => 'day']) }}
    </div>
    <div class="col-10">
        <h3 class="calendar-title text-center font-weight-bold"></h3>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <div class="table-responsive">
            <table id="weeksTbl" class="table table-bordered text-center" style="cursor:pointer;">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table tfr my_stripe_single text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th width="50%">Employee Name</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                            <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $i => $row)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                            <td><input type="time" name="clock_in[]" placeholder="HH:MM" class="form-control clock-in"></td>
                            <td><input type="time" name="clock_out[]" placeholder="HH:MM" class="form-control clock-out"></td>
                            <td>
                                <select name="status[]" class="custom-select status">
                                    @foreach (['present', 'absent', 'on_leave'] as $val)
                                        <option value="{{ $val }}">{{ ucfirst(str_replace('_', ' ', $val)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            {{ Form::hidden('employee_id[]', $row->id) }}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.attendances.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$attendance? 'Update' : 'Generate', ['class' => 'form-control btn btn-primary text-white']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {

        init() {
            $.ajaxSetup(config.ajax);
            $('#month').change(this.monthChange).trigger('change');
            $('#weeksTbl').on('click', '.day-btn', this.dayBtnClick);
        },

        dayBtnClick() {
            const day = $(this).text();
            const monthLabel = $('#month option:selected').text().replace(/\s+/g, '');
            $('.calendar-title').text(`Attendance for ${monthLabel}, day ${day}`);
            $('#day').val(day);
        },

        monthChange() {
            $('.calendar-title').text('');
            $('#day').val('');

            const monthIndx = $(this).val();
            const year = new Date().getFullYear();
            const daysInMonth = new Date(year, monthIndx, 0).getDate();
            const daysRange = [...Array(daysInMonth).keys()].map(v => v + 1);

            const weeks = daysRange.reduce((init, curr) => {
                const i = init.length - 1;
                if (curr % 7 == 0) {
                    init[i].push(curr);
                    init.push([]);
                } else init[i].push(curr);
    
                return init;
            }, [[]]);

            const tbody = Index.loadCalendar(weeks);
            $('#weeksTbl').html('').append(tbody);
            Index.loadUnmarked();
            Index.fetchDayAttendance();
        },  

        fetchDayAttendance() {
            const url = "{{ route('biller.attendances.day_attendance') }}";
            $.post(url, {month: $('#month').val()}, data => {
                console.log(data)
            });

        },

        loadCalendar(weeks = []) {
            const tbody = document.createElement('tbody');
            weeks.forEach(week => {
                const row = document.createElement('tr');
                week.forEach(day => {
                    const td = document.createElement('td');
                    const span = document.createElement('span');
                    span.innerText = day;
                    span.classList.add('day-btn', 'btn', 'btn-primary', 'round');
                    td.append(span);
                    row.append(td);
                });
                tbody.append(row);
            });
            return tbody;
        },

        loadUnmarked() {
            $('#weeksTbl tbody tr').each(function() {
                $(this).find('td').each(function() {
                    $(this).append(`<sub class="text-success pl-1">${'10/50'}</sub>`);
                })
            });
        },
    };

    $(() => Index.init());
</script>
@endsection