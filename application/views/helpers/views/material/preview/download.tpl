<?php $link = ($this->externalViewerUrl) ? null: $this->materialContentUrl;?>
<v-tooltip right>
    <v-btn class="material-card_btn btn-download primary--text"
            fab
            dark
            color="white"
            href="<?php echo $link?>"
            slot="activator"
    >
        <v-icon class="icon-save" color="primary">save_alt</v-icon>
    </v-btn>
    <span>Скачать файл</span>
</v-tooltip>
