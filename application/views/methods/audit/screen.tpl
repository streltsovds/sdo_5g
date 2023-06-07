<?php
    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/field-training.css'), 'screen, print');
?>
<div class="section <?php if ($this->readonly): ?>strawberry-field-readonly <?php endif; ?>strawberry-field strawberry-<?php echo $this->event->type?>" id="<?php echo $this->escape($this->event->type); ?>">
    <h1><?php echo $this->escape($this->event->name) ?> <a href="<?php echo $this->url(array('event_id' => null, 'module' => 'evaluation', 'controller' => 'list', 'action' => 'index', 'all' => $this->isChief ? 1 : null));?>" class="return"></a></h1>
    <div class="error-box"></div>
    <div class="strawberry-field-wrapper">
        <form method="POST">
            <div class="clearfix">
            <h2><?php echo $this->session->name; ?></h2>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Руководитель');?>:</div><div class="strawberry-field-card-value"><?php echo $this->cardLink('/user/list/view/user_id/' . $this->event->chief->MID); // @todo: baseUrl не работает ?><?php echo $this->escape($this->event->chief->getName())?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Пользователь');?>:</div><div class="strawberry-field-card-value"><?php echo $this->cardLink('/user/list/view/user_id/' . $this->event->user->MID); // @todo: baseUrl не работает ?><?php echo $this->escape($this->event->user->getName())?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Город');?>:</div><div class="strawberry-field-card-value"><?php echo $this->event->user->city ? $this->event->user->city : _('не определен'); ?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Дата проведения мероприятия');?>:</div><div class="strawberry-field-card-value"><?php echo $this->event->begin_date ? $this->event->begin_date : _('не определена'); ?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><label for="dateRoute"><?php echo _('Дата проверяемого маршрута');?></label>:</div>
            <div class="strawberry-field-card-value">
                <?php if (!$this->readonly): ?>
                <?php echo $this->datePicker('dateRoute');?>
                <?php else: ?>
                <?php echo ($dateRoute = $this->event->getsubmethod('dateRoute')) ? $dateRoute : _('не определена');?>
                <?php endif;?>
            </div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><label for="valueTotal"><?php echo _('Количество торговых точек');?></label>:</div>
            <div class="strawberry-field-card-value">
                <?php if (!$this->readonly): ?>
                <?php
                    $value_applicable_total = 0;
                    // TODO: может есть более простой способ как найти это значение.
                    //       важно, что-бы оно было >= значений критериев!!!
                    foreach($this->event->criteria as $criterion) {
                        $selectedCriterion = !empty($criterion->criterion_id) ? $this->event->selectedCriteria[$criterion->criterion_id] : $this->event->selectedCriteria[$criterion->custom_id];
                        $value_applicable = $selectedCriterion['applicable'] ? $selectedCriterion['applicable'] : HM_At_Session_Event_Method_AuditModel::VALUE_WEIGHT_DEFAULT;
                        $value_applicable_total = max($value_applicable_total, $value_applicable);
                    }
                ?>
                <input type="number" name="valueTotal" id="valueTotal" value="<?php echo $this->escape($value_applicable_total); ?>" min="0" step="1">
                <a href="#" id="valueTotal-apply"><?php echo _("Применить") ?></a>
                <?php else: ?>
                <?php echo ($valueTotal = $this->event->getsubmethod('valueTotal')) ? $valueTotal : _('не определено');?>
                <?php endif;?>
            </div></div>
            </div>
            <hr>

            <div class="table-wrapper clearfix">
                <input type="hidden" name="event_id" value="<?php echo $this->event->session_event_id?>">
                <table>
                    <col><col><col><col>
                    <thead>
                        <tr>
                            <th rowspan="2" class="task"><?php echo _('Список задач') ?></th>
                            <th colspan="2" class="count"><?php echo _('Количество торговых точек, где активность') ?></th>
                            <th rowspan="2" class="measured"><?php echo _('Выполнение бизнес-задачи, %') ?></th>
                        </tr>
                        <tr>
                            <th class="count-applicable"><?php echo _('применима') ?></th>
                            <th class="count-achieved"><?php echo _('выполнена') ?></th>
                        </tr>
                    </thead>
                    <tbody><?php
            				  foreach($this->event->criteria as $criterion): ?>
            				  <?php $selectedCriterion = !empty($criterion->criterion_id) ? $this->event->selectedCriteria[$criterion->criterion_id] : $this->event->selectedCriteria[$criterion->custom_id];?>
            				  <tr class="<?php echo $this->cycle(array("odd", "even"))->next() ?>">
            				      <td class="task<?php if ($selectedCriterion['isCustom']): ?> custom<?php endif;?>"><?php echo $this->escape($criterion->name) ?></td>
            				      <td class="count count-applicable">
            				          <?php
            				              $value_applicable = $selectedCriterion['applicable'] ? $selectedCriterion['applicable'] : HM_At_Session_Event_Method_AuditModel::VALUE_WEIGHT_DEFAULT;
            				              if ($value_applicable < 0) {
            				                  $value_applicable = 0;
            				              }
            				              if (!$this->readonly && ($value_applicable > $value_applicable_total)) {
            				                  $value_applicable = $value_applicable_total;
            				              }
            				          ?>
            				          <?php if (!$this->readonly): ?>
            				          <span class="input-wrapper"><input type="number" tabindex="1" name="criteria[<?php echo $criterion->criterion_id; ?>][applicable]" value="<?php echo $this->escape($value_applicable); ?>" min="0" step="1"></span>
            				          <?php else: ?>
            				          <span><?php echo $value_applicable; ?></span>
            				          <?php endif; ?>
            				      </td>
            				      <td class="count count-achieved">
            				          <?php
            				              $value_achieved = $selectedCriterion['achieved'];
            				              if ($value_achieved < 0) {
            				                  $value_achieved = 0;
            				              }
            				              if ($value_achieved > $value_applicable) {
            				                  $value_achieved = $value_applicable;
            				              }
            				          ?>
            				          <?php if (!$this->readonly): ?>
            				          <span class="input-wrapper"><input type="number" tabindex="2" name="criteria[<?php echo $criterion->criterion_id; ?>][achieved]" value="<?php echo $this->escape($value_achieved); ?>" min="0" step="1"></span>
            				          <?php else: ?>
            				          <span><?php echo $value_achieved; ?></span>
            				          <?php endif; ?>
            				      </td>
            				      <td class="measured">
            				          <?php
            				              $value_measured = 0;
            				              if ($value_applicable > 0) {
            				                  $value_measured = round(($value_achieved / $value_applicable) * 100);
            				              }
            				          ?>
            				          <span class="value"><?php echo $this->escape($value_measured) ?></span>
            				      </td>
            				  </tr>
            				  <?php endforeach;?>
            				  <?php if (!$this->readonly): ?>
            				  <tr class="<?php echo $this->cycle(array("odd", "even"))->next() ?> new-criteria added-criteria">
            				      <td class="task">
            				          <div class="input-wrapper"><input type="text" name="extra_criteria[][title]" placeholder="<?php echo $this->escape(_("Добавить новый критерий")); ?>" value=""></div>
            				      </td>
            				      <td class="count count-applicable">
            				          <span class="input-wrapper"><input type="number" name="extra_criteria[][applicable]" value="<?php echo $this->escape($value_applicable_total); ?>" disabled min="0" step="1"></span>
            				      </td>
            				      <td class="count count-achieved">
            				          <span class="input-wrapper"><input type="number" name="extra_criteria[][achieved]" disabled value="" min="0" step="1"></span>
            				      </td>
            				      <td class="measured">
            				          <span class="value">0</span>
            				      </td>
            				  </tr>
            				  <?php endif; // !$this->readonly ?>
            				  <?php
            				  /* TODO: Возможность удалять критерии после открытия формы на редактирование!
            				  */
            				  ?>
            				  </tbody>
                </table>
            </div>

            <?php if ($this->readonly): // зесь нужно выводить все, даже пустые ?>
                <h3><?php echo $this->event->getMemo(HM_At_Session_Event_Method_AuditModel::MEMO1); ?></h3>
                <div><?php echo nl2br($this->event->getMemoValue(HM_At_Session_Event_Method_AuditModel::MEMO1)); ?></div>
                <h3><?php echo $this->event->getMemo(HM_At_Session_Event_Method_AuditModel::MEMO2); ?></h3>
                <div><?php echo nl2br($this->event->getMemoValue(HM_At_Session_Event_Method_AuditModel::MEMO2)); ?></div>
            <?php else: // $this->readonly ?>
                <div class="textarea-fields clearfix">
                    <?php $tf1 = $this->id('tf'); $tf2 = $this->id('tf'); ?>
                    <div class="first">
                        <label for="<?php echo $this->escape($tf1); ?>"><?php echo $this->event->getMemo(HM_At_Session_Event_Method_AuditModel::MEMO1); ?>:</label>
                        <div class="textarea-wrapper"><textarea name="memo[<?php echo HM_At_Session_Event_Method_AuditModel::MEMO1;?>]" id="<?php echo $this->escape($tf1); ?>" placeholder="<?php echo $this->escape($this->event->getMemo(HM_At_Session_Event_Method_AuditModel::MEMO1)) ?>"></textarea></div>
                    </div>
                    <div class="second">
                        <label for="<?php echo $this->escape($tf2); ?>"><?php echo $this->event->getMemo(HM_At_Session_Event_Method_AuditModel::MEMO2); ?>:</label>
                        <div class="textarea-wrapper"><textarea name="memo[<?php echo HM_At_Session_Event_Method_AuditModel::MEMO2;?>]" id="<?php echo $this->escape($tf2); ?>" placeholder="<?php echo $this->escape($this->event->getMemo(HM_At_Session_Event_Method_AuditModel::MEMO2)) ?>"></textarea></div>
                    </div>
                </div>
            <?php endif; ?>
            <hr>
            <div class="form-submit audit-form-submit">
                <?php if (!$this->readonly): ?>
                <input type="hidden" id="finalize" name="finalize" value="0">
                <!--input type="submit"  onClick="javascript: return confirm('<?php echo _('Вы действительно хотите сохранить результат? В дальнейшем Вы сможете вернуться к заполнению формы.')?>')" value="<?php echo _('Сохранить'); ?>"-->&nbsp;
                <input type="submit"value="<?php echo _('Сохранить и закончить'); ?>">
                <?php else :?>
                <input type="button" onClick="javascript: window.print()" value="<?php echo _('Распечатать');?>">
                <input type="button" onClick="javascript: document.location.href = '<?php echo $this->url(array('event_id' => null, 'module' => 'evaluation', 'controller' => 'list', 'action' => 'index', 'all' => $this->isChief ? 1 : null));?>';" value="<?php echo _('Закрыть'); ?>">
                <?php endif;?>
            </div>
        </form>
    </div>
