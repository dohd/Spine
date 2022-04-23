<div class="modal fade" id="skillModal" tabindex="-1" role="dialog" aria-labelledby="skillModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Skilled Labour</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="skillTbl" class="table-responsive tfr my_stripe_single">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th width="20%" class="text-center">Skill Type</th>
                            <th width="15%" class="text-center">Charge</th>
                            <th width="15%" class="text-center">Time/hr</th>
                            <th width="15%" class="text-center">Technicians</th> 
                            <th width="15%" class="text-center">Amount</th>
                            <th width="10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select class="form-control type" name="skill[]" id="skill-0" required>
                                    <option value="">-- Select Skill --</option>                        
                                    <option value="casual">Casual</option>
                                    <option value="contract">Contract</option>
                                    <option value="outsourced">Outsourced</option>
                                </select>
                            </td>
                            <td><input type="number" class="form-control chrg" name="charge[]" id="charge-0" required readonly></td>
                            <td><input type="number" class="form-control hrs" name="hours[]" id="hours-0" required></td>               
                            <td><input type="number" class="form-control tech" name="no_technician[]" id="notech-0" required></td>
                            <td class="text-center"><span class="amount">0</span></td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm rem"><i class="fa fa-trash"></i></button></td>
                            <input type="hidden" name="skill_id[]" id="skillid-0">
                        </tr>  
                    </tbody>
                </table>                
                <div class="row">
                    <div class="col-2 ml-auto">
                        <label for="total">Total (Ksh.)</label>
                        <input type="text" class="form-control" id="skill_ttl" readonly>
                    </div>
                </div>         
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="addRow"><i class="fa fa-plus-square"></i> Add Row</button>
            </div>
        </div>
    </div>
</div>