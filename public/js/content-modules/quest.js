$(document).undelegate('.at-form-quest');
// XXX: По какой-то причине preventDefault не работает на ссылке с target
//      по крайней мере в Chrome 21
$(document).delegate('.at-form-progress .item a, .at-form a.at-form-submit', 'mousedown.at-form-quest', function (event) {
	var target;
	if (target = $(this).attr('target')) {
		$(this)
			.data('target', target)
			.removeAttr('target');
	}
});
$(document).delegate('.at-form-progress .item a, .at-form a.at-form-submit, .at-form a.progress_stop', 'click', function (event) {
    event.preventDefault();
});
$(document).delegate('.at-form-progress .item a, .at-form a.at-form-submit, .at-form a.progress_stop', 'mousedown.at-form-quest', function (event) {

    if (window.hm && hm.QuestRating && hm.QuestRating.current && hm.QuestRating.current.hasConflict()) {
        elsHelpers.alert(
            HM._('Невозможно перейти далее, так как на данной странице даны противоречивые ответы.'),
            HM._('Ошибка')
        );
        return;
    }

    var $this = $(this)
	  , target = $this.data('target')
	  , resourceUrl = $this.attr('href')
	  , $progress = $this.closest('.at-form').find('.at-form-progress:first')
	  , $progressItem
	  , $buttons
	  , $target
	  , $form
	  , xhr;

	if (!resourceUrl) { return; }

	if (target) {
		$target = $('#' + target);
	} else {
		$target = $this.closest('.at-form').find('.at-form-container:first');
	}
	// target должен присутствовать, иначе может поломаться логика
	if (!$target.length) { return; }

	event.preventDefault();
	$form = $target.find('form');
	$buttons = $form.find('.ui-button');
	if ($target.data('saveXhr')) {
		$target.data('saveXhr').abort();
	}
	if ($target.data('loadXhr')) {
		$target.data('loadXhr').abort();
	}
	if ($target.data('confirmDeferred')) {
		$target.data('confirmDeferred').reject();
	}
    if ($this.hasClass('at-form-finalize')) {
        $form.find('[name="finalize"]').val(1);
    }
    if ($this.hasClass('at-form-stop')) {
        $form.find('[name="stop"]').val(1);
    }
	$buttons.button().button('disable');
	if ($progress.length && $this.data('itemId')) {
		$progressItem = $progress.find('.item a[data-item-id="'+ $this.data('itemId') +'"], .item span.close[data-item-id="'+ $this.data('itemId') +'"]').parent().addClass('item-loading');
	}
	xhr = submitForm($form);
	$target.data('saveXhr', xhr);
	xhr.fail(function () {
		$buttons.button('enable');
		if ($progressItem) {
			$progressItem.removeClass('item-loading');
		}
	}).done(function (data) {
		var deferred = $.Deferred();

		$target.data('confirmDeferred', deferred);
		if (data.progress != null && data.itemId != null) {
			$progress.find('.item a[data-item-id="'+ data.itemId +'"], .item span.close[data-item-id="'+ data.itemId +'"]').parent()
				.find('.ui-progressbar').progressbar('value', data.progress);
		}
		if (data.result && !data.confirm && !data.alert && !data.lightdialog) {
			deferred.resolve();
		} else if (data.confirm) {
			elsHelpers.confirm(data.confirm).done(function () {
				deferred.resolve();
			}).fail(function () {
				deferred.reject();
                                $form.find('[name="finalize"]').val(0);
			});
		} else if (data.alert) {
			elsHelpers.alert(data.alert).done(function () {
				deferred.reject();
			}).fail(function () {
				deferred.reject();
			});
		} else if (data.lightdialog) {

			yepnope({
				test: $.ui.lightdialog,
				nope: '/js/lib/jquery/jquery-ui.lightdialog.js',
				complete: function () { _.defer(function () { $(function () {

					$target.attr('href', data.lightdialog);
					$target.lightdialog({
						title: HM._('Коррекция результатов'),
						dialogClass: 'help-card',
						width: 750,
						modal: true,
						closeOnEscape: false,
						contentMaxHeight: 600
					}).lightdialog('open');
				}); }); }
			});
			deferred.reject();
/*			elsHelpers.lightdialog(data.lightdialog).done(function () {
				deferred.reject();
			}).fail(function () {
				deferred.reject();
			});*/
		} else {
			deferred.reject();
		}
		deferred.done(function () {
			if ($this.data('itemId')) {
				// Устанавливаем значения прогресса текущего на данный момент элемента
				//setItemProgress();
				$target.data('loadXhr', $.ajax(resourceUrl, {
					dataType: "html"
				}));
				$target.data('loadXhr').done(function (text) {
					$progress.find('.item').removeClass('item-current');
					$progressItem && $progressItem.addClass('item-current');
					$target.html(text);
					$target.trigger('els:content-changed');
                    $(document).scrollTop(0);
				}).always(function () {
					$progressItem && $progressItem.removeClass('item-loading');
					$buttons.button('enable');
					$("input[type='radio']").checkbox({cls:'jquery-radio-checkbox'});
					$('input:checkbox:not([safari])').checkbox();
					$('input[safari]:checkbox').checkbox({cls:'jquery-safari-checkbox'});
                                        AMtranslated = false;
                                        generic();
                    //sortable
                    initSortableQuestions();
                    //$('.tooltip').bt({killTitle: false}); // что такое bt?
				});
				// Устанавливаем текущий элемент в прогрессе
				//setProgress(nextItemId);
			} else {
				window.location.href = resourceUrl;
			}
		}).fail(function () {
			$progressItem && $progressItem.removeClass('item-loading');
			$buttons.button('enable');
		});
	});
});

