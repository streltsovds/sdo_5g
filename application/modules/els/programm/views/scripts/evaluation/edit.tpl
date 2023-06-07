<div class="multiforms-container">
<?php echo $this->form; ?>
</div>
<?php
$this->inlineScript()->captureStart();
?>
$(function(){
    $('.multiforms-container form').submit(function(){
        $.ajax({
          type: 'POST',
          url: '<?php echo $this->url(array('action' => 'save'));?>',
          data: $(this).serializeArray(),
          success: function(result){
              if (parseInt(result)) {
                  alert('<?php echo _('Данные сохранены успешно');?>');
              } else {
                  alert('<?php echo _('Данные не сохранены. Проверьте правильность заполнения формы и повторите попытку.');?>');
              }    
          }
        });        
        return false;
    });
    
    $('#criteria').on('change', function(){
        showHideCriteria();
    });

    function showHideCriteria() {
        $('select#criteria option').each(function(){

            var criterionId = $(this).attr('value');
            var select = $('select#criterion_' + criterionId); 
            var display = $(this).attr('selected') ? '' : 'none'; 
            
            $('dt#criterion_' + criterionId + '-label').css('display', display);
            select.css('display', display);
            if (display == 'none') {
                select.val(0);
            }
        });
    }
    
});
<?php
$this->inlineScript()->captureEnd();
?>