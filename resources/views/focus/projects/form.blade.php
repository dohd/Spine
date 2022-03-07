<div>
    <div class="row">
        <fieldset class="form-group position-relative has-icon-left  col-md-4">
            <div><label for="customer">Search Customer</label></div>
            <select id="person" name="customer_id" class="form-control select-box" data-placeholder="{{ trans('customers.customer') }}" required>
            </select>
        </fieldset>
        <fieldset class="form-group position-relative has-icon-left  col-md-4">
            <div><label for="branch">Branch</label></div>
            <select id="branch_id" name="branch_id" class="form-control  select-box" data-placeholder="Branch">
            </select>
        </fieldset>
        <fieldset class="form-group col-md-4">
            <div><label for="projectType">Project Type / Sales Account</label></div>
            <select class="form-control select-box" name="sales_account" id="sales_account" data-placeholder="Project Type/Sales Account" required>
                <option value="">-- Select Project Type --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account['id'] }}" {{ $project->sales_account == $account->id ? 'selected' : '' }}>
                        {{ $account['code'] }} {{ $account['holder'] }}
                    </option>
                @endforeach
            </select>
        </fieldset>
    </div>

    <div class="row">
        <fieldset class="form-group position-relative has-icon-right  col-md-6">
            <div><label for="quote">Primary / Main Quote</label></div>
            <select required id="main_quote" name="main_quote" class="form-control required select-box" data-placeholder="Primary / Main Quote">
            </select>
        </fieldset>
        <fieldset class="form-group position-relative has-icon-right  col-md-6">
            <div><label for="quote">Secondary / Other Quotes</label></div>
            <select multiple id="other_quote" name="other_quote[]" class="form-control required select-box" data-placeholder="Seconday / Other Quotes">
            </select>
        </fieldset>
    </div>
    
    <div class="row">
        <fieldset class="form-group col-8">
            <div><label for="projectTitle">Project Title</label></div>
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans('projects.name')]) }}
        </fieldset>
        <fieldset class="form-group col-4">
            <div><label for="projectNumber">Project No</label></div>
            <div class="input-group">
                <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                {{ Form::text('tid', 'Prj-'.sprintf('%04d', $project->tid), ['class' => 'form-control', 'disabled']) }}
            </div>
        </fieldset>
    </div>

    <div><label for="Description">Description</label></div>
    <fieldset class="form-group">
        {{ Form::textarea('note', $project->note, ['class' => 'form-control', 'rows' => 6]) }}
    </fieldset>
</div>
