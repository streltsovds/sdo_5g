<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/at-criterion-table.css'), 'screen,print');?>
<div class="at-criterion">

    <div class="filter">
        <label>ФИО</label>
        <select name="f-fio" class="filter-1" multiple>
            <?php foreach ($this->filters['names'] as $name): ?>
            <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
        &emsp;
        <label>Должность</label>
        <select name="f-so" class="filter-1" multiple>
            <?php foreach ($this->filters['so_names'] as $name): ?>
            <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
        &emsp;
        <label>Подразделение</label>
        <select name="f-pso" class="filter-1" multiple>
            <?php foreach ($this->filters['so_positions'] as $name): ?>
            <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
        &emsp;
        <label>Компетенция/Квалификация</label>
        <select name="f-criteria" class="filter-2" multiple>
            <?php foreach($this->criterias as $ctype => $criterias): ?>
                <?php foreach($criterias as $cid => $criteria): ?>
                    <option value="<?php echo $criteria; ?>"><?php echo $criteria; ?></option>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </select>
    </div>

    <script>
        $(function(){
            $('.filter select').each(function(){
                HM.create('hm.core.ui.select.Select', {
                    select: $(this),
                    filter: true,
                    optionsInside: false
                });
            });
        });
    </script>


    <div class="data-wrap">
        <table class="data">
            <tr class="trh">
                <th class="fio fixed-col">
                    ФИО
                </th>
                <th class="info fixed-col">
                    Должность
                </th>
                <th class="info fixed-col">
                    Подразделение
                </th>
                <?php foreach($this->criterias as $ctype => $criterias): ?>
                    <?php foreach($criterias as $cid => $criteria): ?>
                        <th class="competence">
                            <?php echo $criteria; ?>
                        </th>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($this->people as $person): ?>
            <tr class="trd">
                <td  class="fio fixed-col">
                    <a href="<?php echo
                    $this->url(
                        array(
                            'baseUrl' => '',
                            'action' => 'index',
                            'controller' => 'report',
                            'module' => 'user',
                            'user_id' => $person['id']
                        )
                    ); ?>"><?php echo $person['user_name']; ?></a>
                </td>
                <td class="so fixed-col">
                    <?php echo $person['so_name']; ?>
                </td>
                <td class="pso fixed-col">
                    <?php echo $person['so_parent']; ?>
                </td>
                <?php foreach($this->criterias as $ctype => $criterias): ?>
                    <?php foreach($criterias as $cid => $criteria): ?>
                        <?php if (isset($person['results'][$cid])): ?>
                            <?php $k =  $person['results'][$cid] /  ($person['competences'][$cid]?$person['competences'][$cid]:1); ?>
                            <td class="competence <?php echo ($k > 1)?'green':(($k < 1)?'red':'yellow'); ?>">
                                <?php echo  $person['results'][$cid] ; ?>
                            </td>
                        <?php else: ?>
                            <td class="competence">

                            </td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <script type="text/javascript">
        $(function(){
            var $fixedCol = $('.fixed-col'),
                $fixedRow = $('.trh th'),
                $content = $('.data-wrap');

            $content.on('mousewheel DOMMouseScroll', function(e) {
                var altKey = e.altKey,
                    delta = e.originalEvent.detail < 0 || e.originalEvent.wheelDelta > 0 ? 1 : -1;

                if (delta < 0) {
                    delta = 100;
                } else {
                    delta = -100;
                }

                e.preventDefault();

                var scrollLeft = $content.scrollLeft(),
                    scrollTop = $content.scrollTop();

                if (altKey) {
                    $content.scrollLeft(scrollLeft + delta);
                } else {
                    $content.scrollTop(scrollTop + delta);
                }
            }).on('scroll', function(e) {
                var scrollLeft = $content.scrollLeft();
                var scrollTop = $content.scrollTop();

                $fixedCol.css('left', scrollLeft);
                $fixedRow.css('top', scrollTop);
            });


            var $competence = $('.at-criterion .competence');
            $('.at-criterion .filter .filter-2').change(function () {
                var values = $(this).val() || [];
                if (values.length) {
                    $competence.hide();
                    $('table.data .trh th').each(function (index) {
                        var text = $.trim($(this).text());
                        var colIndex = index;
                        if (values.indexOf(text) != -1) {
                            $(this).show();
                            $('table.data tr').each(function(){
                                $(this).find('td').eq(colIndex).show();
                            });
                        }
                    });
                } else {
                    $competence.show();
                }
            });
        });
    </script>

    <?php $this->inlineScript()->captureStart();?>
        $('.at-criterion .filter .filter-1').change(function () {
            var fval_name = $('.at-criterion .filter select[name="f-fio"]').val() || [];
            var fval_so = $('.at-criterion .filter select[name="f-so"]').val() || [];
            var fval_pso = $('.at-criterion .filter select[name="f-pso"]').val() || [];

            $('table.data .trd').each(function () {

                var $tr = $(this);

                var hide = false;

                if (fval_name.length != 0 && fval_name.indexOf($tr.find('td.fio').text().trim()) == -1) {
                    hide = true;
                }
                if (fval_so.length != 0 && fval_so.indexOf($tr.find('td.so').text().trim()) == -1) {
                    hide = true;
                }
                if (fval_pso.length != 0 && fval_pso.indexOf($tr.find('td.pso').text().trim()) == -1) {
                    hide = true;
                }

                if (hide) {
                    $tr.addClass('hiddenrow');
                } else {
                    $tr.removeClass('hiddenrow');
                }

            });
        });

    <?php $this->inlineScript()->captureEnd();?>

    <form method="post" action="/at/criterion/user/excel">
        <?php foreach ($this->people as $person): ?></php>
            <?php $ids[] = $person['position_id']; ?>
        <?php endforeach; ?>
        <input type="hidden" name="positionIds" value="<?php echo implode(',',$ids); ?>">
        <input type="submit" name="button" target="_blank" value="Экспорт в Excel" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">
    </form>
    </div>