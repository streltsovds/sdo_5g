<?php if (isset($this->message)): ?>
    <v-card>
        <v-card-text>
            <v-alert type="error" outlined value="true"><?php echo $this->message; ?></v-alert>
        </v-card-text>
    </v-card>
<?php endif; ?>
<?php echo $this->form;