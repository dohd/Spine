<legend>Contract Properties</legend>
<hr>
<div class="form-group row">
    <div class="col-2">
        <label for="contract_no">Contract No</label>
        {{ Form::text('tid', @$last_tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-4">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control" required></select>
    </div>
    <div class="col-6">
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="start_date">Start Date</label>
        {{ Form::text('start_date', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-2">
        <label for="start_date">End Date</label>
        {{ Form::text('end_date', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-2">
        <label for="amount">Amount</label>
        {{ Form::text('amount', '0.00', ['class' => 'form-control']) }}
    </div>
    <div class="col-2">
        <label for="period">Duration (Years)</label>
        {{ Form::number('period', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-2">
        <label for="period">Duration per Schedule (months)</label>
        {{ Form::number('schedule_period', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-6">
        <label for="description">Description</label>
        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) }}
    </div>
</div>

<legend>Task Schedules</legend>
<div class="form-group row">
    <div class="col-8">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (array_fill(0, 4, 0) as $i => $v)
                        <tr>
                            <td>Q{{ $i+1 }}</td>
                            <td>01-06-2022</td>
                            <td>01-09-2022</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<legend>Contract Machines</legend>
<div class="table-responsive mb-1">
    <table class="table">
        <thead>
            <tr>
                <th>Branch</th>
                <th>Location</th>
                <th>Type</th>
                <th>Serial No</th>
            </tr>
        </thead>
        <tbody>
            @foreach (array_fill(0, 4, 0) as $i => $v)
                <tr>
                    <td>Hurlingham</td>
                    <td>Hurlingham</td>
                    <td>LG Highwall</td>
                    <td>7364858</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="form-group row">
    <div class="col-11">
        {{ Form::submit('Generate', ['class' => 'btn btn-primary float-right btn-lg']) }}
    </div>
</div>


@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});


</script>
@endsection