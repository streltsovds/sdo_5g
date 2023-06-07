<?php?><div class="list-mass-actions"><table cellspacing="0"><tfoot><tr><td colspan="10" class="bottom-grid <?php if ($this->pagination):?>has-pagination <?endif;?><?php if ($this->massActions):?>has-mass-actions <?endif;?><?php if ($this->export):?>has-export <?endif;?>first-cell last-cell">    <?php if ($this->pagination)?>    <div class="pagination">        <div class="page-numbers"><?php echo $this->pagination;?></div>    </div>    <?php if ($this->actions): ?>    <div class="massActions mass-actions">        <form name="massActions_grid" id="massActions_grid" action="" method="post">            <div id="_fdiv">                <input type="hidden" id="massActionsAll_grid" value="" name="massActionsAll_grid">                <input type="hidden" id="postMassIds_grid" value="" name="postMassIds_grid">                <span class="massSelect">                    <strong><?php echo $this->action_title ?>:</strong>                </span>                <select onchange="" id="gridAction_grid" name="gridAction_grid" id="gridAction_grid">                    <option value=""><?php echo _('Выберите действие')?></option>                    <?php foreach ($this->actions as $url => $options):?>                    <option label="<?php echo $options['label']?>" value="<?php echo $url?>"><?php echo $options['label']?></option>                    <?php endforeach;?>                </select>                <?php if($this->customFormElements && is_array($this->customFormElements)):?>                    <?php foreach($this->customFormElements as $element):?>                        <?php echo $element;?>                    <?php endforeach;?>                <?php endif;?>                <input type="submit" value="<?php echo _('Выполнить'); ?>" id="send_grid" name="send_grid" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">            </div>        </form>    </div>    <?php $this->inlineScript()->captureStart();?>        var confirmMessages_grid = {};        <?php foreach ($this->actions as $url => $options):?>        confirmMessages_grid['<?php echo $url;?>'] = "<?php echo $options['confirm']?>";        <?php endforeach;?>        <?php $this->inlineScript()->captureEnd();?>    <?php endif; ?>    <?php if ($this->export):?>    <div class="export">        <?php if (in_array('print', $this->export['formats'])):?><input type="submit" value="Распечатать" onclick="window.open(); return false;" target="_blank" name="button" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false"><?php endif;?>        <?php if (in_array('excel', $this->export['formats'])):?><input type="submit" value="Excel" onclick="window.open('<?php echo $this->url(array('export' => 'excel', 'page' => 'all', 'query' => null) + $this->export['params']); ?>'); return false;" target="_blank" name="button" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false"><?php endif;?>        <?php if (in_array('word', $this->export['formats'])):?><input type="submit" value="Word" onclick="window.open(); return false;" target="_blank" name="button" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false"><?php endif;?>    </div>    <?php endif;?>    </td></tr></tfoot></table></div>