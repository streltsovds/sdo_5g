<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/materials-recent/materials-recent.css') )
                       ->appendStylesheet( $this->serverUrl('/css/content-modules/material-icons.css') ); ?>
<div class="materials-recent">
    <?php if(count($this->materials) > 0): ?>
    <!--h4><?= _("Последние открытые"); ?>:</h4-->
    <ul>
        <?php foreach($this->materials as $lesson):?>
            <?php $lessonAttribs = array(
                    'href' => $this->url($lesson->getFreeModeUrlParam()));
                    if($lesson->getType()==HM_Event_EventModel::TYPE_COURSE) $lessonAttribs['target']=$lesson->isNewWindow();
            ?>
        <li class="material"><a <?php echo $this->HtmlAttribs($lessonAttribs)?> class="material-icon-small <?= $lesson->material->getIconClass();?>"></a><a  <?php echo $this->HtmlAttribs($lessonAttribs)?>><?= isset($lesson->material->title)?$lesson->material->title:$lesson->title;?></a></li>
        <?php endforeach;?>
    </ul>
    <div class="materials-all"><a href="<?= $this->serverUrl('/subject/materials/index/subject_id/'.$this->subject->subid);?>" class="l-bgc"><?= _('Все материалы');?></a></div>
    <?php else:?>
    <p><?= _('В данном курсе нет материалов, открытых для свободного доступа.'); ?></p>
    <?php endif;?>
</div>
