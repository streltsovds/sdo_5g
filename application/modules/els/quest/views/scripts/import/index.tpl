<?php if ($this->form):?>
    <?php echo $this->form?>
<?php else:?>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'quest', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
<br/>
<?php if (count($this->importManager->getInserts())):?>
<?php $count = 1;?>

    <p><?php echo sprintf(_('Будет создано тестов: %d'), $this->importManager->getInsertsCount())?></p>
    <br/>

    <table class="main" width="100%">
    <tr><th><?php echo _('Тест')?></th><th><?php echo _('Количество вопросов')?></th></tr>
    <?php foreach($this->importManager->getInserts() as $insert):?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=2>...</td></tr>"; break;}?>
        <tr>
            <td><?php echo $insert->name?></td>
            <td><?php echo count($insert->questions);?></td>
        </tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'quest', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>

<?php endif;?>