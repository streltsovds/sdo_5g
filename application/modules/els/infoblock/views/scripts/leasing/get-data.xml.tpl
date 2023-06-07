<chart>
	<series>
	<?foreach ($this->series as $key => $value):?>
		<value xid="<?php echo $key?>"><?php echo $value?></value>
	<?endforeach;?>
	</series>
	<graphs>
		<graph gid="leasing-limit">
		<?foreach ($this->graphs as $key => $value):?>
			<value xid="<?php echo $key?>"><?php echo $this->limitValue;?></value>
		<?endforeach;?>
		</graph>
		<graph gid="leasing-<?php echo $this->type; ?>">
		<?foreach ($this->graphs as $key => $value):?>
			<value xid="<?php echo $key?>"><?php echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>