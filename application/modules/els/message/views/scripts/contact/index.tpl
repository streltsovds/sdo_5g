<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->actions(); ?>
<?php endif; ?>
<style type="text/css">
    .hm-contact-item {
        border: 1px solid #c4c4c4;
        border-radius: 2px;
        min-height: 150px;
        width: 342px;
        float: left;
        background-color: #fff;
        margin: 0px 30px 30px 0px;
        padding: 18px;
    }
    .hm-contact-clear {
        clear: left;
    }
    .hm-contact-item-photo {
        width: 95px;
        height: 120px;
        float: left;
        background-size: 95px auto;
        background-position: center;
        background-repeat: no-repeat;
    }
    .hm-contact-item-description {
        margin-left: 113px;
        line-height: 17px;
    }
    .hm-contact-item-description h5 {
        font-size: 14px;
        font-weight: bold;
        font-family: Vardana, Arial;
    }
    .hm-contact-item-status {
        color: #a5a5a5;
    }
    .hm-contact-online {
        color: #49b150;
    }
    .hm-contact-role {
        display: inline-block;
        height: 23px;
        line-height: 23px;
        margin: 1px 2px;
        padding: 0 3px;
        background-color: #d5be27;
        color: #fff;
    }
    .hm-contact-role-moderator {
        background-color: #f8614d;
	color: #fff;
    }
    .hm-contact-item-additional {
        padding-top: 15px;
    }
    .hm-contact-chat-invite-form {
        height: 1px;
        width: 1px;
        display: ibline-block;
    }
    
</style>

	<?php if (!$this->subject) :?>
    	<div><?php echo _('В данном представлении отображаются только модераторы портала');?></div><br/>
    <?php endif; ?>
    <div id="hm-contacts">
        <?php
        foreach ($this->items as $item) {
            ?>
            <div class="hm-contact-item">
                <div>
                    <div class="hm-contact-item-photo" style="background-image: url(<?php echo $this->baseUrl($item['photo']); ?>);"></div>
                    <div class="hm-contact-item-description">
                        <h5><?php echo $item['fio'] ?></h5>
                        <?php if (!empty($item['position'])): ?><div class="hm-contact-item-position"><?php echo $item['position']; ?></div><?php endif;?>

                        <div class="hm-contact-item-status"><?php echo ($item['online'] ? '<div class="hm-contact-online">'._('Онлайн') : '<div class="hm-contact-offline">'.$item['last_visit']) ?></div></div>

                        <div class="hm-contact-item-additional">
                            <?php if (is_array($item['roles']) && count($item['roles'])):?>
                                <?php foreach ($item['roles'] as $role):?>
                                    <span class="hm-contact-role<?php echo (!$item['is_specialist'] ? ' hm-contact-role-moderator' : '') ?>"><?php echo $role; ?></span>
                                <?php endforeach;?>
                            <?php else: ?>
                                <span class="hm-contact-role<?php echo (!$item['is_specialist'] ? ' hm-contact-role-moderator' : '') ?>"><?php echo $item['role']; ?></span>
                            <?php endif;?>
                        </div>

                        <div style="margin-top: 15px;"></div>
                        <?php if (!empty($item['EMail']) && $this->enablePersonalInfo): ?><div><?php echo _('Email') . ': <b>' . $item['EMail'] . '</b>'; ?></div><?php endif;?>
                        <?php if (!empty($item['Phone']) && $this->enablePersonalInfo): ?><div><?php echo _('Рабочий телефон') . ': <b>' . $item['Phone'] . '</b>'; ?></div><?php endif;?>
                        <?php if (!empty($item['Fax']) && $this->enablePersonalInfo): ?><div><?php echo _('Мобильный телефон') . ': <b>' . $item['Fax'] . '</b>'; ?></div><?php endif;?>
                        <div style="margin-top: 15px;">
                            <?php if ($item['online']): ?>
                            <form class="hm-contact-chat-invite-form" action="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'carousel', 'action' => 'index')) ?>">
                                <input type="hidden" name="users[]" value="<?php echo $item['MID']; ?>">
                                <input type="submit" value="" style="visibility: hidden;">
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="hm-contact-clear"></div>
                </div>
                <?php if ($item['online']): ?>
                    <a href="#" class="hm-contact-chat-invite-link"><?php echo _('Пригласить в чат'); ?></a><br>
                <?php endif; ?>
                <a href="<?php echo $this->url(array('module' => 'message', 'controller' => 'send', 'action' => 'index', 'MID' => $item['MID'])); ?>" class=""><?php echo _('Отправить сообщение'); ?></a>


            </div>
            <?php
        }
        ?>
        <div class="hm-contact-clear"></div>
    </div>
    <script>
        $(function() {
            var $items = $('#hm-contacts .hm-contact-item'),
                currentTop = null;
            
            $items.each(function() {
                var $item = $(this);
                
                if (currentTop === null) {
                    currentTop = $item.offset().top;
                }
                // новая строка - вставляем разделяющий DIV, чтоб не развалилось, если
                // какая-то карточка пользователя больше других по высоте
                if ($item.offset().top != currentTop) {
                    $('<div class="hm-contact-clear"></div>').insertBefore($item);
                    currentTop = $item.offset().top;
                };
            });
            
            $('.hm-contact-chat-invite-link').on('click', function(e) {
                $(this).parent().find('.hm-contact-chat-invite-form').submit();
                e.preventDefault();
            });
        });
    </script>
    <?php
    echo $this->paginationControl($this->paginator, 'Sliding', 'contact/pagination.tpl', array());
?>