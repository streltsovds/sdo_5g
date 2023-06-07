<?php if ($this->detailed): ?>
    <div class="at-form-report">
        <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
        <div class="report-summary clearfix">
            <div class="left-block">
                <?php $cardData = $this->cards['Fulltime1']; ?>
                <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
                <?php echo $this->reportList($cardData['fields']);?>
            </div>
        </div>
        <!--div class="report-summary clearfix">
        <div class="left-block ">
            <?php $cardData = $this->cards['Fulltime2']; ?>
            <h2>Иконка<?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <img src="<?=$this->icon;?>"><br><br>
            <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($cardData['fields']);?>
        </div>
    </div-->
        <div class="report-summary clearfix">
            <div class="left-block">
                <?php $cardData = $this->cards['Fulltime3']; ?>
                <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
                <?php echo $this->reportList($cardData['fields']);?>

                <?php $cardData = $this->cards['Fulltime4']; ?>
                <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
                <?php echo $this->reportList($cardData['fields']);?>
            </div>
        </div>
        <div class="report-summary clearfix">
            <div class="left-block ">
                <?php $cardData = $this->cards['classifiers']; ?>
                <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
                <?php echo $this->reportList($cardData['fields']);?>
            </div>
        </div>
        <?php $cardData = $this->cards['Fulltime_teachers']; ?>
        <h2><?php echo $cardData['title'];?><?php if ($cardData['edit']) : ?><a class="edit" href="<?php echo $cardData['edit']?>" class="edit">&nbsp;</a><?php endif; ?></h2>
        <div class="clearfix">
            <?php echo $this->reportTable($cardData['fields']);?>
        </div>

    </div>
<?php else: ?>
    <div class="tmc-subject">
        <div class="tmc-subject-leftside">

            <?php $fromProgram = in_array($this->subject->subid, $this->fromProgramArray); ?>
            <?php echo $this->partial('list/card.tpl', null, array('subject' => $this->subject, 'graduated' => $this->graduated, 'teachers' => $this->teachers, 'fromProgram' => $fromProgram));?>

            <?php if ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL): ?>
                <?php

                $container = Zend_Registry::get('serviceContainer');
                if ( in_array($container->getService('User')->getCurrentUserRole(),
                    array(HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        HM_Role_Abstract_RoleModel::ROLE_DEAN))):
                    $confirmMsg = array(
                        HM_Subject_SubjectModel::STATE_ACTUAL => _('Вы уверены, что хотите начать обучение на курсе? В этот момент будут отправлены уведомления всем участникам курса и доступ к материалам курса для них будет открыт; относительные даты занятий будут отсчитываться от этого момента.'),
                        HM_Subject_SubjectModel::STATE_CLOSED => _('Вы уверены, что хотите закончить обучение на курсе? При этом все слушатели курса будут автоматически переведены в прошедшие обучение. Дальнейшее зачисление слушателей на курс станет невозможным.')
                    );
                    $actionUrl = $this->url(array(
                        'module'     => 'subject',
                        'controller' => 'index',
                        'action'     => 'change-state',
                        'subject_id' => $this->subject->subid,
                        'state'=> ''
                    ),null,true);
                    $this->inlineScript()->captureStart();
                    ?>
                    $(document).ready(function () {

                    var confs = <?php echo HM_Json::encodeErrorSkip($confirmMsg) ?>;
                    var $subjectsetstate = $(<?php echo HM_Json::encodeErrorSkip("#$cardId"); ?>).find('select[name="subjectsetstate_new_mode"]')
                    , $tparent = $subjectsetstate.parent()
                    , currentValue = $subjectsetstate.val();

                    if ($subjectsetstate.length) {
                    $subjectsetstate
                    .selectmenu({
                    style: 'dropdown',
                    menuWidth: 170,
                    width: 170,
                    positionOptions: { collision: 'none' }
                    }).change(function () {
                    var _this = this
                    , _val  = $(_this).val();
                    if (elsHelpers.confirm != null) {
                    elsHelpers.confirm(confs[_val], <?php echo HM_Json::encodeErrorSkip(_("Смена состояния курса")) ?>).done(function () {
                    window.location = <?php echo HM_Json::encodeErrorSkip($actionUrl); ?> + (currentValue = _val);
                    }).always(function () {
                    $(_this).val(currentValue)
                    .selectmenu('value', currentValue);
                    });
                    } else {
                    if (confirm(confs[_val])) {
                    window.location = <?php echo HM_Json::encodeErrorSkip($actionUrl); ?> + (currentValue = _val);
                    }
                    $(_this).val(currentValue)
                    .selectmenu('value', currentValue);
                    }
                    });
                    }

                    });
                    <?php
                    $this->inlineScript()->captureEnd();
                endif;
                ?>
            <?php endif; // end period restriction?>

        </div>


        <div class="tmc-subject-rightside">
            <?php if (strlen(strip_tags(trim($this->subject->description)))) :?>
                <h2><?php echo _('Описание курса');?></h2>
                <hr>
                <div class="text-content">
                    <?php echo $this->subject->description?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
