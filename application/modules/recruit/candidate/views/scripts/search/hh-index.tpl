
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/vcandidate_search_result.css');?>  
<?php echo $this->listSwitcher(
      array('internal' => _('пользователей компании'), 'reinternal' => _('кандидатов, проходивших оценку'), 'external' => _('новых кандидатов')),
      array('module' => 'candidate', 'controller' => 'search', 'action' => 'index'),
      $this->listSwitcher
);?>

<?php if (count($this->searchResult)): ?>
<div class="els-grid">
<div id="vacancy-candidates-box" style="clear:both;" class="vacancy-<?php echo $this->vacancy->vacancy_id; ?>">
    <?php
        foreach ($this->searchResult as $resultItemKey => $resultItem) {
	     if (in_array($resultItemKey, $this->ignore)) continue;
            echo "<div class='vacancy-candidate hash-".$resultItem->addition('resumeHash')." response-".$resultItem->addition('response')."' id='hm-vacancy-".$resultItemKey."'>".
                    "<div class='hm-candidate-checkbox'><input class='hm-resume-checkbox' type='checkbox' value='".$resultItemKey."'></div>".
                    "<div class='hm-hh-candidate'>".
                        $resultItem->getRawHtmlDescription().
                    "</div>".
                 "</div>";
        }
    ?>
</div>
<div class="list-mass-actions">
<table cellspacing="0">
<tfoot><tr><td class="bottom-grid has-export first-cell last-cell" colspan="10">
<div class="massActions mass-actions">
<span class="massSelect">
                    <strong><?php echo _('Для выбранных элементов');?>:</strong>
                </span>
    <select id="hm-action" <?php if (!$this->searchAssignAvailable): ?>disabled<?php endif;?>>
        <option value="null"><?php echo _('Выберите действие');?></option>
        <option value="candidateApply"><?php echo _('Включить в сессию подбора');?></option>
<!--        <option value="candidateApplyHoldOn">--><?php //echo _('Включить в сессию подбора в качестве потенциального кандидата');?><!--</option>-->
        <option value="candidateIgnore"><?php echo _('Включить в чёрный список');?></option>
    </select>
    <button id="hm-candidate-action-submit"><?php echo _('Выполнить');?></button>
</div>
<div class="export"></div>
</td></tr></table>
</div>
</div>
<?php $this->inlineScript()->captureStart();?>
    var vc = HM.create('recruit.VacancyCandidate', {url : {
        candidateApply : "/recruit/candidate/list/assign-hh/vacancy_id/<?php echo $this->vacancy->vacancy_id; ?>",
        candidateApplyHoldOn : "/recruit/candidate/list/assign-hh/vacancy_id/<?php echo $this->vacancy->vacancy_id; ?>/status/<?php echo HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON;?>",
        candidateIgnore : "/recruit/candidate/index/ignore-resumes"
    }});
<?php $this->inlineScript()->captureEnd();?>
<?php else: ?>
<div class="clearfix"></div>
<?php $url = $this->url(array('action' => 'form'))?>

<div style="padding-top: 20px;"><?php echo _("Не найден ни один кандидат, соответствующий критериям поиска, указанным в заявке на подбор.<br>Вы можете воспользоваться <a href='{$url}'>формой поиска</a> по произвольным критериям.")?></div>
<?php endif;?>