<?php if ($this->deputy): $item = $this->deputy; ?>
<?php echo sprintf(_('Вас замещает коллега: %s, c %s до %s'), $item['user']->getName(), $item['dateBegin'], $item['dateEnd']); ?></p>
<p><button class="close-assign"><?php echo _('Отменить')?></button></p>
<?php
    $cloaseAssignUrl = $this->url(array('module' => 'supervisor', 'controller' => 'deputy', 'action' => 'close', 'MID' => $item['user']->MID));
?>
<script type="text/javascript">
    $(function(){

        $('.close-assign').on('click', function() {
            window.location.href = '<?php echo $cloaseAssignUrl; ?>';
        });

    });
</script>

<?php elseif ($this->user): $item = $this->user; ?>
<p><?php echo sprintf(_('Вы замещаете Вашего коллегу: %s, c %s до %s'), $item['user']->getName(), $item['dateBegin'], $item['dateEnd']); ?></p>

    <?php if ($item['active']): ?>
    <p><button class="login-as"><?php echo _('Войти от имени')?></button></p>
    <?php
        $loginAsUrl = $this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'login-as', 'MID' => $item['user']->MID));
    ?>
    <script type="text/javascript">
        $(function(){

            $('.login-as').on('click', function() {
                window.location.href = '<?php echo $loginAsUrl; ?>';
            });

        });
    </script>
    <?php else:?>
    <?php echo sprintf("(Вход от имени замещаемого пользователя будет доступен с %s)", $item['dateBegin']);?>
    <?php endif; ?>

<?php else:?>
<p><?php echo _('На текущий момент времени замещений нет')?></p>
<?php endif;?>


