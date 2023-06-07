<chart>
	<meta>
		<?php if (is_array($this->meta) && count($this->meta)):?>
		<?foreach ($this->meta as $key => $value):?>
		<<?php echo $key?>><![CDATA[<?php echo $value?>]]></<?php echo $key?>>
		<?endforeach;?>
		<?php endif;?>
	</meta>
	<series>
	<?foreach ($this->series as $key => $value):?>
		<value xid="<?php echo $key?>"><?php echo $value?></value>
	<?endforeach;?>
	</series>
	<graphs>
		<graph gid="activity-<?php echo $this->type; ?><?php echo $this->single;?>">
		<?foreach ($this->graphs as $key => $value):?>
			<value xid="<?php echo $key?>"><?php echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>