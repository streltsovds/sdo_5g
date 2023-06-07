<?php echo $this->grid;?>

<?php
$this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/workflow.css'));
$this->inlineScript()->captureStart();
?>
$(function(){
$(".els-grid").undelegate(".grid-workflow","click.workflow-toggler").delegate(".grid-workflow","click.workflow-toggler",function(e){
$(".workflow").remove();

var param = "index=" + $(this).data("workflow_id")
$.ajax({
type: "POST",
url: "<?php  echo $this->url(array('action' => 'workflow'));?>",
data: param,
error: function( msg ) {
				//console.log( "ERROR: " + msg );
},
success:function(msg){
(function(e){
var curPos = $(e.currentTarget).offset();
$('body')
.append(msg)
$('.workflow').css({
left: curPos.left+$(e.currentTarget).width(),
top: (curPos.top+($(e.currentTarget).height()/2))-85
});
})(e)
}
})
})
$(document).undelegate(".workflow","click.workflow-toggler").delegate(".workflow","click.workflow-toggler",function(e){
var currentTarget = $(e.target);
if(currentTarget.is(".wih_title")){
var desc = currentTarget
.closest(".workflow_item_head")
.next(".workflow_item_description")
if(desc.is(":visible")){
desc.slideToggle()
return
}
$(".workflow_item_description:visible").slideToggle()
desc.slideToggle()
return
}else{
if($(currentTarget).closest(".workflow_item_description:visible").length<1){
$(".workflow_item_description:visible").slideToggle()
}
if(currentTarget.is(".close")||$(currentTarget).closest(".workflow").length<1){
$(".workflow").remove();
}
}
})
})


$(window).delegate(".workflow-select","change",function(e){
    var currentTarget = $(e.target);
    if (confirm('Вы действительно желаете изменить статус кандидата?')) {
        $.post("<?php echo $this->url(array('action' => 'update-state')); ?>", currentTarget.closest("form").serialize(), closeAndRefresh);
    }
});

function closeAndRefresh(){
    $(".workflow").remove();
    top.location.href = top.location.href;
}

<?php
$this->inlineScript()->captureEnd();
?>
