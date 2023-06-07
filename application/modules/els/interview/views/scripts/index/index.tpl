<script>
	//$("#ball").attr("disabled") = "";
 $(function() {
	$("#type").change(function(){
    	if($("#type option:selected").val() == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>)
    	{
    		$("#ball").prop("disabled",false);
    	}
    	else
    		$("#ball").prop("disabled",true);
    	}).change();
	
    $( "#dialog-confirm" ).dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?php echo _('Да')?>: function() {
				$( this ).dialog( "close" );
				$("#target").submit();
			},
			<?php echo _('Нет')?>: function() {
				$( this ).dialog( "close" );
            }
		}
	});
	$( "#interview" ).click(function() {
		if ($("#type").attr("value") == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>)
		{
			$( "#dialog-confirm" ).dialog( "open" );
			return false;
		}
	});
});
</script>
<div id="dialog-confirm" title="Подтверждение действия">
    <p><span style="float: left; margin: 0 7px 20px 0;">Вы действительно желаете выставить оценку за данное занятие? Дальнейшее добавление сообщений будет невозможно.</span></p>
</div>
<?php
$this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/test.css'));
$keyshowform = $kods = array();
foreach($this->messages as $message){
    
    if ($this->taskPreview && in_array($message->question_id, $kods)) continue; // при предпросмотре не показываем одни и те же назначенные варианты
    
    echo $this->interviewMessage($message, $this->teacher, $this->lesson, $this->mark);
	$keyshowform[] = $message->type;
	$kods[] = $message->question_id;
}
?>

<div style="margin-top: 30px;"></div>
<?php
if (!in_array(HM_Interview_InterviewModel::MESSAGE_TYPE_BALL, $keyshowform) && !$this->taskPreview)
echo $this->form;
?>
<?= $this->proctoringStudent($this->lessonId); ?>