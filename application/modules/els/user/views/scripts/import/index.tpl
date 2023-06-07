<?php if ($this->form) : ?>
    <?php echo $this->form ?>
<?php else : ?>
    <v-card class="pa-4">
        <?php if ($this->importManager->getInsertsCount()) : ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo sprintf(_('Будут добавлены следующие %d пользователя(ей):'), $this->importManager->getInsertsCount()); ?></v-card-title>
            <v-card-text>
                <table class="main" style="max-height: 50vh; display: block;" width="100%">
                    <tr>
                        <th><strong><?php echo _('ФИО') ?></strong></th>
                    </tr>
                    <?php foreach ($this->importManager->getInserts() as $insert) : ?>
                        <?php if ($count >= 1000) {
                            echo "<tr><td>...</td></tr>";
                            break;
                        } ?>
                        <tr>
                            <td><?php echo $insert->getName() ?></td>
                        </tr>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                </table>
            </v-card-text>
        <?php endif; ?>

        <?php if ($this->importManager->getUpdatesCount()) : ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo sprintf(_('Будут обновлены следующие %d пользователя(ей):'), $this->importManager->getUpdatesCount()); ?></v-card-title>
            <v-card-text>
                <table class="main" style="max-height: 50vh; display: block;" width="100%">
                    <tr>
                        <th><strong><?php echo _('Было') ?></strong></th>
                        <th><strong><?php echo _('Стало') ?></strong></th>
                    </tr>
                    <?php foreach ($this->importManager->getUpdates() as $update) : ?>
                        <?php if ($count >= 1000) {
                            echo "<tr><td colspan=\"2\">...</td></tr>";
                            break;
                        } ?>
                        <tr>
                            <td><?php echo $update['source']->getName() ?></td>
                            <td><?php echo $update['destination']->getName() ?></td>
                        </tr>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                    <?php if (count($this->importManager->getUpdates()['additions'])) : ?>
                        <?php if (count($this->importManager->getUpdates()['people'])) : ?>
                            <p><?php echo _('Дополнительно:'); ?></p>
                        <?php endif; ?>
                        <?php foreach ($this->importManager->getUpdates()['additions'] as $mid => $update) : ?>
                            <?php $user = Zend_Registry::get('serviceContainer')->getService('User')->find($mid)->current(); ?>
                            <?php $message = count($update['groups']) && count($update['tags']) ? _('обновление групп и тегов') : (count($update['groups']) ? _('обновление групп') : _('обновление тегов')); ?>
                            <?php if ($count >= 1000) {
                                echo "<tr><td colspan=\"2\">...</td></tr>";
                                break;
                            } ?>
                            <tr>
                                <td><?php echo $user->getName() . ' - ' . $message; ?></td>
                            </tr>
                            <?php $count++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </v-card-text>
        <?php endif; ?>

        <?php if ($this->importManager->getCount()) : ?>
            <v-card-text>
                <?php if ($this->source == 'studyHistoryCsv') : ?>
                    <?php echo $this->formButton('cancel', _('Отмена'), ['class' => $this->cancelBtnClass, 'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'subject', 'controller' => 'history', 'action' => 'index'])) . '"']) ?>
                <?php else : ?>
                    <?php echo $this->formReset('cancel', _('Отмена'), ['class' => $this->cancelBtnClass, 'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'user', 'controller' => 'list', 'action' => 'index'], null, true)) . '"']) ?>
                <?php endif; ?>
                &nbsp;&nbsp;
                <?php echo $this->formButton('process', _('Далее'), ['onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'user', 'controller' => 'import', 'action' => 'process', 'source' => $this->source])) . '"']) ?>
            </v-card-text>
        <?php else : ?>
            <v-card-text>
                <v-card-title><?php echo _('Нет пользоватеей для добавления и обновления'); ?></v-card-title>
                <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "' . $this->serverUrl($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'index'), null, true)) . '"')) ?>
            </v-card-text>
        <?php endif; ?>
    </v-card>
<?php endif; ?>