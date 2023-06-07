<?php $loadUrl = $this->url(array('module' => 'infoblock', 'controller' => 'timesheet', 'action' => 'load'));?>
<?php $saveUrl = $this->url(array('module' => 'infoblock', 'controller' => 'timesheet', 'action' => 'save'));?>
<?php $deleteUrl = $this->url(array('module' => 'infoblock', 'controller' => 'timesheet', 'action' => 'delete'));?>
<hm-timesheet
    save-url="<?php echo $saveUrl;?>"
    delete-url="<?php echo $deleteUrl;?>"
    load-url="<?php echo $loadUrl;?>"
    chart-url="/infoblock/timesheet/get-data/format/json"
></hm-timesheet>
<?php /*

<!--<div class="timesheet">-->
<!--    <div class="timesheet__content">-->
<!--        <div class="timesheet__content-wrapper"></div>-->
<!--        <div class="timesheet__form-wrapper">-->
<!--            <form action="--><?php//= $saveUrl ?><!--" method="post" id="timesheet__form">-->
<!--                <div class="timesheet__item timesheet__item-tofill">-->
<!--                    <select name="" id="" class="timesheet__select valid" required>-->
<!--                        <option value="" hidden selected disabled="disabled">Вид деятельности</option>-->
<!--                        --><?php //foreach ($this->actionTypes as $actionType): ?>
<!--                            <option value="--><?php //= $actionType->classifier_id ?><!--">--><?php //= $actionType->name ?><!--</option>-->
<!--                        --><?php //endforeach; ?>
<!--                    </select>-->
<!--                    <input class="timesheet__main-input-el valid" type="text" name="" id="" placeholder="описание" required/>-->
<!--                    <span class="timesheet__form-time">-->
<!--                        <label>-->
<!--                            c-->
<!--                            <input class="timesheet__timecontrol valid" type="time" name="" id="" placeholder="в формате hh:mm" min="06:00" max="19:00" pattern="[0-9]{2}:[0-9]{2}" required />-->
<!---->
<!--                        </label>-->
<!--                        <label>-->
<!--                            по-->
<!--                            <input class="timesheet__timecontrol valid" type="time" name="" id="" min="06:00" max="19:00" placeholder="в формате hh:mm"  pattern="[0-9]{2}:[0-9]{2}" required />-->
<!--                        </label>-->
<!--                        <span class="timesheet__time-valid" hidden>Время окончания деятельности должно быть больше времени начала</span>-->
<!--                    </span>-->
<!--                    <button class="timesheet__addBtn">Добавить</button>-->
<!--                </div>-->
<!--                <button class="timesheet__savebtn">Сохранить изменения</button>-->
<!--            </form>-->
<!--        </div>-->
<!---->
<!---->
<!--    </div>-->
<!---->
<!--    <template id="template">-->
<!--        <div class="timesheet__item timesheet__item--filled">-->
<!--            <span class="timesheet__select timesheet__select--filled"></span>-->
<!--            <span class="timesheet__main-input"></span>-->
<!--            <span class="timesheet__time">-->
<!--                <span class="timesheet__time-from">-->
<!--                    c-->
<!--                    <span></span>-->
<!--                </span>-->
<!--                <span class="timesheet__time-to">-->
<!--                    по-->
<!--                    <span></span>-->
<!--                </span>-->
<!--                <button class="timesheet__deleteBtn">Удалить</button>-->
<!--            </span>-->
<!---->
<!--        </div>-->
<!--    </template>-->
<!---->
<!--    <aside class="timesheet__pie-chart" id="timesheet__pie-chart">-->
<!--        --><?php //echo $this->chartJS(
//            array(),
//            array(),
//            array(
//                'id' => 'timesheet',
//                'type' => 'ampie',
//            )
//        );?>
<!--    </aside>-->
<!--</div>-->
<!---->
<!---->
<?php //$this->inlineScript()->captureStart(); ?>
<!--hm.timesheet.getData('--><?php //echo $loadUrl?><!--')-->
<!---->
<!--hm.timesheet.handleFormSubmit('#timesheet__form', '.timesheet__time-valid', '.timesheet__form-time')-->
<!---->
<!--document.querySelector('.timesheet__savebtn').addEventListener('click', function(e)-->
<!--{-->
<!--    e.preventDefault()-->
<!--    hm.timesheet.handleSave('--><?php //echo $saveUrl?><!--')-->
<!--})-->
<!---->
<?php //$this->inlineScript()->captureEnd(); ?>

<?php */ ?>
