<?php if ($this->backUrl): ?>
<div style="padding-bottom: 10px;">
    <button onClick="javascript: document.location.href='<?php echo $this->backUrl; ?>';">Назад</button>
</div>
<?php endif; ?>

<div class="at-form-report">
    <v-layout wrap>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Общие сведения');?></v-card-title>
                <v-card-text>
                    <v-layout wrap>
                        <v-flex xs12 sm6><?php echo $this->reportList($this->lists['generalLeft']);?></v-flex>
                        <v-flex xs12 sm6><?php echo $this->reportList($this->lists['generalRight']);?></v-flex>
                    </v-layout>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip right>
                        <v-btn text icon slot="activator" color="primary" href="<?php echo $this->editUrl?>">
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <span><?php echo _('Редактировать')?></span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-flex>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Должностные обязанности');?></v-card-title>
                <v-card-text><?php echo $this->reportTable($this->tables['responsibility'], '', '#');?></v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip right>
                        <v-btn text icon slot="activator" color="primary" href="<?php echo $this->editUrl?>">
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <span><?php echo _('Редактировать')?></span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-flex>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Основные требования к кандидату');?></v-card-title>
                <v-card-text><?php echo $this->reportList($this->lists['primaryRequirements']);?></v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip right>
                        <v-btn text icon slot="activator" color="primary" href="<?php echo $this->editUrl?>">
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <span><?php echo _('Редактировать')?></span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-flex>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Дополнительные требования к кандидату');?></v-card-title>
                <v-card-text><?php echo $this->reportList($this->lists['additionalRequirements']);?></v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip right>
                        <v-btn text icon slot="activator" color="primary" href="<?php echo $this->editUrl?>">
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <span><?php echo _('Редактировать')?></span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-flex>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Требования по профстандартам');?></v-card-title>
                <v-card-text><?php echo $this->reportTable($this->tables['skills'], ''/*, _('№ п/п')*/);?></v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip right>
                        <v-btn text icon slot="activator" color="primary" href="<?php echo $this->editUrl?>">
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <span><?php echo _('Редактировать')?></span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-flex>
    </v-layout>
</div>