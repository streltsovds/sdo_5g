<?php
$this->headScript()
     ->appendFile($this->serverUrl('/js/lib/jquery/jquery.cookie.js'));

$this->headLink()->appendStylesheet( $this->serverUrl('/css/content-modules/extended-page.css') );
?>

<div <?php if ($this->courseContent === true): ?> class="extended-page-has-course-content"<?php endif; ?>>

<?php $this->placeholder('columns')->captureStart('SET'); ?>
<?php if($this->topContent):?>
<?php echo $this->topContent; ?>
<?php endif;?>
<div class="content-container<?php if ($this->courseContent === true): ?> content-container-expandable<?php endif; ?>">
    <div class="content-here">
        <?php echo $this->workspace?>
    </div>
    <?php if ($this->courseContent === true): ?>
    <a class="content-size" title="<?php echo _("Развернуть на весь экран"); ?>" data-titles="<?php echo $this->escape(HM_Json::encodeErrorSkip(array(
            "expand" => _("Развернуть на весь экран"),
            "collapse" => _("Свернуть")
        ))) ?>">
        <span class="content-size-expand" title="<?php echo _("Развернуть на весь экран"); ?>"><?php echo _("Развернуть на весь экран"); ?></span>
        <span class="content-size-collapse" title="<?php echo _("Свернуть"); ?>"><?php echo _("Свернуть"); ?></span>
        <span class="ui-icon"></span>
    </a>
    <?php endif; ?>
</div>

<?php
if (count($this->getTabLinks()) && $this->_withoutActivities === false):?>
    <?php $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.form.js')); ?>
    <?php foreach($this->getTabLinks() as $title => $url):?>
        <?php $this->tabContainer()->addPane('tabs', $title, '', array('contentUrl' => $url));?>
    <?php endforeach;?>
    <?php echo $this->tabContainer()->tabContainer(
        "tabs",
        array(
            "cache" => true,
            "spinner" => false,
            "selected" => -1
        ),
        array("class" => "extended-page-tabs ui-local-error-box"));
    ?>
<?php endif;?>
<?php $this->placeholder('columns')->captureEnd(); ?>

<?php if (!$this->withoutContextMenu): ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<?php
    $this->accordionContainer()
         ->setElementHtmlTemplate('<h3 class="ui-accordion-header"><a href="#"><span class="header">%s</span></a></h3><div class="ui-accordion-content"><div class="ui-accordion-content-wrapper">%s</div></div>');
/*    if ($this->getContextNavigation()) {
    	$navigation = $this->navigation()->menu()->renderMenu($this->getContextNavigation(), array('maxDepth' => 1));
        $this->accordionContainer()
             ->addPane("page-context-accordion", $this->getPaneName(),
                 strip_tags($navigation, '<ul><li><a>')
             );
    }*/
   
    if (count($this->getInfoBlocks())) {
        foreach($this->getInfoBlocks() as $block) {
            $blockName = $block['name'];
            $blockOptions = $block['options'];
            if($this->_withoutActivities === true && $blockName == 'ActivitiesBlock'){
                continue;
            }
            $blockContent = $this->{$blockName}(null, null, $blockOptions);
            if (null !== $blockContent) {
                $this->accordionContainer()
                    ->addPane("page-context-accordion", (isset($blockOptions['title']) ? $blockOptions['title'] : _('Контекстный блок')), $blockContent);
            }
        }
    }

    echo $this->accordionContainer()
              ->accordionContainer("page-context-accordion", array('autoHeight' => FALSE), array("class" => "page-context-accordion"));
?>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php endif; ?>
<?php echo $this->partial('_columns.tpl', 'default', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'extended-page'.($this->withoutContextMenu ? ' extended-page-narrow ' : ''),
    'type' => $this->withoutContextMenu ? 'pc' : 'px'
)); ?>

</div>
<script src="/js/extended.js"></script>