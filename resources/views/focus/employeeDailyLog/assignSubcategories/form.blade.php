<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>


<div class="row mb-2">


    <div class="col-10 col-lg-4">
        <label for="name" class="mt-2">Employee</label>
        <input type="text" id="name" name="name" value="{{ $employee['details']['first_name'] . ' ' . $employee['details']['last_name'] }}" readonly class="form-control box-size mb-2">
        <input type="text" id="employeeId" name="employeeId" value="{{ $employee['details']['id'] }}" readonly hidden="" class="form-control box-size mb-2">
    </div>

    <div class="col-10 col-lg-4">
        <label for="department" class="mt-2">Department</label>
        <input type="text" id="department" name="department" value="{{ $employee['department'] }}" readonly class="form-control box-size mb-2">
    </div>

</div>

<div class="mb-4">
    @php
        $i = 1;
    @endphp

@foreach($departments as $dept)

        <h3 class="font-weight-bolder mb-2 mt-2">{{ $dept['name'] }}</h3>

        <div class="mb-1">
        <input type="checkbox"
               id="{{$dept['id']}}master"
               style="width: 16px; height: 16px;"
               class="round {{$dept['id']}}master"
        >
        <label for="{{$dept['id']}}master"> Allocate All '{{ $dept['name'] }}' Task Categories </label>
        </div>

        @if(!empty($deptEdlSubcategories[$dept['name']]))
            <div class="row">
                @foreach($deptEdlSubcategories[$dept['name']] as $deptSubcat)

                    <div class="col-10 col-lg-6 custom-control custom-checkbox mb-1">
                        <input type="checkbox"
                               id="{{ $deptSubcat['id'] }}"
                               name="{{ $deptSubcat['id'] }}"
                               value="{{ $deptSubcat['id'] }}"
                               style="width: 16px; height: 16px;"
                               class="round {{$dept['id']}}child"
                               @if(in_array($deptSubcat['id'], $allocations)) checked @endif
                        >
                        <label for="{{ $deptSubcat['id'] }}"> {{ $deptSubcat['name'] }} </label>
                    </div>

                    @php
                        $i++;
                    @endphp
                @endforeach
            </div>
        @else

            <div class="ml-2">
                <p>No Categories Created for the {{ $dept['name'] }} Department</p>
                <div class="media">
                    <a href="{{ route('biller.employee-task-subcategories.create') }}" class="btn btn-dropbox round"> Create</a>
                </div>
            </div>

        @endif

    @endforeach
</div>

<script>

    $(document).ready(function () {

        const departments = @json($departments);

        departments.forEach(function(dept, index) {

            $('.' + dept.id + 'master').change(function () {
                var isChecked = $(this).prop('checked');
                $('.' + dept.id + 'child').prop('checked', isChecked);
            });


        });



    });

</script>

