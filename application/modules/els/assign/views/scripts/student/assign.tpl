<?php if (count($this->userList)): ?>
<v-dialog :value="true" persistent content-class="dialog-reappointment">
    <v-card class="pa-8 modal-reappointment">
        <h3>Следующие пользователи уже проходили обучение на выбранном курсе(-ах):</h3>
        <v-list class="modal-reappointment__list">
            <?php foreach($this->usersName as $name): ?>
            <v-list-item>
                <?php echo $name ?>
            </v-list-item>
            <?php endforeach; ?>
        </v-list>
        <br/>
        <div class="modal-reappointment__buttons">
            <p>Вы действительно желаете назначить их на курс(ы) повторно?</p>
            <?php echo $this->form; ?>
        </div>
    </v-card>
</v-dialog>
<?php endif; ?>

<?php
$this->inlineScript()->captureStart();
?>
    $('#btn_all_submit').bind('click',function(e){
        $('#<?php echo $this->postMassField;?>').val($('#all_users').val());
console.log($('#<?php echo $this->postMassField;?>').val());
    });
     $('#btn_filter_submit').bind('click',function(e){
        $('#<?php echo $this->postMassField;?>').val($('#filtered_users').val());
    });
<?php
$this->inlineScript()->captureEnd();
?>