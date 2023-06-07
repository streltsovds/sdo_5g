<v-card>
    <v-card-title>
        <h2><?php echo _('Психологическое тестирование') ?></h2>
    </v-card-title>
    <?php if (count($this->criteriaPersonal)): ?>
        <v-card-text>
            <?php foreach ($this->criteriaPersonal as $criterionId => $criterionPersonal): ?>

                <h3><?php echo $criterionPersonal; ?></h3>

                <?php if (isset($this->lists['categories'][$criterionId])): ?>
                    <div class="clearfix">
                        <?php echo $this->reportList($this->lists['categories'][$criterionId]); ?>
                    </div>
                <?php endif; ?>

                <div class="pagebreak"></div>
            <?php endforeach; ?>
        </v-card-text>
    <?php endif; ?>
</v-card>
