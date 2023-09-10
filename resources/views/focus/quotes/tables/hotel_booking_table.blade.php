<table id="quoteTbl" class="table-responsive pb-5 tfr my_stripe_single text-center hotel_booking_tbl">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th width="20%">Hotel</th>
            <th width="8%">Adult Pax</th>
            <th width="8%">Child Pax</th>
            <th width="10%">Date In</th>
            <th width="10%">Date Out</th>
            <th width="20%">Description</th>
            <th>Cost</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
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
