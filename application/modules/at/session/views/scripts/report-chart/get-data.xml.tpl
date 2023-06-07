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
		<?foreach ($this->graphs as $key => $graph):?>
		    <?php if ($key == HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS):?>
    	    	<?foreach ($graph as $sessionUserId => $sessionGraph):?>
		            <graph title="<?php echo $this->legend[$key];?>" gid="analytics-<?php echo $key; ?>">
            		<?foreach ($sessionGraph as $keykey => $value):?>
            			<value xid="<?php echo $keykey?>"><?php echo $value;?></value>
            		<?endforeach;?>
            		</graph>
        		<?endforeach;?>
    		<?php else:?>
        		<graph title="<?php echo $this->legend[$key];?>" gid="analytics-<?php echo $key; ?>">
        		<?foreach ($graph as $key => $value):?>
        			<value xid="<?php echo $key?>"><?php echo $value;?></value>
        		<?endforeach;?>
        		</graph>
    		<?php endif;?>
		<?endforeach;?>
	</graphs>
</chart>