<h1>Отклонение кандидатов</h1>
<br/>
<?php echo $this->form; ?>

<?php
$this->inlineScript()->captureStart();
?>
    $('#cancel_no_status').bind('click',function(){
        $('#result').val(-1);
    });
     $('#cancel_blacklist').bind('click',function(){
        $('#result').val(-3);
    });
     $('#cancel_reserve').bind('click',function(){
        $('#result').val(-2);
    });
     $('#prev').bind('click',function(){
        window.location.href = $('#backUrl').val();
    });
<?php
$this->inlineScript()->captureEnd();
?>