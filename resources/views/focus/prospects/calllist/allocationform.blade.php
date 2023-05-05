<div class="form-group row">
    <div class="col-2">
        <label for="month">Monthly Calendar Days</label>

       
        <input type="hidden" id="callId" name="callId" value={{ $id }}>
        <select name="month" id="month" class="custom-select">
            <option value="">Choose Month</option>
            @foreach (range(1, 12) as $v)
                @php $dsp = in_array($v, [$start,$end]) ?: 'd-none' @endphp
                <option value="{{ $v }}" class="{{ $dsp }}">
                    {{ DateTime::createFromFormat('!m', $v)->format('F') }}
                </option>
            @endforeach
        </select>
        {{ Form::text('day', null, ['class' => 'form-control mt-1', 'placeholder' => 'call day', 'id' => 'day', 'required']) }}
    </div>
    <div class="col-3">
        <h5>{{ $daterange }}</h5>
    </div>
    <div class="col-7">
        <h3 class="calendar-title text-center font-weight-bold"></h3>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <div class="table-responsive">
            <table id="weeksTbl" class="table table-bordered text-center">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <div class="table-responsive">
            <table id="prospectTbl" class="table tfr my_stripe_single text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Industry</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Region</th>
                        <th>Call Status</th>


                    </tr>
                </thead>
                <tbody>
                    <!-- row template -->
                    <tr>
                        <td class="index"></td>
                        <td class="title"></td>
                        <td class="company"></td>
                        <td class="industry"></td>
                        <td class="name"></td>
                        <td class="email"></td>
                        <td class="phone"></td>
                        <td class="region"></td>
                        <td class="status"></td>
                        <td class="calldate"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.calllists.index') }}" class="btn btn-danger block">Cancel</a>
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$prospect ? 'Update' : 'Generate', ['class' => 'form-control btn btn-primary text-white hidden']) }}
    </div>
</div>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {
            defaultProspectRows: '',
            rowTemplate: '',

            init() {
                $.ajaxSetup(config.ajax);
                Index.defaultProspectRows = $('#prospectTbl tbody').html().replace(/class="hidden"/g, '');

                $('#weeksTbl').on('click', '.day-btn', this.dayBtnClick);

                $('#month').change(this.monthChange).trigger('change');
                $('#day').focus(() => alert('Please, click on day from calendar!'));

                Index.rowTemplate = $('#prospectTbl tbody').html();
                $('#prospectTbl tbody tr:first').remove();
            },



            dayBtnClick() {
                const day = $(this).text();
                const monthLabel = $('#month option:selected').text().replace(/\s+/g, '');
                $('.calendar-title').text(`Prospects for ${monthLabel}, day ${day}`);
                $('#day').val(day);
                $('input:submit').removeClass('hidden');
                Index.loadCallListProspects();
                //Index.callCount();
            },

            monthChange() {
                $('.calendar-title').text('');
                $('#day').val('');
                $('input:submit').addClass('hidden');

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
                }, [
                    []
                ]);

                const rows = Index.loadWeekRow(weeks);
                $('#weeksTbl tbody').html('').append(rows);
                
            },

            // callCount() {
            //     const day = $('#day').val();
            //     const month = $('#month').val();
            //     const id = $('#callId').val();
            //     const url = "{{ route('biller.calllists.prospectviacalllist') }}";
            //     $.post(url, {
            //         day,
            //         month,
            //         id
            //     }, data => {
            //         console.log(data.length);
                   

            //         $('#weeksTbl').find('td').each(function() {
            //             const td = $(this);
            //             let count = 0;
            //             const monthDay = td.find('.day-btn').text();
            //             data.forEach((v,i) => {
                           
            //                 if (v.prospect.call_status == 0) count++;
            //             });
            //             if (count) td.find('.call-ratio').text(`${count}/${data.length}`);

            //         });
            //     });
            // },

            loadWeekRow(weeks = []) {
                const trList = [];
                weeks.forEach(week => {
                    const tdList = [];
                    week.forEach(day => {
                        const td = `
                        <td>
                            <span class="day-btn btn btn-primary round">${day}</span>
                            <sub class="call-ratio text-success pl-1"></sub>
                        </td>
                    `;
                        tdList.push(td);
                    });
                    trList.push(`<tr>${tdList.join('')}</tr>`)
                });
                return trList.join('');
            },

            loadCallListProspects() {
                const day = $('#day').val();
                const month = $('#month').val();
                const id = $('#callId').val();
                $('#prospectTbl tbody').html('').append(Index.defaultProspectRows);

                const url = "{{ route('biller.calllists.prospectviacalllist') }}";
                $.post(url, {
                    day,
                    month,
                    id
                }, data => {

                    $('#prospectTbl tbody').html('');
                    data.forEach((v, i) => {

                        $('#prospectTbl tbody').append(Index.rowTemplate);
                        row = $('#prospectTbl tbody tr:last');
                        status = '';
                        if(v.prospect.call_status == 'notcalled'){
                            status = 'Not Called';
                        }else if (v.prospect.call_status == 'callednotpicked'){
                            status = 'Called Not Picked';
                        }else{
                            status = 'Called';
                        }
                        row.find('.index').text(i + 1);
                        row.find('.title').text(v.prospect.title);
                        row.find('.company').text(v.prospect.company);
                        row.find('.industry').text(v.prospect.industry);
                        row.find('.name').text(v.prospect.contact_person);
                        row.find('.email').text(v.prospect.email);
                        row.find('.phone').text(v.prospect.phone);
                        row.find('.region').text(v.prospect.region);
                        row.find('.status').text(status);
                    });
                })
            },
        };

        $(() => Index.init());
    </script>
@endsection
