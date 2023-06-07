<?php if ($this->fact && $this->fact->text):?>
    <div class="hm-user-content">
        <?php echo $this->fact->text;?>
    </div>
<?php else: ?>
        <hm-empty > 
            <?php echo _('Нет данных для отображения') ?>
        </hm-empty>
<?php endif; ?>
<?php if($this->isModerator):?>
    <hm-actions-edit url="<?php echo $this->baseUrl($this->url(array('module' => 'infoblock', 'controller' => 'interesting-fact', 'action' => 'index')))?>"/>
<?php endif;?>