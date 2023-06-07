<?php if ($this->form):?>
    <?php echo $this->form?>
<?php else:?>
    <div class="tmc-upform">
        <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
        <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'quest', 'controller' => 'import-questions', 'action' => 'process', 'source' => $this->source))).'"'))?>
    </div>
    <br/>
    <?php if (count($this->importManager->getInserts())):?>
    <?php $count = 1;?>

        <p><?php echo sprintf(_('Будут добавлены %d вопроса(ов):'), $this->importManager->getInsertsCount())?></p>
        <br/>

        <table class="main" width="100%">
        <tr><th><?php echo _('Вопрос')?></th><th><?php echo _('Ответы')?></th></tr>
        <?php foreach($this->importManager->getInserts() as $insert):?>
            <?php if ($count >= 1000) { echo "<tr><td colspan=2>...</td></tr>"; break;}?>
            <tr>
                <td><?php echo $insert->question?></td>
                <td>
                    <?php foreach($insert->answers as $answer):?>
                        <p><?php if ($answer['is_correct']) echo '+'; else echo '-';?> <?php echo $answer['variant']?>
                    <?php endforeach;?>
                </td>
            </tr>
            <?php $count++;?>
        <?php endforeach;?>
        </table>
    <?php endif;?>
    <br/>
    <div class="tmc-upform">
        <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
        <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'quest', 'controller' => 'import-questions', 'action' => 'process', 'source' => $this->source))).'"'))?>
    </div>
<?php endif;?>