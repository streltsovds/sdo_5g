<?php if (is_array($this->data)):?>
    <?php if($this->class == HM_View_Helper_ReportList::CLASS_COLORED_QUESTION): ?>

        <v-list style="background: inherit !important;" class="report-list report-list-<?php echo $this->class?>">
            <?php foreach ($this->data as $key => $value):?>
                <?php
                $rowclass = '';
                if ($key == _('Вопрос')) {
                    $rowclass = 'tmc-row-question';
                } else if ($key == _('Ответ')) {
                    $rowclass = 'tmc-row-answer';
                } else if ($key == _('Результат')) {
                    $rowclass = 'tmc-row-result-yellow';
                    $p = explode('(',$value);
                    if (isset($p[1])) {
                        $pp = (int) str_replace('%)', '', $p[1]);
                        if ($pp == 100) {
                            $rowclass = 'tmc-row-result-green';
                        }
                        if ($pp == 0) {
                            $rowclass = 'tmc-row-result-red';
                        }
                    }
                }
                ?>
            <v-list-item>
                <v-list-item-content>
                    <v-list-item-subtitle class="report-list-key <?php echo $rowclass; ?>">
                        <?php echo $key?>
                    </v-list-item-subtitle>
                    <v-list-item-title class="report-list-value <?php echo $rowclass; ?>">
                        <?php echo $value?>
                    </v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <?php endforeach;?>
        </v-list>

    <?php else: ?>

        <v-list style="background: inherit !important;" class="report-list report-list-<?php echo $this->class?>">
            <?php foreach ($this->data as $key => $item):?>
            <?php
                $class = 'inline';
                if (is_array($item) && array_key_exists('block', $item) && $item['block']) $class = 'block';
            ?>
            <v-list-item>
                <v-list-item-content class="<?php echo $class;?>">
                    <v-list-item-subtitle class="report-list-key">
                        <?php echo $key?>
                    </v-list-item-subtitle>
                    <v-list-item-title class="report-list-value">
                        <?php if (is_array($item)): ?>
                            <?php echo array_key_exists('value', $item) ? $item['value'] : ''; ?>
                        <?php else: ?>
                            <?php echo $item?>
                        <?php endif;?>
                    </v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <?php endforeach;?>
        </v-list>

    <?php endif;?>
<?php endif;?>