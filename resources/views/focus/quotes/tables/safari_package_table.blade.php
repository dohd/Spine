<table id="quoteTbl" class="table-responsive pb-5 tfr my_stripe_single text-center safari_package_tbl">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th width="12%">Transport</th>
            <th width="18%">Hotel</th>
            <th width="6%">Pax No.</th>
            <th width="10%">Date In</th>
            <th width="10%">Date Out</th>
            <th width="15%">Description</th>
            <th>Cost</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <select class="custom-select transp" name="transp[]">
                    <option value="">-- Transport --</option>
                    @foreach (['FLIGHT', 'SAFARI VAN', 'SAFARI CRUISER', 'TRAIN', 'SELF DRIVE'] as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            </td> 
            <td>
                <select class="custom-select hotel" name="tax_rate[]">
                    <option value="">-- Select Hotel --</option>
                    @foreach ([] as $item)
                        <option value="{{ '' }}">{{ '' }}</option>
                    @endforeach
                </select>
            </td> 
            <td><input type="number" name="adult_pax[]" class="form-control adult_pax"></td>
            <td><input type="number" name="child_pax[]" class="form-control child_pax"></td>
            <td><input type="text" name="date_in[]" class="form-control datepicker date_in"></td>
            <td><input type="text" name="date_out[]" class="form-control datepicker date_out"></td>
            <td><textarea name="descr[]" cols="30" rows="2" class="form-control descr"></textarea></td>
            <td>
                <div class="row no-gutters">
                    <div class="col-12">
                        <input type="text" class="form-control cost" name="cost[]">
                    </div>
                </div>
            </td>
            <td><button type="button" class="btn btn-info rem">Remove</button></td>
        </tr>
    </tbody>
</table>
