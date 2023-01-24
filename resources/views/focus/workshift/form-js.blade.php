@section('extra-scripts')
<script>
    $(".remove:checked").each(function() {
        const $tr = $(this).parents('tr:first');
        $tr.find('td input').prop('readonly',true);
                
    });
    // $('form').submit(function (e) { 
    //     e.preventDefault();
    //     console.log($(this).serializeArray());
    // })
    function handleChange(input) {
        var input_value = $('.hours').val();
        if (input_value < 0) input.value = 0;
        if (input_value > 24) input.value = 24;
    }

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    let tableRow = $('#itemTbl tbody tr:first').html();
    $('#itemTbl tbody tr:first').remove();
    let rowIds = 1;
     $('#addtool').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);
        $('#itemTbl tbody').append('<tr>' + html + '</tr>');
    });

    $('#itemTbl').on('click', '.remove', removeRow);
    function removeRow() {
        if ($(this).is(':checked', true)) {
                const $tr = $(this).parents('tr:first');
                $tr.find('td input').prop('readonly',true);
            } else {
                const $tr = $(this).parents('tr:first');
                $tr.find('td input').prop('readonly',false);
            }
        
        // $tr.next().remove();
        // $tr.remove();
    }
</script>
@endsection