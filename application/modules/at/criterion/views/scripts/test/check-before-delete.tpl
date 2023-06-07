<?=_('Следующие курсы связаны с удаляемой квалификацией и после удаления квалификации поулчат статус "Не опубликован"');?>
<br />
<br />
<table class="main" width="100%">
 <tr><th><?php echo _('Курс')?></th><th><?php echo _('Компетенция/Квалификация')?></th></tr>
<?php
foreach ($this->subjects as $subject) {
    ?>
    <tr>
    <?php
    echo '<td>'.$subject['subject'].'</td><td>'.$subject['competence'].'</td>';
    ?>
    </tr>
    <?php
}
?>
</table>
<br />

<form id="check-before-delete" enctype="application/x-www-form-urlencoded" method="post" action="<?=$this->url ?>">
<input type="hidden" name="criterion_id" value="<?=$this->criterion_id ?>" id="criterion_id">
<input type="submit" name="submit" id="submit" value="Продолжить" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">
</form>
