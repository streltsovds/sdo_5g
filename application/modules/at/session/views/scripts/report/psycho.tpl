        <h2><?php echo _('Отчёт') ?>: <?php echo $this->title; ?></h2>

    <v-card class="report-summary">
        <v-card-title class="headline">
            <?php echo _('Общая информация');?>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 sm6>
                    <?php echo $this->reportList($this->lists['general']);?>
                </v-flex>
                <v-flex xs12 sm6>
                    <?php echo $this->reportList($this->lists['session']);?>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>

    <div class="pagebreak"></div>&nbsp;

    <v-card>
        <v-card-title class="headline">
            <?php echo _('Графики результатов');?>
        </v-card-title>
        <v-card-text>

    <?php foreach($this->data as $i=>$data) : ?>
                    <?php $this->reportChartJS(); ?>
                    <?php echo $this->reportChartJS(
                        $data,
                        array(),//$this->graph[$i]
                        [
                            'id' => 'psycho',
                            'type' => 'line',
                            'dataLabel' => 'title',
                            'dataValue' => 'value',
                            'title' => sprintf(_('Диаграмма %s. ').$this->graph[$i]['title'], $i+1),
                            'maxValue' => 10,
                            'height' => 300,
                        ],
                        [//'dataTitle' => _('Значение'),
                            'showTable' => HM_View_Helper_ReportChartJS::TABLE_DISPLAY_NONE, //BLOCK
                        ]
                    ); ?>

            <table>
                <thead>
                    <tr>
                        <th>Показатель</th><th>Название</th><th>Комментарий</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($this->table[$i] as $row) : ?>
                        <tr>
                            <td><?=$row[0]?></td><td><?=$row[2]?></td><td><? echo "{$row[3]}(".round($row[1], 2).")"?></td>
                        </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

    <?php endforeach; ?>





        </v-card-text>
</v-card>
