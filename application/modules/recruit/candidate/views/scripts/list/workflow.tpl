<div class="workflow">
	<div class="workflow_corner"></div>
	<div class="workflow_header">
		<h1><?php echo $this->candidate->fio;?></h1>
		<span class="close"></span>
	</div>
	<div class="workflow_list">
	<?php foreach($this->stages as $key => $stage):?>
	    <?php if($this->workflow[$key] != ''){ $currStage = $this->workflow[$key];}else{ $currStage = $key;}?>
		<div class="workflow_item <?php $freeze = false; if(intval($this->workflow[$key] / 100) == 2):?>success<?php elseif(intval($this->workflow[$key] / 100) == 1):?>complete<?php elseif($this->workflow[$key] ==''): $freeze = true;?>freeze<?php elseif(intval($this->workflow[$key] / 100) == 0):?>incomplete<?php else: ?>freeze<?php endif;?>">
			<div class="workflow_item_head">
				<div class="wih_icon">
				</div>
				<div class="wih_title">
					<?php if ($this->actions[$currStage]['one'] === true && !$freeze): ?>
						<a href="<?php echo $this->actions[$currStage]['url'];?>"><?php if($this->workflow[$key] != ''){ echo HM_Vacancy_Assign_AssignModel::getStatus($this->workflow[$key]); } else{echo HM_Vacancy_Assign_AssignModel::getStatus($key); } ?></a>
					<?php else:?>
					    <?php if($this->workflow[$key] != ''){ echo HM_Vacancy_Assign_AssignModel::getStatus($this->workflow[$key]); } else{echo HM_Vacancy_Assign_AssignModel::getStatus($key); } ?>
					<?php endif;?>
				</div>
				<div class="wih_time">
				<?php if($freeze && ($key == array_pop(array_keys($this->stages)))):?>
					до 24.11.2011
				<?php endif;?>
				</div>
			</div>
			<div class="workflow_item_description">
			<?php if($freeze != true):?>
				<div class="wid_text">
					 <!-- Тут потом описание -->
					<hr>
					<div class="wid_control_link">
						<?php if(is_array($this->actions[$currStage]) && $this->actions[$currStage]['one']!==true):?>
							<?php foreach($this->actions[$currStage] as $link):?>
								<a href="<?php echo $link['url'];?>"><?php echo $link['title'];?></a>
							<?php endforeach;?>
						<?php endif;?>
					</div>
				</div>
				<div class="wid_deadline">
					<div class="wid_d_time">
						14:23
					</div>
					<div class="wid_d_full">
						<div class="wid_d_full_se">
							 Начало:<br>
							 11.11.2011<br>
							<span>17:24</span>
						</div>
						<div class="wid_d_full_se">
							 Окончание:<br>
							 11.11.2011<br>
							<span>17:24</span>
						</div>
						<div class="wid_d_clear">
						</div>
						<div class="wid_d_full_desc complete">
							<div>
								Выполнено в срок
							</div>
							<div class="wid_d_full_desc_time">
								 31.11.2011 в <span>17:23</span>
							</div>
						</div>
						<div class="wid_d_full_desc delay">
							<div>
								Выполнено с опозданием
							</div>
							<div class="wid_d_full_desc_time">
								 31.11.2011 в <span>17:23</span>
							</div>
						</div>
						<div class="wid_d_full_desc not">
							<div>
								Не выполнено
							</div>
						</div>
					</div>
				</div>
				<?php endif;?>
			</div>
		</div>
	<?php endforeach;?>
	</div>
</div>	