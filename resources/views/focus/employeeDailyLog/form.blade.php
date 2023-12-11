<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<div class="row">
    @csrf <!-- CSRF protection -->

    <div class="col-3">

        <label for="date">Date:</label>
        <input type="text" id="date" name="date" required class="datepicker form-control box-size mb-2">

    </div>
{{--    <label for="email">Email:</label>--}}
{{--    <input type="email" id="email" name="email" required class="form-control box-size">--}}


    <div class="col-12 mt-3">

        <h3>Tasks:</h3>

        <hr>

        @for($i = 0; $i < 20; $i++)
            <div class="row" id="task{{$i}}">

                <div class="col-12 mb-1">Task {{$i + 1}}</div>

                <div class="col-8">
                    <label for="subcategory{{$i}}">Category:</label>
                    @if(empty($taskCategories[0]))
                        <h5> No Task Categories Allocated to You </h5>
                    @endif
                    <select class="form-control box-size" id="subcategory{{$i}}" name="subcategory{{$i}}" >
                        <option value="">-- Select Category --</option>
                        @foreach ($taskCategories as $cat)
                            <option value="{{ $cat['value'] }}">
                                {{ array_search($cat ,$taskCategories) + 1 . '. ' . $cat['label'] . '  |  ' . $cat['frequency'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-5 col-lg-1 mt-1 mt-lg-0">
                    <label for="hours{{$i}}" >Hours:</label>
                    <input type="number" max="9" id="hours{{$i}}" name="hours{{$i}}" class="form-control box-size" step="0.01" >
                </div>

                <div class="col-12 col-lg-9 mt-1 mt-lg-1">
                    <label for="description{{$i}}">Description:</label>
                    <textarea id="description{{$i}}" name="description{{$i}}" class="form-control box-size mb-2" rows="4" ></textarea>
                </div>

                <div id="removeButton{{$i}}" class="float-right mt-4 ml-3" @if($i === 0) hidden="true" @endif>
                    <button type="button" class="btn btn-danger"> Remove </button>
                </div>

                <hr class="col-10 mt-2 ml-2">

            </div>
        @endfor

    </div>


    <button id="toggleButton" type="button" class="btn btn-secondary ml-2 mb-2">Add a Task</button>


</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">


        tinymce.init({
            selector: 'textarea',
            menubar: 'file edit view format table tools',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 230,
        });



    $(document).ready(function() {

        {{--const taskCategories = @json($taskCategories);--}}
        {{--const subcategories = @json($taskSubcategories);--}}

        // // Function to populate the task select box based on the selected department
        // function populateTasks(taskNumber) {
        //
        //     const selectedCategory = $("#category" + taskNumber).val();
        //     const subcategorySelect = $("#subcategory" + taskNumber);
        //     subcategorySelect.empty(); // Clear existing options
        //
        //     if (selectedCategory in subcategories) {
        //         const subs = subcategories[selectedCategory];
        //
        //         subcategorySelect.append(new Option('-- Select Subcategory --', ''));
        //         subs.forEach(function (task) {
        //             subcategorySelect.append(new Option(task, task));
        //         });
        //     }
        //
        // }
        //
        // // Populate the task select box initially
        // populateTasks();
        //
        // for(let i = 0; i < 20; i++ ){
        //     $("#category" + i).on("change", function() {
        //         populateTasks(i);
        //     });
        // }

        // Initially hide the textarea
        for(let i = 1; i < 20; i++ ) {
            $('#task' + i).hide();
        }

        for(let i = 1; i < 20; i++ ) {
            $('#removeButton' + i).click(function () {
                $('#task' + i).hide();

                $('#category' + i).val('');
                $('#hours' + i).val('');
                $('#description' + i).val('');
            });
        }

        // Attach a click event handler to the toggle button
        let taskNumber = 1;
        $('#toggleButton').click(function() {
            // Toggle the visibility of the textarea
            $('#task' + taskNumber).show();
            taskNumber++;
        });
    });

</script>
@endsection
