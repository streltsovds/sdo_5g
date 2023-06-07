<?php
$noDataMessage = '
<div>
    <h3>' . _('Данное задание не содержит ни одного варианта и не может быть назначено слушателям.') . '</h3>
    <p>
        ' .  _('Перейдите к редактированию материала для создания варианта(ов),') . '
        <br>
        ' .  _('затем сможете приступить к назначению слушателей на занятие.') . '
    </p>
    <form action="' .  $this->editUrl . '">
        <button type="submit" class="v-btn v-btn--contained theme--light v-size--large primary" >
            ' .  _('Перейти к редактированию материала') . '
        </button>
    </form>
</div>'; ?>

<hm-task-preview
  :task='<?php echo HM_Json::encodeErrorSkip($this->task, JSON_HEX_APOS)?>'
  :variants='<?php echo HM_Json::encodeErrorSkip($this->variants, JSON_HEX_APOS)?>'
  :no-data-message='<?php echo HM_Json::encodeErrorSkip($noDataMessage, JSON_HEX_APOS)?>'
/>