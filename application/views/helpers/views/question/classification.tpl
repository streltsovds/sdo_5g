<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<div class="at-form-comment">
    <?php echo _('Внимание! Перетащите в правую сторону ответы.'); ?>
</div>
<?php $variants = array(); ?>
<?php $variantsh = array(); ?>
<?php $used = array(); ?>
<div class="tests_body">
    <div class="main_question_div">
        <div class="right_question_div">
            <div class="right_question_div-wrapper">
                <div class="eLS-sortable-container">
                    <?php foreach ($this->question->variants as $variant) {
                            $variantsh[$variant->variant][] = md5($variant->question_variant_id.HM_Quest_Question_Type_ClassificationModel::SALT);
                        }
                    ?>
                    <?php foreach ($this->question->variants as $variant):?>
                        <?php if(!in_array($variant->variant, $variants)):?>
                            <div class="tmc-variant-outer">
                                <?php $variants[] = $variant->variant; ?>
                                <h2><?=$variant->variant?></h2>
                                <div id="<?=md5($variant->question_variant_id.HM_Quest_Question_Type_ClassificationModel::SALT)?>" class="eLS-sortable eLS-sortable-<?=$this->question->question_id;?> ui-sortable" data-questionid="<?=$this->question->question_id;?>" data-type="classification">
                                    <div class="eLS-sortable-container_wrapper">
                                    </div>
                                    <?php if (is_array($this->result) && isset($this->result[$variant->question_variant_id])): ?>
                                        <?php
                                            $tvariants = $this->question->variants;
                                            foreach ($tvariants as $tvariant):?>
                                            <?php if (in_array($this->result[$tvariant->question_variant_id], $variantsh[$variant->variant])): ?>
                                                <!-- НИКАКИХ ПРОБЕЛОВ МЕЖДУ первым дивом и инпутом!!!!!! -->
                                                <div class="eLS-sortable-item" id="answer_<?=$this->question->question_id;?>"><input type="hidden" name="results[<?=$this->question->question_id;?>][<?=$tvariant->question_variant_id;?>]" value="<?php echo md5($variant->question_variant_id.HM_Quest_Question_Type_ClassificationModel::SALT); ?>">
                                                    <div class="eLS-sortable-item-container" style="zoom: 1;">
                                                        <table cellpadding="0" cellspacing="0" border="0" class="eLS-drag-handler">
                                                            <tr class="eLS-drag-handler_wrapper">
                                                                <td class="drag-handler_left"></td>
                                                                <td class="eLS-answer-variant"><?=$tvariant->data?></td>
                                                                <td class="drag-handler_right"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                                <?php $used[] = $tvariant->question_variant_id; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif;?>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
        <div class="left_question_div">
            <div class="left_question_div-wrapper">
                <div class="eLS-sortable-container">
                    <div id="question_id_<?=$this->question->question_id;?>" class="eLS-sortable eLS-sortable-<?=$this->question->question_id;?> ui-sortable" data-questionid="<?=$this->question->question_id;?>" data-type="classification">
                        <?php
                            $variants = $this->question->variants;
                            shuffle($variants);
                        ?>
                        <?php foreach ($variants as $variant):?>
                            <?php if (!in_array($variant->question_variant_id, $used)): ?>
                                <!-- НИКАКИХ ПРОБЕЛОВ МЕЖДУ первым дивом и инпутом!!!!!! -->
                                <div class="eLS-sortable-item" id="answer_<?=$this->question->question_id;?>"><input type="hidden" name="results[<?=$this->question->question_id;?>][<?=$variant->question_variant_id;?>]" value="">
                                <div class="eLS-sortable-item-container" style="zoom: 1;">
                                    <table cellpadding="0" cellspacing="0" border="0" class="eLS-drag-handler">
                                    <tr class="eLS-drag-handler_wrapper">
                                    <td class="drag-handler_left"></td>
                                    <td class="eLS-answer-variant"><?=$variant->data?></td>
                                    <td class="drag-handler_right"></td>
                                    </tr>
                                    </table>
                                </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
