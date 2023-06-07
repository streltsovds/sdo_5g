<form method="post">
    <v-btn color="primary" href="<?php echo $this->backUrl; ?>">
        <span>Назад</span>
    </v-btn>
    <v-btn color="primary" type="submit" name="merge">
        <span>Объединить</span>
    </v-btn>
    <v-btn color="primary" type="submit" name="saveboth">
        <span>Сохранить как 2 разных</span>
    </v-btn>
</form>
<v-container>
    <v-row>
        <v-col>
            <div style="padding: 10px;">
                <?= $this->action('index', 'report', 'user', array('user_id' => $this->baseUser->MID, 'withoutContextMenu' => true, 'withoutPrintButton' => true)); ?>
            </div>
        </v-col>
        <v-col xs6 sm6 md6 class="duplicate-merge__duplicate">
            <div style="padding: 10px;">
                <?= $this->action('index', 'report', 'user', array('user_id' => $this->newUser->MID, 'withoutContextMenu' => true, 'withoutPrintButton' => true)); ?>
            </div>
        </v-col>
    </v-row>
</v-container>
<form method="post">
    <v-btn color="primary" href="<?php echo $this->backUrl; ?>">
        <span>Назад</span>
    </v-btn>
    <v-btn color="primary" type="submit" name="merge">
        <span>Объединить</span>
    </v-btn>
    <v-btn color="primary" type="submit" name="saveboth">
        <span>Сохранить как 2 разных</span>
    </v-btn>
</form>
