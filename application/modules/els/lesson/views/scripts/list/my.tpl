<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
?>
<div class="tmc-my-lessons"><?php echo _('План занятий'); ?></div>

<div class="tmc-mark-table">
<form id="marksheetteacher">
<?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'switcher' => 'my'));?>

<?php if ($this->markDisplay):?>
    <table class="progress_table" border="0" cellpadding="0" cellspacing="0" style="table-layout: fixed; width: 100%;">
        <col style="width: 0px;">
        <col style="width: auto;">
        <col style="width: 202px;">
        <col style="width: 170px;">
        <tr>
            <td></td>
            <td height="20" align="left" valign="middle">
                <div class="progress_title"><br><?php echo _('Прогресс прохождения плана')?></div>
            </td>
            <td height="20" valign="middle">
                <div class="progress_title tmc-go-left" style="margin-left: 5px;"><?php echo sprintf(_('Итоговая%sоценка'), ' ')?></div>
            </td>
            <td height="20" valign="middle">

            </td>
        </tr>
        <tr>
            <td></td>
            <td class="progress_td" height="27" width="470" align="center" valign="middle">
                <?php echo $this->progress($this->percent, 'xlarge')?>
            </td>
            <td>
                <?php echo $this->score(array(
                    'score' => $this->mark,
                    'user_id' => $this->forStudent,
                    'lesson_id' => 'total',
                    'scale_id' => $this->subject->getScale(),
                    'mode' => HM_View_Helper_Score::MODE_DEFAULT,
                ));?>
            </td>
            <td>
                <div class="tmc-teacher-comment" title="<?php echo _('Комментарий преподавателя')?>"></div>
            </td>
        </tr>
    </table>
<?php endif;?>

<?php if (count($this->lessons)):?>
    <?php foreach($this->lessons as $lesson):?>
        <?php if ($lesson instanceof HM_Lesson_LessonModel):?>
        <?php echo $this->lessonPreview(
            $lesson,
            $this->titles,
            $this->lessonView,
            $this->forStudent,
            $this->eventCollection,
            $this->lessonCols
        )?>
        <?php endif;?>
    <?php endforeach;?>
<?php else:?>
    <?php echo _('Отсутствуют данные для отображения')?>
<?php endif;?>

<?php if(!$this->forStudent && ($this->currentUserIsTeacher || $this->currentUserIsDean)):?>
<?php $this->inlineScript()->captureStart(); ?>
    $(function(){
        $(".lesson_bg_img").before('<span class="field-cell drag-handler"></span>')
        $('#marksheetteacher').sortable({
            tolerance: 'pointer',
            appendTo: 'body',
            handle: 'span.drag-handler',
            helper: 'clone',
            revert: true,
            update: function (event, ui) {
                var cItemSort = $.map($('#marksheetteacher').sortable("toArray"),function(item){
                    if(item.length>0) return item
                })
                $.getJSON('<?php echo $this->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'save-order'));?>', {
                    posById: cItemSort
                });
            }
        });
    })
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>

<?php if($this->currentUserIsTeacher || $this->currentUserIsDean):?>
<?php $this->inlineScript()->captureStart(); ?>
if(typeof initMarksheet=="function"){
    initMarksheet({
        url: {
            comments: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-comment'));?>",
            score: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score'));?>"
        },
        l10n: {
            save: "<?php echo _("Сохранить"); ?>",
            noStudentActionSelected: "<?php echo _("Не выбрано ни одного действия со слушателем"); ?>",
            noStudentSelected: "<?php echo _("Не выбрано ни одного слушателя"); ?>",
            noLessonActionSelected: "<?php echo _("Не выбрано ни одного действия с занятием"); ?>",
            noLessonSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
            formError: "<?php echo _("Ошибка формы") ?>",
            ok: "<?php echo _("Хорошо"); ?>",
            confirm: "<?php echo _("Подтверждение"); ?>",
            areUShure: "<?php echo _("Данное действие может быть необратимым. Вы действительно хотите продолжить?"); ?>",
            yes: "<?php echo _("Да"); ?>",
            no: "<?php echo _("Нет"); ?>"
        }
    });
}
<?php $this->inlineScript()->captureEnd(); ?>

<?php endif;?>
</div>
</form>

<?php $this->inlineScript()->captureStart(); ?>
var url2SelfStudy = '<?php echo $this->url2SelfStudy;?>';
var url2SelfStudyFinish = '<?php echo $this->url2SelfStudyFinish;?>';
var need2Popup = <?php echo $this->need2Popup;?>;

//скрипт, который отображает форму для редактирования иконки занятия
jQuery(document).ready(function(){
    var dialogContainer = $('<div id="ico-dialog"></div>');
    $('.els-iconEdit').on('click', function(e){
        //console.log('dial_'+index);
        e.preventDefault();
        dialogContainer.html('Загрузка...');
        dialogContainer.dialog({width: 470});
        dialogContainer.load($(this).attr('href'));
    });
});
jQuery(document).ready(function(){
    $(".lesson-callback").on('click','a',function(e){
    e.preventDefault();
    $('#callback-form > iframe').attr('src','/message/ajax/lesson-callback/lesson_id/'+$(e.target).data('testid'));
    $('#callback-form').dialog({width:600,
                                height:443,
                                modal:true,
                                closeOnEscape:true,
                                resizable:false
        });
    });
    
    $('#popupDialog1').dialog({width:600,
        height:230,
        modal:true,
        closeOnEscape:false,
        resizable:false,
        autoOpen : need2Popup==1,
        buttons: [
            {
              text: "Пройти самоподготовку",
              click: function() {
                    $( this ).dialog( "close" );
                    document.location.href = url2SelfStudy; 
                }
            },
        ]
    });

    $('#popupDialog2').dialog({width:600,
        height:260,
        modal:true,
        closeOnEscape:false,
        resizable:false,
        autoOpen : need2Popup==2,
        buttons: [
            {
              text: "Да, подтверждаю завершение",
              click: function() {
                    $( this ).dialog( "close" );
                    document.location.href = url2SelfStudyFinish; 
                }
            },
            {
              text: "Нет, продолжить самоподготовку",
              click: function() {
                    $( this ).dialog( "close" );
                    document.location.href = url2SelfStudy; 
                }
            },
        ]
    });


    $('.ui-dialog-titlebar-close').hide();
});
<?php $this->inlineScript()->captureEnd(); ?>

<div id="callback-form" style="display:none;">
    <iframe style="height: 100%;width:100%;" src="/message/ajax/lesson-callback/lesson_id/">
    </iframe>
</div>

<div id=popupDialog1  style="display:none;" title='Самоподготовка'>Для допуска к экзамену Вам необходимо предварительно пройти занятие <u style='cursor:pointer' onClick='$("#popupDialog1").dialog( "close" ); document.location.href = url2SelfStudy; '><?php echo $this->lessonTitle;?></u>. <br>

</div>
<div id=popupDialog2  style="display:none;" title='Самоподготовка'>Для допуска к экзамену Вам необходимо предварительно пройти занятие <u style='cursor:pointer' onClick='$("#popupDialog2").dialog( "close" ); document.location.href = url2SelfStudy; '><?php echo $this->lessonTitle;?></u>. <br>
    Вы запускали данное занятие <?php echo $this->runsCount;?> раз. Пожалуйста подтвердите завершение самоподготовки.
</div>






