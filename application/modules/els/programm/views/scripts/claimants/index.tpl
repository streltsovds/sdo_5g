<v-card>
    <?php if (!$this->editable): ?>
    <v-card-text>
        <v-alert type="warning" outlined value="true">
            <?php echo sprintf(_('Невозможно отредактировать программу, так как в настоящий момент её проходят пользователи (%s)'), count($this->processes));?>
        </v-alert>
    </v-card-text>
    <?php endif; ?>
    <hm-programm-builder
            :all-items='<?php echo json_encode($this->items, JSON_FORCE_OBJECT); ?>'
            :include-items='<?php echo json_encode($this->events, JSON_FORCE_OBJECT); ?>'
            save-url="<?php echo $this->serverUrl($this->url(array('module' => 'programm', 'controller' => 'claimants', 'action' => 'assign', 'programm_id' => $this->programm->programm_id))); ?>"
            edit-url="<?php echo $this->url(array('module' => 'programm', 'controller' => 'claimants', 'action' => 'edit', 'baseUrl' => '', 'agreement_type' => ''));?>"
            :options='<?php echo json_encode($this->options); ?>'
            :actions='["remove", "add", "edit"]'
            <?php if (!$this->editable) echo 'read-only'; ?>
    >
        <template slot="allItemsTitle"><?php echo _('Все согласующие должности'); ?></template> //
        <template slot="includeItemsTitle"><?php echo _('Должности, включенные в программу согласования'); ?></template>
        <template slot="remove"><?php echo _('Удалить из программы');?></template>
        <template slot="edit"><?php echo _('Указать конкретную должность');?></template>
        <template slot="add"><?php echo _('Добавить');?></template>
        <template slot="selectItemLabel"><?php echo _('Выберите категорию'); ?></template>
    </hm-programm-builder>
