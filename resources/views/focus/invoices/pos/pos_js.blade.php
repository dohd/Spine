<script type="text/javascript">
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {
            autoHide: true,
            format: "{{ config('core.user_date_format') }}"
        }
    };

    $.ajaxSetup(config.ajax);
    $('[data-toggle="datepicker"]').datepicker(config.date);
    $('[data-toggle="datepicker"]').datepicker('setDate', new Date());

    $('form input').keydown(function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    $('#keyword').keyup(delay(function (e) {
        if (this.value.length > 2) load_pos($(this).val());
    }, 500));

    $(document).on('click', '.payment_row_add', function (e) {
        $("#amount_row").append($("#payment_row").clone());
        update_pay_pos();
    });

    $(document).on('change', '#s_warehouses, #s_category', function (e) {
        load_pos();
    });

    /**
     * Payment Modal Shown
    */
    $("#pos_payment").on("show.bs.modal", function () {
        $('.p_amount').val($('#invoiceyoghtml').val());
        update_pay_pos();

        // on click pay later
        $('#is_future_pay').val('');
        $('#pos_future_pay').click(function() {
            $('#is_future_pay').val(1);
            $('#pos_basic_pay').click();
        });
    });
    
    
    function update_pay_pos() {
        var am_pos = 0;
        $('.p_amount').each(function() {
            if (this.value > 0) 
                am_pos = am_pos + accounting.unformat(this.value, accounting.settings.number.decimal);
        });

        var ttl_pos = accounting.unformat($('#invoiceyoghtml').val(), accounting.settings.number.decimal);
        <?php
            $round_off = false;
            if ($round_off == 'PHP_ROUND_HALF_UP') {
                echo ' ttl_pos=Math.ceil(ttl_pos);';
            } elseif ($round_off == 'PHP_ROUND_HALF_DOWN') {
                echo ' ttl_pos=Math.floor(ttl_pos);';
            }
        ?>

        var due = parseFloat(ttl_pos - am_pos).toFixed(2);
        if (due >= 0) {
            $('#balance1').val(accounting.formatNumber(due));
            $('#change_p').val(0);
        } else {
            due = due * (-1)
            $('#balance1').val(0);
            $('#change_p').val(accounting.formatNumber(due));
        }
    }

    // After form submit callback 
    function trigger(data) {
        // print receipt ajax call
        $.ajax({
            url: "{{ route('biller.pos.browser_print') }}",
            dataType: "html",
            method: 'get',
            data: {invoice_id: data.invoice.id},
            success: res => {
                // toggle receipt modal
                $('#print_section').html(res);
                $('#pos_print').modal('toggle');
                $("#print_section").printThis({
                    // beforePrint: function (e) {$('#pos_print').modal('hide');},
                    printDelay: 500,
                    afterPrint: null
                });
            }
        });
    }

    @php
        $pmt= payment_methods();
        array_push($pmt, "Change");
    @endphp
    function loadRegister(show = true) {
        $.ajax({
            url: '{{route('biller.register.load')}}',
            dataType: "json",
            method: 'get',
            success: function (data) {
                $('#register_items').html('@foreach($pmt as $row)<div class="col-6"><div class="form-group  text-bold-600 green"><label for="' + data.pm_{{$loop->iteration}}+ '">{{$row}}</label><input type="text" class="form-control green" id="' + data.pm_{{$loop->iteration}}+ '" value="' + data.pm_{{$loop->iteration}}+ '" readonly="" ></div></div>@endforeach');
                $('#r_date').html(data.open)
            }
        });
        if (show) $('#pos_register').modal('toggle');
    }

    function print_it() {
        $("#print_section").printThis({
            printDelay: 333,
            afterPrint: null,
        });
    }
</script>