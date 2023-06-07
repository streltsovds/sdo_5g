<?php
class HM_View_Helper_QuestProgress extends HM_View_Helper_Abstract {
	
	public function questProgress($progress, $attributes, $allowLinks = true)
	{
        if (count($progress) < 2) return '';
	    
		$bar = array();
		if (!is_array($attributes)) {
			$attributes = array();
		}
		if (!empty($progress)) {
		    $liWidth = (string)(100 / count($progress)) - 0.1;
		    $liWidth = str_replace(',', '.', $liWidth);
		}
		$i = 0;
		foreach ($progress as $item) {
			$classes = array('item');
			if ($item['current']) {
				$classes[] = 'item-current';
			}
			if ($i == 0) {
				$classes[] = 'item-first';
			}
			$i++;
			if ($i == count($progress)) {
				$classes[] = 'item-last';
			}
			$htmlLIAttribs = $this->view->htmlAttribs(array(
				'class' => $classes,
				'data-progress' => $item['itemProgress'],
				'style' => "width: {$liWidth}%;",
			));
			$htmlAAttribs = $this->view->htmlAttribs($this->view->htmlAttribsPrepare($attributes, array(
				'href' => $item['url'],
				'data-item-id' => $item['itemId'],
		        'title' => sprintf(_("Сохранить и перейти к странице '%s'"), $this->view->escape($item['name'])),
			)));

			if ($allowLinks) {
                $bar[] ="<li {$htmlLIAttribs}>".$this->view->progress($item['itemProgress'], 'x-custom')."<a {$htmlAAttribs}><span>".$this->view->escape($item['name'])."</span></a></li>";
            } else {
                $bar[] ="<li {$htmlLIAttribs}>".$this->view->progress($item['itemProgress'], 'x-custom')."<span class='close' data-item-id='" . $item['itemId'] . "'><span>".$this->view->escape($item['name'])."</span></span></li>";
            }

		}
		$bar = implode($bar);

		$html = "<div class=\"at-form-progress\"><ol>{$bar}</ol></div>";
		return $html;
	}
}
