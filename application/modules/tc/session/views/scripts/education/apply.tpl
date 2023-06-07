<style>
    fieldset select.multiselect {
        width: 1050px;
        max-width: 1050px;
        height: 200px;
    }
</style>
<?php echo $this->form?>
<script>
    if ($('select#cost_item').val() == 3 || $('select#cost_item').val() == 4) {
        $('#subject_id').attr('disabled', true);
        $('#subject_id').css('opacity', 0.6);


        $('#event_name').attr('disabled', false);
        $('#event_name').css('opacity', 1);
        $('#price').attr('disabled', false);
        $('#price').css('opacity', 1);
    }
    if ($('select#cost_item').val() == 1 || $('select#cost_item').val() == 2 || $('select#cost_item').val() == 5) {
        $('#subject_id').attr('disabled', false);
        $('#subject_id').css('opacity', 1);

        $('#event_name').attr('disabled', true);
        $('#event_name').css('opacity', 0.6);
        $('#price').attr('disabled', true);
        $('#price').css('opacity', 0.6);
    }
</script>