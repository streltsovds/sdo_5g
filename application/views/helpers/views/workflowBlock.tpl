<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/workflow-block.css')); ?>
<?php if ($this->process): ?>
<div class="workflowBlock <?php if (($processAbstract = $this->process->getProcessAbstract()) && !$processAbstract->isStrict()):?>arbitrary<?php endif;?>">
    <div class="workflow_list">
        <?php foreach($this->process->getStates() as $key => $state): ?>
        <div class="workflow_item <?php echo $state->getClass(); ?>">
            <div class="workflow_item_head clearfix">
                <div class="wih_icon"></div>
                <div class="wih_title">
                    <span><?php echo $state->getTitle(); ?></span>
                    <?php /* <div class="wih_time">12.03.2012</div> */ ?>
                    <span class="hm-workflow-actions">
                    </span>
                </div>
            </div>
            <div class="workflow_item_description clearfix">
                <div class="wid_text">

                    <?php $extendedDescription = $state->getExtendedDescription(); ?>
                    <?php if ($extendedDescription): ?>
                        <?php if (trim(strip_tags($extendedDescription['comment']))): ?>
                            <div class="wid_text-comment"><?php echo nl2br($extendedDescription['comment']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($extendedDescription['files'])): ?>
                            <div class="wid_text-comment">
                                <?php foreach ($extendedDescription['files'] as $file): ?>
                                    <a href="<?php echo $file->getUrl() ?>" target="_blank"><?php echo $file->getDisplayName() ?></a>
                                    <?php if ($creator = $file->getCreator()) : ?>
                                        (<?=$creator['fio']?>)
                                    <?php endif;?>
                                    <br>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="wid_control_link">
                        <?php echo $this->getHelper("WorkflowBlock")->renderStatesList($state->getActions());?>
                    </div>
                </div>
                <div class="wid_forms">
                    <!-- намеренно отключены-->
                </div>
            </div>
        </div>
    <?php endforeach;?>
    </div>
</div>
<?php $this->inlineScript()->captureStart();?>
$('.wid_control_link_fail a').click(function(){
    return confirm('<?php echo _('Вы действительно хотите прекратить выполнение бизнес-процесса?');?>');
});


$().ready(function() {
    $('.workflowBlock').find('.workflow_list').accordion({
    header: '> .workflow_item > .workflow_item_head',
    active: $('.workflowBlock .workflow_list > .workflow_item.complete > .workflow_item_head')
    }).end();
});
<?php $this->inlineScript()->captureEnd();?>
<?php else:?>
<p><?php echo _('Бизнес-процесс не начат');?></p>
<?php endif;?>