</div>
<?php if (!$this->readonly): ?>
<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function () {
    "use strict";

    function updateHandler (inputs, $value, event) {
        var values
          , original
          , measured = 0;

        original = [inputs[0].val(), inputs[1].val()];
        values = [parseInt(inputs[0].val(), 10) || 0, parseInt(inputs[1].val(), 10) || 0];
        if (values[0] < 0) {
            values[0] = 0;
        }
        if (values[0] > valueTotal) {
            values[0] = valueTotal;
        }
        if (values[1] < 0) {
            values[1] = 0;
        }
        if (values[0] < values[1]) {
            values[1] = values[0];
        }
        if (values[0] > 0) {
            measured = Math.round((values[1] / values[0]) * 100);
        }
        if (values[0] != original[0] || values[1] != original[1]) {
            inputs[0].val(values[0]);
            inputs[1].val(values[1] || '');
        }
        $value.text(measured);
        return values;
    }

    function findValuesAndUpdate ($row, event, user) {
        var values;
        values = updateHandler([
            $row.find('td.count-applicable input'),
            $row.find('td.count-achieved input')
        ], $row.find('td.measured .value'), event);
        if (user) {
            if (values[0] !== valueTotal) {
                $row.data('userEdited', values[0]);
            } else {
                //$row.data('userEdited', null);
            }
        }
    }

    var dialogActive;
    function changeApplicable ($input, $rows) {
        var currentValue    = $input.val()
          , currentValueInt = parseInt(currentValue, 10) || 0
          , deferred = $.Deferred()
          , needsUserInput = false;

        if (dialogActive || !currentValueInt) {
            deferred.reject();
        } else {
            needsUserInput = _.any($rows.get(), function (row) {
                var applicable = parseInt($(row).find('td.count-applicable input').val(), 10) || 0;
                return applicable > currentValueInt;
            });
            if (!needsUserInput) {
                deferred.resolve();
            } else {
                dialogActive = true;
                elsHelpers.confirm(<?php echo HM_Json::encodeErrorSkip(_("Значения колонки «Количество торговых точек, где активность применима» будут изменены в меньшую сторону!")) ?>, <?php echo HM_Json::encodeErrorSkip(_("Подтвердите изменение значения")) ?>, <?php echo HM_Json::encodeErrorSkip(array(
                    'ok'     => _("Изменить"),
                    'cancel' => _("Отменить")
                )) ?>).done(function () {
                    deferred.resolve();
                }).fail(function () {
                    deferred.reject();
                });
            }
        }
        deferred.done(function () {
            $rows.each(function () {
                var $input = $(this).find('td.count-applicable input')
                  , inputValueInt = parseInt($input.val(), 10) || 0;
                if ($(this).data('userEdited') != null && currentValueInt > $(this).data('userEdited')) {
                    $(this).find('td.count-applicable input').val($(this).data('userEdited'));
                } else {
                    $(this).find('td.count-applicable input').val(currentValueInt);
                }
            });
            $rowClone.find('td.count-applicable input').val(currentValueInt);
            valueTotal = currentValueInt;
        });
        deferred.fail(function () {
            $input.val(valueTotal);
        });
        deferred.always(function () {
            dialogActive = false;
        });

        return deferred.promise();
    }

    function getTaskRows ($form) {
        return  $form.find('table > tbody > tr');
    }

    var $form = $('#' + <?php echo HM_Json::encodeErrorSkip($this->event->type) ?>)
      , $tableRows = getTaskRows($form)
      , $rowClone = $form.find('table > tbody > tr.new-criteria').clone(true, true)
      , valueTotal = parseInt($form.find('input[name="valueTotal"]').val(), 10) || 0;
    $form.delegate('input[name="valueTotal"], table input', 'change input keydown keyup keypress paste', function (event) {
        var $tr
          , $this = $(this);
        if (event.type == 'input') {
            $form.undelegate('input[name="valueTotal"], table input', 'keydown keyup keypress paste');
        }

        if (!$this.closest('html').length) { return; }
        if (this.name == 'valueTotal') {
            (event.type == 'change') && changeApplicable($(this), $tableRows).done(function () {
                $tableRows.not('.new-criteria').each(function () {
                    findValuesAndUpdate($(this), event);
                });
            });
        } else {
            $tr = $(this).closest('tr');
            if (!$tr.is('.new-criteria')) {
                findValuesAndUpdate($tr, event, 'user');
            }
        }
    });
    $form.delegate('a#valueTotal-apply', 'click', function (event) {
        event.preventDefault();
        $form.find('input[name="valueTotal"]').trigger('change');
    });
    $form.delegate('form', 'submit', function (event) {
        var $form = $(this);
        var values = _.select($form.serializeArray(), function (obj) {
            return (/^criteria/.test(obj.name) || /^extra_criteria/.test(obj.name)) && /\[achieved\]$/.test(obj.name);
        });
        var allValuesFilled = _.all(values, function (obj) { return obj.value.length > 0; });
        var message;

        $.ui.errorbox.clear('all');
        if (!values.length || !allValuesFilled) {
            event.preventDefault();
            message = <?php echo HM_Json::encodeErrorSkip(_('Отчёт заполнен не полностью!')); ?>;
            $('html, body').animate({ scrollTop: 0 });
            $('<div>', {text: message}).appendTo($form).errorbox({ level: 'error' });
        } else if ($('#dateRoute').datepicker('getDate') == null) {
            event.preventDefault();
            message = <?php echo HM_Json::encodeErrorSkip(sprintf(_('Отчёт заполнен не полностью! Не заполнено поле «%s».'), _("Дата проверяемого маршрута"))); ?>;
            $('html, body').animate({ scrollTop: 0 });
            $('<div>', {text: message}).appendTo($form).errorbox({ level: 'error' });
        } else {
            message = <?php echo HM_Json::encodeErrorSkip(_('Вы действительно хотите сохранить результат и завершить работу с данным мероприятием? В дальнейшем Вы не сможете изменить результат оценки.')); ?>;
            $form.find('input[type="submit"]')
                .prop('disabled', true);
            if ($form.find('#finalize').val() != 1) {
                event.preventDefault();
                elsHelpers.confirm(message).done(function () {
                    $form.find('#finalize').val(1);
                    $form.submit();
                }).fail(function () {
                    $form.find('#finalize').val(0);
                }).always(function () {
                    $form.find('input[type="submit"]')
                        .prop('disabled', false);
                });
            }
        }
    });
    
    $form.delegate('table tr.added-criteria td.task input', 'change input keydown keyup keypress paste', function (event) {
        var $this = $(this)
          , value = $this.val()
          , $row = $this.closest('tr')
          , $next = $row.next('tr')
          , start = -1;

        if (!$this.closest('html').length) { return; }
        if (event.type == 'input') {
            $form.undelegate('table tr.added-criteria td.task input', 'keydown keyup keypress paste');
        }
        if ($.trim(value)) {
            prepareAddedRow($row);
            if (!$next.length) {
                start = Number($row.is('.odd'));
                $row.after($rowClone.clone(true, true));
            }
        } else {
           if ($next.length) {
               start = Number($row.is('.even'));
               _.defer(function () {
                   $row.remove();
                   $next.find('td.task input').focus();
               });
           } else {
               prepareNewRow($row);
           }
        }
        if (start != -1 && $row.get(0).parentNode) {
            $row.nextAll('tr').each(function (index) {
                $(this)
                    .addClass((index + start) % 2 ? 'even' : 'odd')
                    .removeClass((index + start) % 2 ? 'odd' : 'even')
            });
        }
        $tableRows = getTaskRows($form);
    });
    function prepareAddedRow ($row) {
        var rowId = _.uniqueId('rid');
        $row.removeClass('new-criteria');
        $row.find('input').prop('disabled', false);
        $row.find('input').each(function () {
            this.name = ('' + this.name).replace(/^extra_criteria\[[^\]]*\]/, 'extra_criteria['+ rowId +']');
        });
        return $row;
    }
    function prepareNewRow ($row) {
        $row.addClass('new-criteria');
        $row.find('td.count input').prop('disabled', true);
        $row.find('td.task input').val('');
        $row.find('input').each(function () {
            this.name = ('' + this.name).replace(/^extra_criteria\[[^\]]*\]/, 'extra_criteria[]');
        });
        return $row;
    }
});
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>