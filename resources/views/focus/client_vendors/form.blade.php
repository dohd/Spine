<div class="row">
    <div class="col-12">
        <div class="form-group">
            {{ Form::label('company', 'Company',['class' => 'col-lg-2 control-label']) }}
            <div class='col'>
                {{ Form::text('company', null, ['class' => 'form-control', 'placeholder' => 'Company', 'required' => 'required']) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('name', 'Supplier Name',['class' => 'col-lg-2 control-label']) }}
            <div class='col'>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Supplier Name', 'required' => 'required']) }}
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('phone', 'Phone',['class' => 'col-lg-2 control-label']) }}
                    <div class='col'>
                        {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Phone', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('email', 'Email',['class' => 'col-lg-2 control-label']) }}
                    <div class='col'>
                        {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Email', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('address', 'Street Address',['class' => 'col-12 control-label']) }}
                    <div class='col'>
                        {{ Form::text('address', null, ['class' => 'form-control', 'placeholder' => 'Street Address', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('postbox', 'Post Box',['class' => 'col-12 control-label']) }}
                    <div class='col'>
                        {{ Form::text('postbox', null, ['class' => 'form-control', 'placeholder' => 'Post Box', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <h6 class="mb-2">User Info</h6>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('first_name', 'First Name',['class' => 'col-12 control-label']) }}
                    <div class='col'>
                        {{ Form::text('first_name', @$user->first_name, ['class' => 'form-control', 'placeholder' => 'First Name', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('last_name', 'Last Name',['class' => 'col-12 control-label']) }}
                    <div class='col'>
                        {{ Form::text('last_name', @$user->last_name, ['class' => 'form-control', 'placeholder' => 'Last Name', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('password', 'Password',['class' => 'col-lg-2 control-label']) }}
            <div class='col'>
                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => 'Password']) }}
            </div>
        </div>
    </div>
</div>


