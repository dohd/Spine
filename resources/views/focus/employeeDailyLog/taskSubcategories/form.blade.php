<head>
    <script src="https://cdn.tiny.cloud/1/4do69iegy8gdo10luhw47dgizr05tlp11q66osjli88uaf6e/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>


<div class="row mb-2">

    <div class="col-10 col-lg-7">
        <label for="department">Department:</label>
        <select class="form-control box-size" id="department" name="department">
            <option value="">-- Select Department --</option>
            @foreach ($departments as $val)
                <option value="{{ $val }}">
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-10 col-lg-7">
        <label for="name" class="mt-2">Subcategory Name:</label>
        <input type="text" id="name" name="name" required class="form-control box-size mb-2">
    </div>

    <div class="col-10 col-lg-7">
        <label for="frequency">Frequency:</label>
        <select class="form-control box-size" id="frequency" name="frequency">
            <option value="">-- Select Frequency --</option>
            @foreach ($frequency as $val)
                <option value="{{ $val }}">
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>

</div>