</v-card>
<?php /*
<?php if (!$this->editable): ?>
<div class="error-box" id="error-box"><div title="" class="ui-widget ui-els-flash-message"><div class="ui-state-error ui-corner-all"><span class="ui-icon ui-icon-check"></span><div class="ui-message-here"><?php echo sprintf(_('Невозможно отредактировать программу, так как в настоящий момент её проходят пользователи (%s)'), count($this->processes));?></div></div></div></div>
<?php endif;?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forms.css'), 'screen,print');?>
<?php $pageId = $this->id('pa'); ?>
<?php $this->headLink()
    ->appendStylesheet( $this->baseUrl('css/content-modules/extended-page.css') )
    ->appendStylesheet( $this->baseUrl('css/content-modules/program-editor.css') ); ?>

<table width="100%" cellspacing="10">
    <tr>

<?php if (count($this->items)):?>
    <td width="50%">
        <h3 class="title"><?php echo _('Все согласующие должности'); ?></h3>
<div class="leftside" id="<?= $pageId; ?>-accordion">
    <?php foreach ($this->items as $item):?>
    <h3 class=""><a href="#"><span class="header"><?php echo $item['name']?></span></a></h3>
    <div class=""><div class="">
        <ul  class="programs-editor-sorted" id="<?= $pageId ?>-source">
            <?php foreach($item['subitems'] as $key => $title):?>
            <li <?php if (array_key_exists($key, $this->events)):?>class="ui-state-disabled"<?php endif;?>>
                <span><?php echo $title?></span>
                <input type="hidden" name="agreement_type[]" value="<?php echo $key?>">
                <span class="ui-icon ui-icon-close remove" title="<?php echo _('Удалить из программы');?>"></span>
                <?php if ($item['isCustom']): ?><span id="" class="ui-icon ui-icon-pencil edit" title="<?php echo _('Указать конкретную должность');?>" style="right: 30px;"></span><?php endif;?>
            </li>
            <?php endforeach;?>
        </ul>
    </div></div>
    <?php endforeach;?>
</div>
<br>

</td>
    <?php endif;?>
<td width="50%">

<h3 class="title"><?= _('Должности, включенные в программу согласования') ?></h3>
<form method="POST" action="<?php echo $this->serverUrl($this->url(array('module' => 'programm', 'controller' => 'claimants', 'action' => 'assign', 'programm_id' => $this->programm->programm_id)))?>" id="<?= $pageId ?>-sorted">
    <ul class="programs-editor-sorted">
        <?php if (count($this->events)):?>
            <?php foreach($this->events as $key => $name):?>
                <li>
                    <span><?php echo $name?></span>
                    <input type="hidden" name="agreement_type[]" value="<?php echo $key;?>">
                    <span class="ui-icon ui-icon-close remove" title="<?php echo _('Удалить из программы');?>"></span>
                    <?php if (HM_Agreement_AgreementModel::isStatic($key)): ?><span id="" class="ui-icon ui-icon-pencil edit" title="<?php echo _('Указать конкретную должность');?>" style="right: 30px;"></span><?php endif;?>
                </li>
            <?php endforeach;?>
        <?php endif;?>
    </ul>
    <fieldset class="noborder">
        <?php echo $this->modeCheckbox;?>
        <button class="toPost" <?php if (!$this->editable): ?>disabled<?php endif;?>><?php echo _('Сохранить'); ?></button>
    </fieldset>
</form>


</td></tr></table>

<?php // @todo: рефакторить! большой кусок js-кода продублирован  ?>
<?php $this->headScript()->captureStart(); ?>
(function () {

function trySave ($form, callback) {

<?php if (!$this->editable): ?>
    return true;
<?php endif;?>

    var key = '<?php echo $this->finalizesubmethod;?>';
    $('.input-finalize').remove();
    if ($('#mode_finalize').attr('checked')) {
        $form.append($('<input class="input-finalize" type="hidden" name="agreement_type[]" value="' + key +'">'));
    }
    $.ajax($form.prop('action'), {
        type: /^(GET|PUT|POST|DELETE|HEAD|OPTIONS)$/i.test($form.prop('method'))
            ? $form.prop('method').toUpperCase()
            : 'GET',
        data: $form.serializeArray()
    })
    .always(function() {
        if (callback) callback();
    });
}

$(document).ready(function () {

    var $source = $("#" + <?= HM_Json::encodeErrorSkip($pageId) ?> + '-source');

    $("#" + <?= HM_Json::encodeErrorSkip($pageId) ?> + '-accordion').accordion({
        autoHeight: false
    });
    $("#" + <?= HM_Json::encodeErrorSkip($pageId) ?> + '-source > li').draggable({
        appendTo: 'body',
        cursor: 'move',
        cursorAt: {top: 0, left: 0},
        helper: function () {
            return $('<div>').css({
                width:  $(this).outerHeight(),
                height: $(this).outerHeight(),
                marginTop: -1 * ($(this).outerHeight() / 2),
                marginLeft: -1 * ($(this).outerHeight() / 2)
            }).addClass('programs-editor-draggable-helper');
        },
        //containment: '#main .els-body:first',
        stop: function () {},
        connectToSortable: '#' + <?= HM_Json::encodeErrorSkip($pageId) ?> + '-sorted > ul.programs-editor-sorted'
    });
    $("body").on("click", ".toPost", function (){
        if (typeof $(this).closest('form').attr('id') != 'undefined') {
            trySave($(this).closest('form'));
        }
        return false;
    });
    $('#' + <?= HM_Json::encodeErrorSkip($pageId) ?> + '-sorted > ul.programs-editor-sorted').sortable({
        appendTo: 'body',
        cursor: 'move',
        cursorAt: {top: 0, left: 0},
        forcePlaceholderSize: true,
        placeholder: 'ui-state-highlight',
        axis: 'y',
        tolerance: 'pointer',
        receive: function (event, ui) {
            ui.item.addClass('ui-state-disabled');
        },
        remove: function () {
            //trySave($(this).closest('form'));
        },
        update: function () {
            //trySave($(this).closest('form'));
        },
        cursorAt: {top: 0, left: 0},
        helper: function (event, element) {
            return $('<div>').css({
                width:  element.outerWidth(),
                height: element.outerHeight(),
                left:   element.offset().left,
                marginTop: -1 * (element.outerHeight() / 2)
            }).addClass('programs-editor-draggable-helper');
        }//,
        //containment: '#main .els-body:first'
    }).delegate('li .remove', 'click', function (event) {
        var $li = $(event.target).closest('li')
          , $input = $li.find('input[type="hidden"]')
          , val = $input.val()
          , name = $input.prop('name')
          , $items
          , position
          , height
          , accordionContent
          , scrollTop
          , $ul = $li.closest('ul')
          , $form = $ul.closest('form');

        $items = $('.leftside li input[type="hidden"]').filter(function () {
            return $(this).val() === val && $(this).prop('name') === name;
        }).closest('li');

        position = $items.first().show().position();
        height = $items.first().outerHeight();
        $items.first().hide();

        accordionContent = $items.first().closest('.ui-accordion-content');

        scrollTop = accordionContent.scrollTop();
        if (position) { // временный хак с if (position); надо разобраться почему не определен position
            if (position.top < 0) {
            scrollTop = scrollTop + position.top - 10;
        } else if (position.top > (accordionContent.height() - height)) {
            scrollTop = scrollTop + position.top - accordionContent.height() + height + 10;
            }
        }

        if (accordionContent.scrollTop() != scrollTop) {
            accordionContent.animate({
                scrollTop: scrollTop
            }, function () {});
            $items.delay(400);
        }
        $items.removeClass('ui-state-disabled')
            .hide()
            .slideToggle('fast', function () {
                $(this).effect('highlight');
            });
        $li.slideToggle('fast', function () {
            $li.remove();
            $ul.sortable('refresh');
            //trySave($form);
        });
    }).delegate('li .edit', 'click', function (event) {
        var $li = $(event.target).closest('li')
          , $input = $li.find('input[type="hidden"]')
          , val = $input.val()
          , $ul = $li.closest('ul')
          , $form = $ul.closest('form');
        if (confirm('<?php echo _('Вы действительно хотите сохранить изменения в программе и перейти к выбору должности?');?>')) {
            trySave($form, function(){
                document.location.href = '<?php echo $this->url(array('module' => 'programm', 'controller' => 'claimants', 'action' => 'edit', 'baseUrl' => '', 'agreement_type' => ''));?>' + val;
            });
        }
  });


});

})();

<?php $this->headScript()->captureEnd(); ?>
*/ ?>