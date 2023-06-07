<?php

    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/field-training.css'), "screen, print");

    $onClick = new Zend_Json_Expr('function (dtnode, event) {
        event.preventDefault();
        if (!dtnode.data.isFolder) {
            dtnode.select(!dtnode.isSelected());
        }
    }');
    $onKeydown = new Zend_Json_Expr('function (dtnode, event) {
        if (!dtnode.data.isFolder && _.indexOf([32, 13], event.which) !== -1) {
            event.preventDefault();
            dtnode.select(!dtnode.isSelected());
        }
    }');
    $letters = array('a','b','c','d','e','f');

    
?>
<div class="section <?php if ($this->readonly): ?>strawberry-field-readonly <?php endif; ?>strawberry-field strawberry-<?php echo $this->event->type?>" id="<?php echo $this->escape($this->event->type); ?>">
    <h1><?php echo $this->escape($this->event->name) ?> <a href="<?php echo $this->url(array('event_id' => null, 'module' => 'evaluation', 'controller' => 'list', 'action' => 'index', 'all' => $this->isChief ? 1 : null));?>" class="return"></a></h1>
    <div class="error-box"></div>
    <div class="strawberry-field-wrapper">
        <form method="POST">
            <div class="clearfix"><h2><?php echo $this->session->name; ?></h2>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Руководитель');?>:</div><div class="strawberry-field-card-value"><?php echo $this->cardLink('/user/list/view/user_id/' . $this->event->chief->MID); // @todo: baseUrl не работает ?><?php echo $this->escape($this->event->chief->getName())?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Пользователь');?>:</div><div class="strawberry-field-card-value"><?php echo $this->cardLink('/user/list/view/user_id/' . $this->event->user->MID); // @todo: baseUrl не работает ?><?php echo $this->escape($this->event->user->getName())?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Город');?>:</div><div class="strawberry-field-card-value"><?php echo $this->event->user->city ? $this->event->user->city : _('не определен'); ?></div></div>
            <div class="clearfix"><div class="strawberry-field-card-label"><?php echo _('Дата проведения мероприятия');?>:</div><div class="strawberry-field-card-value"><?php echo $this->event->begin_date ? $this->event->begin_date : _('не определена'); ?></div></div></div>
            <hr>

            <div class="clearfix">
                <input type="hidden" name="event_id" value="<?php echo $this->event->session_event_id?>">
                <?php foreach ($this->event->forest as $criterion_id => $tree) :?>
                <div class="section <?php echo array_shift($letters)?>"><div class="wrapper">
                    <h3><?php echo $this->event->criteria[$criterion_id]->name; ?></h3>
                    <div class="tree"><?php echo $this->uiDynaTree(
                        $treeId = $this->id('t'),
                        $this->htmlTree($tree, 'htmlTree'),
                        array(
                            'title' => '',
                            'clickFolderMode' => 2,
                            'onClick'   => $onClick,
                            'onKeydown' => $onKeydown,
                            'onQuerySelect' => new Zend_Json_Expr($this->readonly ? 'function () { return false; }' : 'null'), 
                            'onSelect' => new Zend_Json_Expr($this->readonly ? 'null' : 'function (flag, dtnode) {
                                if (!dtnode.data.isFolder) {
                                    updateList('.HM_Json::encodeErrorSkip($treeId).', '.HM_Json::encodeErrorSkip($listId = $this->id('l')).', dtnode.tree);
                                }
                            }'),
                            'checkbox' => true
                        )
                    ); ?></div>
                    <div class="selected"><h4><?php echo _('Оценки по выбранным критериям') ?>:</h4>
                    <ul class="criteria-list" id="<?php echo $this->escape($listId); ?>" data-criterion-id="<?php echo $this->escape($criterion_id); ?>">
                        <?php $index = 0; ?>
                        <?php foreach ($this->event->selectedCriteria[$criterion_id] as $selectedCriterion): ?>
                        <?php echo $this->partial('_criteria-row.tpl', array(
                            'listId' => $listId,
                            'key' => $selectedCriterion['key'],
                            'title' => $selectedCriterion['title'],
                            'value' => $selectedCriterion['value'],
                            'isCustom' => $selectedCriterion['isCustom'],
                            'total' => count($this->event->selectedCriteria[$criterion_id]),
                            'index' => ++$index,
                            'readonly' => $this->readonly
                        )); ?>
                        <?php endforeach; ?>
                        <?php if (!$this->readonly): ?>
                        <?php echo $this->partial('_criteria-row.tpl', array(
                            'criterionId' => $criterion_id,
                            'index' => ++$index,
                            'readonly' => $this->readonly
                        )); ?>
                        <?php while ($index < 3) { $index++; ?>
                        <li class="empty<?php if ($index == 3): ?> last<?php endif; ?>"></li>
                        <?php } ?>
                        <?php endif; ?>
                    </ul></div>
                </div>
                </div>
                <?php endforeach;?>
            </div>
            <?php if ($this->readonly): // зесь нужно выводить все, даже пустые ?>
                <h3><?php echo $this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO1); ?></h3>
                <div><?php echo $this->event->getMemoValue(HM_At_Session_Event_Method_FieldModel::MEMO1); ?></div>
                <h3><?php echo $this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO2); ?></h3>
                <div><?php echo $this->event->getMemoValue(HM_At_Session_Event_Method_FieldModel::MEMO2); ?></div>
                <h3><?php echo $this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO3); ?></h3>
                <div><?php echo $this->event->getMemoValue(HM_At_Session_Event_Method_FieldModel::MEMO3); ?></div>
            <?php else: // $this->readonly ?>

            <div class="textarea-fields clearfix">
                <?php $tf1 = $this->id('tf'); $tf2 = $this->id('tf'); $tf3 = $this->id('tf'); ?>
                <?php /* textarea здесь идут не по порядку, а 1 → 3 → 2 */ ?>
                <div class="first">
                    <label for="<?php echo $this->escape($tf1); ?>"><?php echo $this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO1); ?>:</label>
                    <div class="textarea-wrapper"><textarea name="memo[<?php echo HM_At_Session_Event_Method_FieldModel::MEMO1;?>]" id="<?php echo $this->escape($tf1); ?>" placeholder="<?php echo $this->escape($this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO1)); ?>"></textarea></div>
                </div>
                <div class="third">
                    <label for="<?php echo $this->escape($tf3); ?>"><?php echo $this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO3); ?>:</label>
                    <div class="textarea-wrapper"><textarea name="memo[<?php echo HM_At_Session_Event_Method_FieldModel::MEMO3;?>]" id="<?php echo $this->escape($tf3); ?>" placeholder="<?php echo $this->escape($this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO3)); ?>)"></textarea></div>
                </div>
                <div class="second">
                    <label for="<?php echo $this->escape($tf2); ?>"><?php echo $this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO2); ?>:</label>
                    <div class="textarea-wrapper"><textarea name="memo[<?php echo HM_At_Session_Event_Method_FieldModel::MEMO2;?>]" id="<?php echo $this->escape($tf2); ?>" placeholder="<?php echo $this->escape($this->event->getMemo(HM_At_Session_Event_Method_FieldModel::MEMO2)); ?>"></textarea></div>
                </div>
            </div>
            <?php endif; // readonly ?>
            <hr>
            <div class="form-submit coach-form-submit">
                <?php if (!$this->readonly): ?>
                <input type="hidden" id="finalize" name="finalize" value="0">
                <!--input type="submit"  onClick="javascript: return confirm('<?php echo _('Вы действительно хотите сохранить результат? В дальнейшем Вы сможете вернуться к заполнению формы.')?>')" value="<?php echo _('Сохранить'); ?>"-->&nbsp;
                <input type="submit" value="<?php echo _('Сохранить и закончить'); ?>">
                <?php else :?>
                <input type="button" onClick="javascript: window.print()" value="<?php echo _('Распечатать');?>">
                <input type="button" onClick="javascript: document.location.href = '<?php echo $this->url(array('event_id' => null, 'module' => 'evaluation', 'controller' => 'list', 'action' => 'index', 'all' => $this->isChief ? 1 : null));?>';" value="<?php echo _('Закрыть'); ?>">
                <?php endif;?>
            </div>
        </form>

    </div>
