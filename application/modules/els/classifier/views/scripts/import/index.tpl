<?php if ($this->form): ?>
    <?php echo $this->form ?>
<?php else: ?>
    <v-card class="pa-4">

        <v-card-title><?php echo sprintf(_('Будут добавлены %d рубрик(а) и удалены %d рубрик(а)'), $this->importManager->getInsertsCount(), $this->importManager->getDeletesCount()) ?></v-card-title>


        <v-card-text>
            <?php echo $this->formReset('cancel', _('Отмена'), [
                'class' => 'primary--text v-btn v-btn--outlined v-size--large',
                'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => $this->type])) . '"']); ?>

            <?php echo $this->formButton('process', _('Далее'), [
                'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'classifier', 'controller' => 'import', 'action' => 'process', 'source' => $this->source, 'type' => $this->type])) . '"']); ?>
        </v-card-text>

        <?php if (count($this->importManager->getInserts())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут добавлены следующие рубрики') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><?php echo _('Название') ?></th>
                </tr>
                <?php foreach ($this->importManager->getInserts() as $insert): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td>...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $insert->name ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?php if (count($this->importManager->getUpdates())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут обновлены следующие рубрики') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><?php echo _('Было') ?></th>
                    <th><?php echo _('Стало') ?></th>
                </tr>
                <?php foreach ($this->importManager->getUpdates() as $update): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td colspan=\"2\">...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $update['source']->name ?></td>
                        <td><?php echo $update['destination']->name ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?php if (count($this->importManager->getDeletes())): ?>
            <?php $count = 1; ?>
            <v-card-title><?php echo _('Будут удалены следующие рубрики') ?>:</v-card-title>

            <table class="main" width="100%">
                <tr>
                    <th><?php echo _('Название') ?></th>
                </tr>
                <?php foreach ($this->importManager->getDeletes() as $delete): ?>
                    <?php if ($count >= 1000) {
                        echo "<tr><td>...</td></tr>";
                        break;
                    } ?>
                    <tr>
                        <td><?php echo $delete->name ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <v-card-text>
            <?php echo $this->formReset('cancel', _('Отмена'), [
                'class' => 'primary--text v-btn v-btn--outlined v-size--large',
                'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => $this->type])) . '"']) ?>

            <?php echo $this->formButton('process', _('Далее'), [
                'onClick' => 'window.location.href = "' . $this->serverUrl($this->url(['module' => 'classifier', 'controller' => 'import', 'action' => 'process', 'source' => $this->source, 'type' => $this->type])) . '"']) ?>
        </v-card-text>

    </v-card>
<?php endif; ?>