(function () {

function updateRow ($row) {
	if (!$row.length) { return }
	updateRowRaw($row);
	if ($row.hasClass('quest-item-rowgroup-item')) {
		updateRowgroupRaw($row.prevAll('.quest-item-rowgroup:first'));
	}
}

function hasSelectedValue ($row) {
	var $inputs;

	if (!$row.length) { return }
	
	$inputs = $row.find('input[type="radio"]');
	return $inputs.length && $inputs.filter(':checked').length;
}

function updateRowRaw ($row) {
	if (!$row.length) { return }

	if (hasSelectedValue($row)) {
		$row.addClass('valueselected');
	} else {
		$row.removeClass('valueselected');
	}
}

function updateRowgroupRaw ($rowgroup) {
	var $items
	  , allSelected;

	if (!$rowgroup.length) { return }

	$items = $rowgroup.nextUntil('.quest-item-rowgroup');
	allSelected = _.all($items.get(), function (item) {
		return hasSelectedValue($(item));
	});
	if (allSelected) {
		$rowgroup.addClass('valueselected');
	} else {
		$rowgroup.removeClass('valueselected');
	}
}

function updateTablesOnPage () {
	$('.at-form-container table tbody tr.quest-item-row').each(function () {
		updateRowRaw($(this));
	});
	$('.at-form-container table tbody tr.quest-item-rowgroup').each(function () {
		updateRowgroupRaw($(this));
	});
}

$(document).ready(updateTablesOnPage);
$(document).delegate('.at-form-container', 'els:content-changed.at-form-quest', updateTablesOnPage);

$(document).delegate('.at-form-container table input[type="radio"]', 'change.at-form-quest', function (event) {
	updateRow($(event.target).closest('.quest-item-row'));
});

})();

$(document).bind('ready.at-form-quest els:content-changed.at-form-quest', function () {
	$('.at-form-navpanel a.at-form-button').each(function () {
		$(this).button({ disabled: $(this).hasClass('ui-state-disabled') });
	});
})

