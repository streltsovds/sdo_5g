<?php if ($this->form): ?>
    <?php echo $this->form ?>
<?php else: ?>
    <v-card class="pa-4">

        <v-card-title>
            <?php echo sprintf(_('Будут добавлены %d элемента(ов), обновлены %d элемента(ов)'), $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount()) ?></v-card-text>
        </v-card-title>

        <?php
        $cancelButton = $this->formReset('cancel', _('Отмена'), [
            'class' => 'primary--text v-btn v-btn--outlined v-size--large',
            'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'kbase', 'controller' => 'resources', 'action' => 'index'], null, true)) . '"']);

        $processButton = $this->formButton('process', _('Далее'), [
            'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'resource', 'controller' => 'import', 'action' => 'process'])) . '"']);
        ?>

        <v-card-text>
            <?php echo $cancelButton ?>&nbsp;&nbsp;<?php echo $processButton ?>
        </v-card-text>

        <?php if (count($this->importManager->getInserts())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут добавлены следующие элементы') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><?php echo _('Код') ?></th>
                    <th><?php echo _('Название') ?></th>
                    <th><?php echo _('Описание') ?></th>
                </tr>
                <?php foreach ($this->importManager->getInserts() as $insert): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td colspan=\"2\">...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $insert->resource_id_external ?></td>
                        <td><?php echo $insert->title ?></td>
                        <td><?php echo $insert->description ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (count($this->importManager->getUpdates())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут обновлены следующие элементы') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><?php echo _('Код элемента') ?></th>
                    <th><?php echo _('Название элемента') ?></th>
                    <th><?php echo _('Новое название элемента') ?></th>
                </tr>
                <?php foreach ($this->importManager->getUpdates() as $update): if ($update['source'] == "" || $update['destination'] == "") {
                    continue;
                } ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td colspan=\"2\">...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $update['source']->resource_id_external ?></td>
                        <td><?php echo $update['source']->title ?></td>
                        <td><?php echo $update['destination']->title ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <v-card-text>
            <?php echo $cancelButton ?>&nbsp;&nbsp;<?php echo $processButton ?>
        </v-card-text>
    </v-card>
<?php endif; ?>