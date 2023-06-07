<chart>
	<meta>
		<?foreach ($this->meta as $key => $value):?>
		<<?php echo $key?>><![CDATA[<?php echo $value?>]]></<?php echo $key?>>
		<?endforeach;?>
	</meta>
	<series>
	<?foreach ($this->series as $key => $value):?>
		<value xid="<?php echo $key?>"><?php echo $value?></value>
	<?endforeach;?>
	</series>
	<graphs>
		<graph gid="yield-<?php echo $this->type; ?>">
		<?foreach ($this->graphs as $key => $value):?>
			<value xid="<?php echo $key?>"><?php echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>