</div>
<?php if (!$this->readonly): ?>
<?php $itemTemplateId = $this->id('lti'); ?>
<script type="text/template" id="<?php echo $this->escape($itemTemplateId); ?>"><?php echo $this->partial('_criteria-row.tpl', array(
    'readonly' => false,
    'mode' => 'template'
)) ?></script>
<?php $this->inlineScript()->captureStart(); ?>
function updateList () {
    var args = arguments;

    _.defer(function () { _updateList.apply(null, args); });
}
function _updateList (treeId, listId, tree) {
    var selected = tree.getSelectedNodes()
      , $list = $('#' + listId)
      , $listItems = $list.children('li')
      , $visibleItems = $listItems.filter(':visible')
      , $emptyItems = $listItems.filter('.empty')
      , $addedItems = $listItems.filter('.added-criteria')
      , selectedNodes = [];

    if (!$list.data('dtTree')) {
        $list.data('dtTree', tree);
    }

    if (!_updateList.itemTemplate) {
        _updateList.itemTemplate = _.template($('#' + <?php echo HM_Json::encodeErrorSkip($itemTemplateId) ?>).html());
    }

    _.each(selected, function (node) {
        var id = 'coach-' + listId + '-' + node.data.key
          , key = node.data.key
          , $item = $('#' + id);
        if (!$item.length) {
            $item = $(_updateList.itemTemplate({
                'title': node.data.title,
                'key': node.data.key,
                'listId': listId
            })).attr('id', id).data('dtKey', key);
        }
        selectedNodes.push($item.get(0));
        if (!$item.is(':visible') || !$item.parent().length) {
            $item.insertBefore($addedItems.length ? $addedItems.first() : $emptyItems.first()).show()
                .find('input').prop('disabled', false);
        }
    });

    selectedNodes = selectedNodes.concat($listItems.filter('.added-criteria').not('.new-criteria').get());
    selectedNodes = _.uniq(selectedNodes);
    $listItems.not(selectedNodes)
        .not('.new-criteria').hide()
        .find('input').prop('disabled', true);

    updateEmptyRows(selectedNodes.length + ($addedItems.length ? 1 : 0), $emptyItems, $list);
}
function updateEmptyRows (selectedNodesLength, $emptyItems, $list) {
    $emptyItems.hide();
    while (selectedNodesLength < 3/* > */) {
        $emptyItems.eq(2 - selectedNodesLength).show();
        selectedNodesLength++;
    }
    $list.children('li:visible').removeClass('last').last().addClass('last');
}
function prepareNew ($new) {
    $new.addClass('new-criteria');
    $new.find('input:not(.criteria-title)').prop('disabled', true);
    $new.find('input.criteria-title').val('');
    $new.find('input').each(function () {
        this.name = ('' + this.name).replace(/^extra_criteria\[[^\]]*\]/, 'extra_criteria[]');
    });
    return $new;
}
function updateAddedItem ($added) {
    var rowId = _.uniqueId('rid');
    $added.removeClass('new-criteria');
    $added.find('input:not(.criteria-title)').prop('disabled', false);
    
    $added.find('input').each(function () {
        this.name = ('' + this.name).replace(/^extra_criteria\[[^\]]*\]/, 'extra_criteria['+ rowId +']');
    });
    return $added;
}
$(document).delegate('#' + <?php echo HM_Json::encodeErrorSkip($this->event->type) ?> + ' form', 'submit', function (event) {
    var $form = $(this);
    var values = _.select($form.serializeArray(), function (obj) {
        return /^criteria/.test(obj.name) || (/^extra_criteria/.test(obj.name) && /\[value\]$/.test(obj.name));
    });
    var allValuesFilled = _.all(values, function (obj) { return obj.value.length > 0; });
    var valueTotal = parseInt($('#valueTotal').val(), 10);
    var message;

    $.ui.errorbox.clear('all');
    if (!values.length) {
        event.preventDefault();
        message = <?php echo HM_Json::encodeErrorSkip(_('Отчёт заполнен не полностью!')); ?>;
        $('html, body').animate({ scrollTop: 0 });
        $('<div>', {text: message}).appendTo($form).errorbox({ level: 'error' });
    } else if (!allValuesFilled) {
        event.preventDefault();
        message = <?php echo HM_Json::encodeErrorSkip(_('Отчёт заполнен не полностью!')); ?>;
        $('html, body').animate({ scrollTop: 0 });
        $('<div>', {text: message}).appendTo($form).errorbox({ level: 'error' });
    } else if (_.isNaN(valueTotal) || valueTotal < 0) {
        event.preventDefault();
        message = <?php echo HM_Json::encodeErrorSkip(sprintf(_('Отчёт заполнен не полностью! Не заполнено поле «%s».'), _("Количество торговых точек"))); ?>;
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
$(document).delegate('#' + <?php echo HM_Json::encodeErrorSkip($this->event->type) ?> + ' .criteria-list .added-criteria input.criteria-title', 'change input keydown keyup keypress paste', function (event) {
    var $this = $(this)
      , value = $this.val()
      , $row = $this.closest('li')
      , $next = $row.next('li:not(.empty)')
      , $list = $row.closest('ul')
      , $clone;

    if (!$this.closest('html').length) { return; }
    if (event.type == 'input') {
        $(document).undelegate('#' + <?php echo HM_Json::encodeErrorSkip($this->event->type) ?> + ' .criteria-list .added-criteria input.criteria-title', 'keydown keyup keypress paste');
    }
    if ($.trim(value)) {
        updateAddedItem($row);
        if (!$next.length) {
            $row.after(prepareNew($row.clone(false, false)));
            updateEmptyRows($list.children('li:visible').not('.empty').length, $list.children('li.empty'), $list);
        }
    } else {
        if ($next.length) {
            _.defer(function () {
                $row.remove();
                $next.find('input.criteria-title').focus();
                updateEmptyRows($list.children('li:visible').not('.empty').length, $list.children('li.empty'), $list);
            });
        } else {
            prepareNew($row);
        }
    }
});
$(document).delegate('#' + <?php echo HM_Json::encodeErrorSkip($this->event->type) ?> + ' .criteria-list .remove-criteria', 'click', function (event) {
    var key = $(this).closest('li').data('dtKey')
      , tree = $(this).closest('ul').data('dtTree');

    event.preventDefault();

    if (tree && key) {
        tree.visit(function (node) {
            if (node.data.key == key) {
                node.select(false);
            }
        });
    }
});
$(document).delegate('#' + <?php echo HM_Json::encodeErrorSkip($this->event->type) ?> + ' .criteria-list .els-icon', 'click', function (event) {
    var $others = $(this).parent().find('.els-icon.cross, .els-icon.check').not(this)
      , $input = $(this).closest('li').find('input.criteria-value');
    if ($(this).hasClass('cross')) {
        $(this).toggleClass('cross-checked');
    } else if ($(this).hasClass('check')) {
        $(this).toggleClass('check-checked');
    }
    if ($(this).hasClass('cross-checked')) {
        $others.removeClass('check-checked');
        $input.val('<?php echo $this->event->scale->getValueId(HM_At_Session_Event_Method_FieldModel::SCALE_VALUE_NEGATIVE);?>');
    } else if ($(this).hasClass('check-checked')) {
        $others.removeClass('cross-checked');
        $input.val('<?php echo $this->event->scale->getValueId(HM_At_Session_Event_Method_FieldModel::SCALE_VALUE_POSITIVE);?>');
    } else {
        $input.val('');
    }
});
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>