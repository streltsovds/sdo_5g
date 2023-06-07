<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/subject.css'));?>
<?php $requests = count($this->position->reserveRequest) ? $this->position->reserveRequest->getList('user_id') : array(); ?>

<div class="clearfix" style="margin: 30px 0;">
    <div style="float: left;">
        <?php echo $this->partial('position/card.tpl', null, array('position' => $this->position));?>
    </div>
    <div class="hm-catalog-experience-item-toolbar" style="padding-left: 30px; float: left;">
        <button
            data-url="<?php echo $this->url(array('baseUrl' => 'hr', 'module' => 'reserve-request', 'controller' => 'list', 'action' => 'create-request', 'position_id' => $this->position->reserve_position_id));?>"
            <?php if (in_array($this->userId, $requests)): ?>
                disabled="disabled"
                title="<?php echo _('Вы уже подали заявку на участие в программе кадрового резерва на данную дложность.'); ?>"
            <?php endif;?>
        ><?php echo _('Подать заявку'); ?></button>
    </div>
</div>

<?php if (strlen(strip_tags(trim($this->position->requirements)))) :?>
    <h2><?php echo _('Требования к кандидатам');?></h2>
    <hr>
    <div class="text-content">
        <?php echo $this->position->requirements?>
    </div>
<?php endif; ?>

<?php if (strlen(strip_tags(trim($this->position->formation_source)))) :?>
    <h2><?php echo _('Источник формирования');?></h2>
    <hr>
    <div class="text-content">
        <?php echo $this->position->formation_source?>
    </div>
<?php endif; ?>

<?php $this->inlineScript()->captureStart(); ?>
$(function () {
    $(document).on('click', '.hm-catalog-experience-item-toolbar button', function (e){
        e.preventDefault();
        if (confirm('<?php echo _('Вы действительно желаете подать заявку на участии в программе кадрового резерва?')?>')) {
            document.location.href = $(this).data('url');
        }
        return false;
    });
})

<?php $this->inlineScript()->captureEnd(); ?>