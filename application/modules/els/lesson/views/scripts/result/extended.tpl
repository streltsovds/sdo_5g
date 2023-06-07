<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css'); ?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css'); ?>
<script>
    $(document).ready(function(){
        var filter = $('#els-extended-group-filter');
        var group = $('#els-extended-group');
        var content = $('#els-extended-content');
        var defaultContent = content.html();

        var lastGroupSelect = $.cookie('lastGroupSelect');

        filter.on('change', function(){
            var filterVal = $(this).val();
            $.cookie('lastGroupSelect',filterVal);
            $('input[name="group_id"]').each(function(){
                content.html(defaultContent);
                group.find('.active').removeClass('active');
                if ($(this).val() == filterVal) {
                    $(this).parents('tr:first').show();
                } else {
                    $(this).parents('tr:first').hide();
                }
            });
        });

        if (lastGroupSelect = $.cookie('lastGroupSelect')) {
            filter.val(lastGroupSelect).trigger('change');
        } else {
            filter.val(0).trigger('change');
            $.cookie('lastGroupSelect',0);
        }

        $('.els-extended-user-interview').on('click', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            group.find('.active').removeClass('active');
            $(this).parents('tr:first').addClass('active');
            content.html('Загрузка...').load(url);
        });
    });
</script>

<?php if ($this->userUrl): ?>
    <script>
        $(document).ready(function(){
            $('#user_id_<?=$this->userId?>').parents('tr').addClass('active');
            var content = $('#els-extended-content');
            content.html('Загрузка...').load('<?=$this->userUrl?>');
        });
    </script>
<?php endif;?>

<div class="els-extended-users">
    <select id="els-extended-group-filter">
        <?php foreach($this->groups as $key => $value) {
            if ($value['new_count'] > 0) {
                echo '<option style="color: red;" value="'.$key.'">'.$value['name'].' (New +'.$value['new_count'].')'.'</option>';
            } else {
                echo '<option style="color: black;" value="'.$key.'">'.$value['name'].'</option>';
            }
        }
        ?>
    </select>

    <div id="els-extended-group">
        <table width="100%">
            <?php foreach($this->users as $user) {?>
                <tr style="display: none;">
                    <td>
                        <input type="hidden" name="group_id" value="<?php echo $user['group_id']; ?>">
                        <input type="hidden" name="user_id" id='user_id_<?=$user['MID']?>' value="<?php echo $user['MID']; ?>">
                        <?php echo $user['card']; ?>
                    </td>
                    <td>
                        <a class="els-extended-user-interview" href="<?php echo $user['url']; ?>"><?php echo $user['fio']; ?></a>&nbsp;<?php if ($user['is_new']) echo '<b><sup style="font-size: 0.8em; color: red;">New</sup></b>'; ?>
                        <br/>
                        <b class="els-extended-user-variant"><?php echo $user['interview_title']; ?></b>
                    </td>
                    <td>
                        <div class="<?php echo ($user['mark'] > 0) ? 'score_red' : 'score_gray'; ?> number_number">
                            <span align="center"><?php echo ($user['mark'] > 0) ? $user['mark'] : 'Нет'; ?></span>
                        </div>
                    </td>
                </tr>
            <?php }?>
        </table>
    </div>
</div>
<div id="els-extended-content" style="">
    <div class="els-extended-default">
        Нет данных для отображения.<br>
        Необходимо выбрать пользователя в меню слева.
    </div>
</div>