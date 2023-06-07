<table>
    <tr style="width: 20%;">
        <td>
            <?php echo $this->form; ?>
        </td>
    </tr>
    <tr style="width: 80%;">
        <td>
           <div class="integration-output">

           </div>
        </td>
    </tr>
</table>

<?php $this->inlineScript()->captureStart(); ?>
$(function() {
//    $('#integration input[name=type]').on('change', function(){
//        if (parseInt($(this).val())) {
//            $('#source').removeAttr('disabled');
//        } else {
//            $('#source').attr('disabled', 'disabled');
//        }
//    });
//    $('#source').attr('disabled', 'disabled');
});
<?php $this->inlineScript()->captureEnd(); ?>