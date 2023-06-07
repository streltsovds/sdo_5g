<v-card>
    <v-card-title class="headline"><?php echo _('Оценка компетенций');?></v-card-title>
    <v-card-text>
        <?php echo $this->reportText(Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceReportComment', $this->session->getOptionsModifier()));?>
        <?php /* Полезный chart, но скрыт, пока не избавимся от flash :/?>
            <h3><?php echo _('Анализ результатов');?></h3>
            <h3 class="report-chart-title"><?php echo sprintf(_('Диаграмма %s. Сравнение результатов оценки с профилем должности'), ++$i);?></h3>
            <div>
                <?php echo $this->chartJS(
                $this->analyticsChartData['data'],
                $this->analyticsChartData['graphs'],
                array(
                'id' => 'analytics',
                'type' => 'bar',
                'dataValue' => 'value',
                'dataLabel' => 'title',
                'width'  => 1400,
                'height' => 400,
                )
                );?>
            </div>
            <p><?php echo _('Ваши сильные стороны (компетенции, превосходящие профиль успешности)');?>:</p>
            <?php echo $this->reportList($this->lists['competence_top']);?>
            <p><?php echo _('Компетенции, набравшие меньшее количество баллов по сравнению с профилем успешности');?>:</p>
            <?php echo $this->reportList($this->lists['competence_bottom']);?>
            <?php if (isset($this->lists['competence_top_hidden'])): ?>
            <p><?php echo _('Скрытые возможности (компетенции, по которым оценка экспертов максимально превышает Вашу самооценку)');?>:</p>
            <?php echo $this->reportList($this->lists['competence_top_hidden']);?>
            <?php endif;?>
            <?php if (isset($this->lists['competence_bottom_hidden'])): ?>
            <p><?php echo _('Скрытые зоны развития (компетенции, по которым самооценка максимально превышает среднюю оценку экспертов)');?>:</p>
            <?php echo $this->reportList($this->lists['competence_bottom_hidden']);?>
            <?php endif;?>
            <div class="pagebreak"></div>
        <?php */?>

        <?php $this->reportChartJS();?>
        <h4><?php echo sprintf(_('Диаграмма %s. Объединенные результаты оценки компетенций (среднее значение)'), ++$i)?></h4>
        <?php echo $this->reportChartJS(
            $this->charts['competences']['data'],
            $this->charts['competences']['graphs'],
            array(
                'id' => 'competences',
                'type' => 'radar',
                'maxValue' => $this->scaleMaxValue,
                'height' => $this->print ? 350 : 500,
                'legend' => ['show' => true],
                'margin' => [
                    'top' => 20,
                    'right' => 100,
                    'bottom' => 20,
                    'left' => 100
                ]
            ),
            array(
                'dataTitle'=> _('Компетенция'),
                'showTable' => HM_View_Helper_ReportChartJS::TABLE_DISPLAY_BLOCK,
                'footnote' => isset($this->footnotes['competences']) ? $this->footnotes['competences'] : '',
            )
        );?>
        <div class="pagebreak"></div>
        <?php if (count($this->competenceCriteria)): ?>
            <h3><?php echo _('Подробные результаты по компетенциям');?></h3>
            <?php foreach($this->competenceCriteria as $criterionId => $criterionName): ?>
                <?php $chartId = 'competence_criterion_' . $criterionId;?>
                <?php if (isset($this->charts[$chartId])): ?>
                    <?php if (count($this->charts[$chartId]) > 1):  // если нет разных категорий респондентов, выглядит очень некрасиво и бесполезно ?>
                        <h4><?php echo sprintf(_('Диаграмма %d. Оценка компетенции %s'), ++$i, sprintf('&quot;%s&quot;', $criterionName)) ?></h4>
                        <?php echo $this->reportChartJS(
                            $this->charts[$chartId],
                            array(),
                            array(
                                'id' => $chartId,
                                'dataValue' => 'value',
                                'dataLabel' => 'title',
                                'maxValue' => $this->scaleMaxValue,
                                'height' => 250,
                                'axisY' => [
                                    'ticksCount' => 4
                                ],
                                'axisX' => [
                                    'text' => [
                                        'rotate' => true
                                    ]
                                ],
                                'margin' => [
                                    'left' => 30,
                                    'bottom' => 110
                                ]
                            )
                        );?>
                    <?php endif;?>
                    <?php echo $this->reportTable($this->tables[$chartId]);?>
                    <div class="pagebreak"></div>
                <?php endif;?>
            <?php endforeach;?>
        <?php endif;?>
    </v-card-text>
</v-card>
