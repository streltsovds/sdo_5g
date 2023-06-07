<?php echo $this->form?>
<?php $this->inlineScript()->captureStart();?>
$(function(){
    var oldDate = {
        beginDate: $("#begin").val(),
        endDate: $("#end").val()
    }
    $("#projects").bind('submit',function(e){
        if($("input[name='period']:checked").val()!=0) return;
        var begin = $('#begin').val(),
            end     = $('#end').val();
        if(oldDate.beginDate!=begin||oldDate.endDate!=end){
            if(!confirm("<?php echo _('При изменении времени начала/окончания конкурса изменятся все даты мероприятий, которые вышли за окончание конкурса. Продолжить?')?>")){
                $('#begin').val(oldDate.beginDate);
                $('#end').val(oldDate.endDate);
                return false;
            }
        }
    })

    function updateInputs() {
        if ($('#auto_mark').attr('checked')) {
            val = ($('#scale_id').val() == <?php echo HM_Scale_ScaleModel::TYPE_CONTINUOUS?>)
            $('#formula_id').attr('disabled', !val);
            $('#threshold').attr('disabled', val);    
        } else {
            $('#formula_id').attr('disabled', true);
            $('#threshold').attr('disabled', true);
        }
    }
    
    $('#auto_mark').change(function(){
        updateInputs();
    });
    
    $('#scale_id').change(function(){
        $('#auto_mark').attr('checked', false);
        $('#formula_id').attr('disabled', true);
        $('#threshold').attr('disabled', true);
    }); 
    
    updateInputs();
    
})
<?php $this->inlineScript()->captureEnd();?>