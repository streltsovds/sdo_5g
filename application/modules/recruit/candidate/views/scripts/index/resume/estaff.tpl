<?php if (!$this->isAjax): ?>
    <a href="<?php echo $this->referer; ?>">Назад</a>
<?php endif; ?>
<div class="at-form-report at-form-report-resume-small">
    <?php if (!$this->isAjax): ?> <v-card> <?php endif; ?>
        <v-layout wrap>
            <v-flex xs4 sm3 md2>
                <div class="photo-block">
                    <img src="<?php echo $this->photo;?>" alt="Фото отсутствует"/>
                </div>
            </v-flex>
            <v-flex xs8 sm9 md10>
                <v-card-title class="headline"> <?php echo $this->name;?></v-card-title>
                <?php if ($this->estaffUrl): ?>
                    <v-card-text>
                        <hm-download-btn
                                text='<?php echo _("Резюме из E-Staff"); ?>'
                                url='<?php echo $this->estaffUrl?>'
                                name='new'
                                options='width=800,height=600,left=350,top=150,toolbar=1'
                        ></hm-download-btn>
                    </v-card-text>
                <?php endif; ?>
            </v-flex>
        </v-layout>
    <?php if (!$this->isAjax): ?> </v-card> <?php endif; ?>
</div>

