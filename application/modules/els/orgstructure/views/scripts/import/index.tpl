<?php if ($this->form): ?>
    <?php echo $this->form ?>
<?php else: ?>
    <v-card class="pa-4">

        <v-card-title><?php echo sprintf(_('Будут добавлены %d элемента(ов) и %d пользователя(ей), обновлены %d элемента(ов) и %d пользователя(ей), удалены %d элемента(ов)'), $this->importManager->getInsertsCount(), $this->importManager->getInsertsPeopleCount(), $this->importManager->getUpdatesCount(), $this->importManager->getUpdatesPeopleCount(), $this->importManager->getDeletesCount()) ?></v-card-title>


        <?php
        $cancelButton = $this->formReset('cancel', _('Отмена'), [
            'class' => 'primary--text v-btn v-btn--outlined v-size--large',
            'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'orgstructure', 'controller' => 'list', 'action' => 'index'])) . '"']);

        $processButton = $this->formButton('process', _('Далее'), [
            'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'orgstructure', 'controller' => 'import', 'action' => 'process'])) . '"']);
        ?>

        <v-card-text>
            <?php echo $cancelButton ?>&nbsp;&nbsp;<?php echo $processButton ?>
        </v-card-text>

        <?php if (count($this->importManager->getInserts())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут добавлены следующие элементы') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><b><?php echo _('Название подразделения/должности') ?></b></th>
                    <th><b><?php echo _('В должности') ?></b></th>
                </tr>
                <?php foreach ($this->importManager->getInserts() as $insert): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td colspan=\"2\">...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $insert->name ?></td>
                        <td><?php if ($insert->getUser()): ?><?php echo $insert->getUser()->getName() ?><?php endif; ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (count($this->importManager->getInsertsPeople())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут добавлены следующие пользователи') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><b><?php echo _('ФИО') ?></b></th>
                </tr>
                <?php foreach ($this->importManager->getInsertsPeople() as $insert): ?>
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
        <?php endif; ?>

        <?php if (count($this->importManager->getUpdates())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут обновлены следующие элементы') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><b><?php echo _('Название исходного подразделения/должности') ?></b></th>
                    <th><b><?php echo _('Назначен') ?></b></th>
                    <th><b><?php echo _('Название подразделения/должности') ?></b></th>
                    <th><b><?php echo _('Назначен') ?></b></th>
                </tr>
                <?php foreach ($this->importManager->getUpdates() as $update): if ($update['source'] == "" || $update['destination'] == "") {
                    continue;
                } ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td colspan=\"2\">...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $update['source']->name ?></td>
                        <td><?php if ($update['source']->getUser()): ?><?php echo $update['source']->getUser()->getName() ?><?php endif; ?></td>
                        <td><?php echo $update['destination']->name ?></td>
                        <td><?php if ($update['destination']->getUser()): ?><?php echo $update['destination']->getUser()->getName() ?><?php endif; ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?php if (count($this->importManager->getUpdatesPeople())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут обновлены следующие пользователи') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><b><?php echo _('ФИО') ?></b></th>
                </tr>
                <?php foreach ($this->importManager->getUpdatesPeople() as $update): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td>...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $update['source']->getName() ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?php if (count($this->importManager->getDeletes())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут удалены следующие элементы') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><b><?php echo _('Название подразделения/должности') ?></b></th>
                    <th><b><?php echo _('В должности') ?></b></th>
                </tr>
                <?php foreach ($this->importManager->getDeletes() as $delete): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td colspan=\"2\">...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $delete->name ?></td>
                        <td><?php if ($delete->getUser()): ?><?php echo $delete->getUser()->getName() ?><?php endif; ?></td>
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