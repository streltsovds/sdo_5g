<v-card color="transparent" class="hm-partials-modes elevation-0">
    <v-card-actions class="pa-0" style="padding-bottom: 26px !important;">
        <v-spacer></v-spacer>
        <span class="hm-partials-modes__label" style="padding-right: 20px; font-size: 14px">
            {{ _('Вид') }}
        </span>
        <hm-group-btn :buttons='<?php echo ($this->buttonsJson);?>'></hm-group-btn>
    </v-card-actions>
</v-card>

