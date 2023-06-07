<?php echo $this->headSwitcher(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'index', 'switcher' => 'index'), 'vacancyCard');?>
<?php $cardId = $this->id('card-inline'); ?>
<div class="ui-dialog pcard pcard_inline"  id="<?php echo $this->escape($cardId) ?>">
	<div class="ui-dialog-content-wrapper">
		<div class="lightdialog ui-dialog-content ui-widget-content" id="ui-lightdialog-2">
            <div class="card_photo">
            	<img src="<?php echo $this->serverUrl('/images/people/vacancy.png');?>" align="left"/>
            </div>
<?php
switch ($this->session->state) {
	case HM_At_Session_SessionModel::STATE_PENDING:
		$tooltip = _('Сессия подбора не начата; никто из пользователей не имеет доступа к анкетам.');
		break;
	case HM_At_Session_SessionModel::STATE_ACTUAL:
		$tooltip = _('Сессия начата, анкеты доступны для заполнения пользователями.');
		break;
	case HM_At_Session_SessionModel::STATE_CLOSED:
		$tooltip = _('Сессия подбора закончена; никто из пользователей не имеет доступа к анкетам.');
		break;
}
?>
<?php
echo $this->card(
    $this->vacancy,
        array(
            'getParentPositionName()' => _('Подразделение'),
            'getOpenDate()' => _('Дата открытия'),
            'getCloseDate()' => _('Плановая дата закрытия'),
            'getThisStatus()'   => _('Следующий шаг'),
            'getCreator()' => _('Инициатор вакансии'),
            'getStateSwitcher()'  => array(
                    'title' => _('Статус сессии'),
                    'tooltip' => $tooltip,
            ),

    ),
    array(
        'title' => _('Карточка вакансии'),
    )
); ?>

		</div>
	</div>
</div>
<?php
    $container = Zend_Registry::get('serviceContainer');
    if (  in_array($container->getService('User')->getCurrentUserRole(),
                   array(HM_Role_Abstract_RoleModel::ROLE_HR))):
        $confirmMsg = array(
            HM_At_Session_SessionModel::STATE_ACTUAL      => _('Вы уверены, что хотите стартовать сессию подбора? В этот момент будут отправлены уведомления всем участникам и доступ к анкетам для них будет открыт.'),
            HM_At_Session_SessionModel::STATE_CLOSED => _('Вы уверены, что хотите закончить сессию подбора? При этом доступ к анкетам будет закрыт и будут подсчитаны итоговые результаты сессии.')
        );
        $actionUrl = $this->url(array(
                                      'module'     => 'recruit',
                                      'controller' => 'vacancy',
                                      'action'     => 'change-state',
                                      'session_id' => $this->vacancy->vacancy_id,
                                      'state'=> ''
                                ),null,true);

 $this->inlineScript()->captureStart();
?>
$(document).ready(function () {

var confs = <?php echo HM_Json::encodeErrorSkip($confirmMsg) ?>;
var $sessionsetstate = $(<?php echo HM_Json::encodeErrorSkip("#$cardId"); ?>).find('select[name="sessionsetstate_new_mode"]')
  , $tparent = $sessionsetstate.parent()
  , currentValue = $sessionsetstate.val();

if ($sessionsetstate.length) {
    $sessionsetstate
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