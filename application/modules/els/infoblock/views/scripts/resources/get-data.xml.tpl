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
		<graph gid="resources">
		<?foreach ($this->data as $key => $value):?>
			<value xid="<?php echo $key?>" color="<?php echo $this->colors[$key];?>"><?php echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>