<div class="multiforms-container">
<?php foreach($this->forms as $form): ?>
<?php echo $form; ?>
<?php endforeach; ?>
</div>
<?php
$this->inlineScript()->captureStart();
?>
$(function(){
    $('.multiforms-container form').submit(function(){
        $.ajax({
          type: 'POST',
          url: '<?php echo $this->url(array('action' => 'save'));?>'.replace('save', 'save-' + $(this).attr('id')),
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
});
<?php
$this->inlineScript()->captureEnd();
?>