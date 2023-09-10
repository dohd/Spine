<table id="quoteTbl" class="table-responsive pb-5 tfr my_stripe_single text-center flight_tbl">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th width="15%">Airline</th>
            <th width="10%">Flight No.</th>
            <th width="8%">Pax No.</th>
            <th width="8%">Flight Class</th>
            <th width="8%">Departure/Arrival</th>
            <th width="10%">Travel Dates</th>
            <th width="16%">Description</th>
            <th>Fare (Tax Inc)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <select class="custom-select airline" name="airline[]">
                    <option value="">-- Select Airline --</option>
                    @foreach ([] as $item)
                        <option value="{{ '' }}">{{ '' }}</option>
                    @endforeach
                </select>
            </td> 
            <td><input type="text" name="flight_no[]" class="form-control flight_no"></td>
            <td>
                <input type="text" name="adult_pax[]" class="form-control child_pax" placeholder="Adult">
                <input type="text" name="child_pax[]" class="form-control child_pax" placeholder="Child">
            </td>
            <td>
                <select class="custom-select flight_class" name="flight_class[]">
                    <option value="">-- Flight Class --</option>
                    @foreach ([] as $item)
                        <option value="{{ '' }}">{{ '' }}</option>
                    @endforeach
                </select>
            </td> 
            <td>
                <input type="text" name="depart[]" class="form-control depart" placeholder="Departure">
                <input type="text" name="arrival[]" class="form-control arrival" placeholder="Arrival">
            </td>
            <td>
                <input type="text" name="travel_date[]" class="form-control datepicker travel_dt_1">
                <input type="text" name="travel_date[]" class="form-control datepicker travel_dt_2">
            </td>
            <td><textarea name="descr[]" cols="30" rows="3" class="form-control descr"></textarea></td>
            <td>
                <div class="row no-gutters">
                    <div class="col-12">
                        <input type="text" name="fare_per_adult[]" class="form-control fpa" placeholder="Per Adult">
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-12">
                        <input type="text" name="fare_per_child[]" class="form-control fpc" placeholder="Per Child">
                    </div>
                </div>
            </td>
            <td><button type="button" class="btn btn-info rem">Remove</button></td>
        </tr>
    </tbody>
</table>
