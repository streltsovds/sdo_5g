<?php $cardId = $this->id('card-inline'); ?>
<div class="pcard pcard_inline" id="<?php /*echo $this->escape($cardId)*/ ?>">
<?php echo $this->card(
    $this->session,
    array(
        'getCycleTitle()' => _('Оценочный период'),
        'getBegin()'  => _('Дата начала'),
        'getEnd()'    => _('Дата окончания'),
        'getState()'  => _('Статус сессии'),
    ),
    array(
        'title' => _('Карточка сессии оценки')
    ));
?>
</div>
<?php
    $container = Zend_Registry::get('serviceContainer');
    if (  in_array($container->getService('User')->getCurrentUserRole(),
                   array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))):
        $confirmMsg = array(
            HM_At_Session_SessionModel::STATE_ACTUAL      => _('Вы уверены, что хотите стартовать сессию оценки? В этот момент будут отправлены уведомления всем участникам и доступ к анкетам для них будет открыт.'),
            HM_At_Session_SessionModel::STATE_CLOSED => _('Вы уверены, что хотите закончить сессию оценки? При этом доступ к анкетам будет закрыт и будут подсчитаны итоговые результаты сессии.')
        );
        $actionUrl = $this->url(array(
                                      'module'     => 'session',
                                      'controller' => 'index',
                                      'action'     => 'change-state',
                                      'session_id' => $this->session->session_id,
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