$(document).ready(function () {
	/*$('#form-cluster').live('submit', function(){
		return false;
	});*/

    var $form = $('.at-form-container form');
    $form.on('submit', function(e){
        e.preventDefault();
    });

    var initialTimestamp = new Date().getTime(); //ms

    var quest_time_left = $('#quest_time_left').val(); //секунд
    var quest_time_limit = $('#quest_time_limit').val(); //секунд
    var $textContainer = $('#quest_timer');
    var $progress_percent = $('#progress_percent');
    var maxW = $progress_percent.parent().width();
    var interval = 1000; //частота обновления прогрессбара (ms)
    if (quest_time_limit <= 60) {
        interval = 300;
    }

    if (quest_time_left && quest_time_left > 0) {
        var timer = setInterval(function() {
            var currentTimestamp = new Date().getTime(),f
                delta = (currentTimestamp - initialTimestamp) / 1000,
                time_left = quest_time_left - delta;

            if (time_left == 60) {
                //осталось 60 сек
                $progress_percent.css('background-color', 'red');
            }
            if (time_left <= 0) {
                //время вышло
                clearInterval(timer);

                elsHelpers.alert(HM._('Истекло время выполнения: сохранены только ответы, полученные до истечения времени.')).done(function () {

                    // Переопределяем, т.к. $form выше закрепляется за формой первой страницы и при тесте на несколько страниц не работает!
                	var $form = $(document).find('.at-form-container form');

                    $form.find('[name="finalize"], [name="timestop"]').val(1);
                	$form.prop('action', 'save');

                	var xhr = submitForm($form);
                    xhr.done(function (data) {
                        top.location.href = $('#quest_redirect_url').val();
                    });
                });
                //редирект к результатам
                //window.location = $('#quest_redirect_url').val();
            }
            var w = maxW * (1 - time_left / quest_time_limit);
            $progress_percent.css('width', w);

            var time_str = Math.round(time_left)+' сек.';
            if (time_left >= 60) {
                time_str = Math.floor(time_left/60)+' мин. ' + Math.round(time_left%60)+' сек.';
            }
            $textContainer.html('&nbsp;'+time_str);
        }, interval);
    }


    $('#manyQuizzesBlock').on('click', '.quest-question .title img', function(){
        window.open($(this).attr('src'), 'img', 'menubar=no,toolbar=no,statusbar=no')
    });
});

function submitForm ($form) {
	var type = ($form.prop('method') || '').toUpperCase()
	  , url = $form.prop('action') || window.location.href;

	if (!/^(GET|POST)$/.test(type)) {
		type = 'GET';
	}
	return $.ajax(url, {
		type:     type,
		data:     $form.serializeArray(),
		dataType: 'json'
	});
}

function initSortableQuestions() {
    $('.eLS-sortable').each(function() {
        var $this = $(this),
            type = $this.data('type'),
            questionId = $this.data('questionid');

        switch (type) {
            case 'sorting':
                $this.sortable({
                    axis: 'y, x',
                    grid: [1, 1],
                    opacity: false,
                    delay: 5,
                    helper: 'clone',
                    tolerance: 'pointer',
                    placeholder: 'ui-state-highlight',
                    containment: '.els-content',
                    connectWith: ['.eLS-sortable-'+questionId],
                    dropOnEmpty: true,
                    handle: '.eLS-drag-handler',
                    stop: function(event, ui) {
                        ui.item[0].style.opacity = '';
                        ui.item[0].style.filter = '';
                        var serializedSortable = $(this).sortable('toArray');
                        for (var i = 0; i < serializedSortable.length; ++i) {
                            var element = document.getElementById(serializedSortable[i]);
                            if (!element) { continue; }
                            $(element).find('.eLS-drag-order ').text((i + 1).toString())
                            element.firstChild.value = (i + 1).toString();
                        }
                    }
                });
                break;
            case 'classification':
                $this.sortable({
                    axis: 'y, x',
                    grid: [1, 1],
                    opacity: false,
                    delay: 5,
                    helper: 'clone',
                    tolerance: 'intersect',
                    placeholder: 'ui-state-highlight',
                    containment: $('.at-form-container'),
                    connectWith: ['.eLS-sortable-'+questionId],
                    dropOnEmpty: true,
                    handle: '.eLS-drag-handler',
                    stop: function(event, ui) {
                        ui.item[0].style.opacity = '';
                        ui.item[0].style.filter = '';

                        var targetDroppable = $(ui.item[0]).closest('.eLS-sortable-'+questionId);
                        if (!targetDroppable.length) { return; }

                        if (targetDroppable.attr('id').indexOf('eLS-sortable') != 0) {
                            ui.item[0].firstChild.value = targetDroppable.attr('id');
                        } else {
                            ui.item[0].firstChild.value = "";
                        }
                    }
                });
                break;
        }
    });
}

$(function(){
    initSortableQuestions();
});
