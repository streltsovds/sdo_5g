<div class="bredcrumbs_2">
	<?php if (!empty($this->panelTitle)) :?>
	<a href="<?php echo $this->url($this->panelUrl)?>" class="filled<?php if ($this->isInactive): ?>-inactive<?php endif;?>" title="<?php if ($this->isInactive) echo _('Вы не являетесь слушателем на данном курсе');?>"><?php echo _($this->panelTitle); ?></a>
	<span>›</span>
	<?php endif; ?>
	<?php if (!empty($this->pageTitle)) :?>
	<span title="<?php echo _($this->pageTitleFull); ?>"><?php echo _($this->pageTitle); ?></span>
	<?php endif; ?>
	<?php if ($this->isPinable): ?>
<!--    --><?php //if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN))): ?><!--<span class="pin-unpin--><?php //if ($this->isPinned): ?><!-- pinned--><?php //endif; ?><!--">-->
<!--        <a href="--><?php //echo $this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'pin', 'uri' => $this->pinUri));?><!--" class="unfixed"><img src="--><?//= $this->serverUrl('/images/icons/unfix.gif'); ?><!--"></a>-->
<!--        <a href="--><?php //echo $this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'unpin'));?><!--" class="fixed"><img src="--><?//= $this->serverUrl('/images/icons/fix.gif'); ?><!--"></a>-->
<!--    </span>--><?php //endif; ?>
<!--	--><?php //if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)): ?><!--<span class="pin-unpin--><?php //if ($this->isPinned): ?><!-- pinned--><?php //endif; ?><!--">-->
<!--        <a href="--><?php //echo $this->url(array('module' => 'project', 'controller' => 'index', 'action' => 'pin', 'uri' => $this->pinUri));?><!--" class="unfixed"><img src="--><?//= $this->serverUrl('/images/icons/unfix.gif'); ?><!--"></a>-->
<!--        <a href="--><?php //echo $this->url(array('module' => 'project', 'controller' => 'index', 'action' => 'unpin'));?><!--" class="fixed"><img src="--><?//= $this->serverUrl('/images/icons/fix.gif'); ?><!--"></a>-->
<!--	</span>--><?php //endif; ?>
        <?php $uri = explode('/', urldecode($this->pinUri)); ?>
        <?php $subjectIdKey = array_search('subject_id', $uri); ?>
        <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN))): ?>
            <span class="pin-unpin<?php if ($this->isPinned): ?> pinned<?php endif; ?>">
                <a href="/subject/index/pin?subject_id=<?= $uri[$subjectIdKey + 1]; ?>&uri=<?= $this->pinUri;?>" class="unfixed"><img src="<?= $this->serverUrl('/images/icons/unfix.gif'); ?>"></a>
                <a href="/subject/index/unpin?subject_id=<?= $uri[$subjectIdKey + 1]; ?>&uri=<?= $this->pinUri;?>" class="fixed"><img src="<?= $this->serverUrl('/images/icons/fix.gif'); ?>"></a>
            </span>
        <?php endif; ?>
        <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)): ?>
            <span class="pin-unpin<?php if ($this->isPinned): ?> pinned<?php endif; ?>">
                <a href="/project/index/pin?subject_id=<?= $uri[$subjectIdKey + 1]; ?>&uri=<?= $this->pinUri;?>" class="unfixed"><img src="<?= $this->serverUrl('/images/icons/unfix.gif'); ?>"></a>
                <a href="/project/index/unpin?subject_id=<?= $uri[$subjectIdKey + 1]; ?>&uri=<?= $this->pinUri;?>" class="fixed"><img src="<?= $this->serverUrl('/images/icons/fix.gif'); ?>"></a>
            </span>
        <?php endif; ?>
	<?php endif; ?>
</div>
<?php $this->inlineScript()->captureStart(); ?>
$(document.body).undelegate('.pin-unpin-page').delegate('.pin-unpin a', 'click.pin-unpin-page', function (event) {
    var $a = $(this)
      , $pinner = $a.closest('.pin-unpin')
      , xhr = $pinner.data('xhr');

    event.preventDefault();

    if (xhr != null) {
        xhr.abort();
    }
    if ($a.hasClass('unfixed')) {
        $pinner.addClass('pinned');
    } else {
        $pinner.removeClass('pinned');
    }
    xhr = $.get($a.attr('href')).done(function () {
        if ($a.hasClass('unfixed')) {
            $pinner.addClass('pinned');
        } else {
            $pinner.removeClass('pinned');
        }
    }).fail(function (__, status) {
        if ($a.hasClass('unfixed')) {
            $pinner.removeClass('pinned');
        } else {
            $pinner.addClass('pinned');
        }
    });
    $pinner.data('xhr', xhr);
});
<?php $this->inlineScript()->captureEnd(); ?>