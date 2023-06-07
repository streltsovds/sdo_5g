<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo $this->baseUrl('css/content-modules/marksheet.css')?>" type="text/css">
        <link rel="stylesheet" href="<?php echo $this->baseUrl('css/common.css')?>" type="text/css">
    </head>
    <body>
        <?php if (!count($this->persons)):?>
            <!-- TODO: Это вынести в отдельный файл при стайлинге -->
            <div style="padding:10px;text-align:center;color:brown;font-size:14px;"><?php echo _('Отсутствуют данные для отображения')?></div>
        <?php else:?>
            <?php $totalSchedules = count($this->lessons); ?>
            <?php $totalPersons = count($this->persons); ?>

            <table id="marksheet" class="main-grid" cellspacing="0" data-lessons="<?php echo $totalSchedules;?>" data-persons="<?php echo $totalPersons;?>">
                <colgroup><col><col><col span="<?php echo $totalSchedules + 1;?>"></colgroup>
                <thead>
                    <tr class="marksheet-labels">
                        <!-- TODO: Убрать в нужное место кнопулю комментирования -->
                        <td class="first-cell" colspan="2"></td>
                        <?php foreach ($this->lessons as $key => $lesson):?>
                        <td class="lesson-cell score-cell">
                            <?php echo $lesson['title']; ?>
                        </td>
                        <?php endforeach;?>
                        <td class="score-cell total-cell last-cell"><span class="total-score-label">Итог</span></td>
                    </tr>
                    <tr class="marksheet-head">
                        <th class="marksheet-rowcheckbox first-cell"></th>
                        <th class="fio-cell"><?php echo _('ФИО');?></th>
                        <?php foreach($this->lessons as $key => $lesson):?>
                            <td class="lesson-cell score-cell"></td>
                        <?php endforeach;?>
                        <td class="total-cell last-cell score-cell"></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $temp1 = 1;
                    foreach ($this->persons as $person):?>
                        <?php $class = ($temp1 % 2 == 0) ? "even" : "odd";
                        if ($temp1 == 1) {$class .= " first-row";} elseif ($temp1 == $totalPersons) {$class .= " last-row";}?>
                        <tr class="<?php echo $class; ?>">
                            <td class="marksheet-rowcheckbox first-cell"></td>
                            <td class="fio-cell"><?php echo $person['name'];?></td>
                            <?php $temp = 1; ?>
                            <?php foreach ($this->lessons as $lesson):?>
                                <td class="score-cell lesson-cell
                                    <?php if(!isset($this->lessonsTotal[$person['id']][$lesson['id']])) { echo 'no-score'; }?>">
                                    <div>
                                        <?php if (isset($this->lessonsTotal[$person['id']][$lesson['id']]) && ($this->lessonsTotal[$person['id']][$lesson['id']]['mark'] > -1)):?>
                                            <?php echo $this->lessonsTotal[$person['id']][$lesson['id']]['mark'];?>
                                        <?php endif;?>
                                    </div>
                                </td>
                                <?php $temp++; ?>
                            <?php endforeach;?>
                            <td class="score-cell total-cell last-cell"><div><?php if($this->subjectsTotal[$person['id']]['mark'] > -1) echo $this->subjectsTotal[$person['id']]['mark'];?></div></td>
                            <?php $temp++;?>

                        </tr>
                        <?php $temp1++; ?>
                    <?php endforeach;?>
                    <tr class="last-row ui-helper-hidden">
                        <td class="first-cell" colspan="2"></td>
                        <td class="slider-cell" colspan="<?php echo $totalSchedules; ?>"></td>
                        <td class="last-cell"></td>
                    </tr>
                </tbody>
            </table>
            <script type="text/javascript">
                window.print();
                window.onload = function cleanPage(){ document.getElementById('ZFDebug_debug').innerHTML = '';};
            </script>
        <?php endif;?>
    </body>
</html>