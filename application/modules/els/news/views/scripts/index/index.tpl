<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/news.css');?>
<?php $actions = json_encode($this->actionsData(), JSON_PRETTY_PRINT | JSON_HEX_APOS); ?>

<hm-news-page with-image debug url="<?= $this->getUrl?>" :actions='<?= $actions?>'></hm-news-page>