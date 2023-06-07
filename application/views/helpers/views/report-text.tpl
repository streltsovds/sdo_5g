<v-card>
    <?php if (strlen($this->title)):?>
        <v-card-title class="headline"><?php echo $this->title?></v-card-title>
    <?php endif;?>
    <?php if (strlen($this->data)):?>
        <v-card-text><?php echo $this->data;?></v-card-text>
    <?php endif;?>
</v-card>