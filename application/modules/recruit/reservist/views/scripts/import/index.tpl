<?php if ($this->form):?>
    <?php echo $this->form?>
<?php else:?>
<br/>
<?php if (count($this->importManager->getInserts())):?>
<?php $count = 1;?>

    <p><?php echo sprintf(_('В файле содержится %d записей.'), $this->importManager->getInsertsCount())?></p>
    <br/>

<!--    <table class="main" width="100%">-->
<!--    <tr>-->
<!--        <th>--><?php //echo _('Пользователь')?><!--</th>-->
<!--        <th>--><?php //echo _('Подразделение')?><!--</th>-->
<!--        <th>--><?php //echo _('Логин')?><!--</th>-->
<!--        <th>--><?php //echo _('Ошибка')?><!--</th>-->
<!--        <th>--><?php //echo _('НТД')?><!--</th>-->
<!--    </tr>-->
<!--    --><?php //foreach($this->importManager->getInserts() as $insert):?>
<!--        --><?php //if ($count >= 1000) { echo "<tr><td colspan=2>...</td></tr>"; break;}?>
<!--        <tr>-->
<!--            <td>--><?php //echo $insert->fio_excel; ?><!--</td>-->
<!--            <td>--><?php //echo $insert->department_excel; ?><!--</td>-->
<!--            <td>--><?php //echo $insert->login; ?><!--</td>-->
<!--            <td>--><?php //echo $insert->description; ?><!--</td>-->
<!--            <td>--><?php //echo $insert->ntd; ?><!--</td>-->
<!--        </tr>-->
<!--        --><?php //$count++;?>
<!--    --><?php //endforeach;?>
<!--    </table>-->
<?php endif;?>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('baseUrl' => 'tc', 'module' => 'session', 'controller' => 'import', 'action' => 'process', 'session_id' => $this->sessionId, 'source' => $this->source))).'"'))?>

<?php endif; ?>