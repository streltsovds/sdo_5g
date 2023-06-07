<v-card>
    <v-card-title class="pb-0">
        <?php echo _('Список адресатов') ?>
    </v-card-title>

    <?php if (count($this->users)) : ?>
        <v-list dense>
            <?php foreach ($this->users as $user) : ?>
                <v-list-item dense>
                    <v-list-item-content>
                        <?php echo sprintf("%s", $user->getName()) ?>
                    </v-list-item-content>
                </v-list-item>
            <?php endforeach; ?>
        </v-list>
    <?php endif; ?>
</v-card>
<br>
<?php echo $this->form ?>