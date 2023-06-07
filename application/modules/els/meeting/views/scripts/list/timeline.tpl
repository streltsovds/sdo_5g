<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/portfolio.css'));?>
<?php echo $this->headSwitcher(array('module' => 'meeting', 'controller' => 'list', 'action' => 'index', 'switcher' => 'timeline'), 'meeting');?>

<style type="text/css">

    #cd-timeline {
        width: 75% !important;

    }
    #cd-timeline::before {
        background-color: #8C8C8C;
    }
    .cd-timeline-img {
        box-shadow: 0 0 0 4px #8C8C8C, inset 0 2px 0 rgba(0, 0, 0, 0.16), 0 3px 0 4px rgba(0, 0, 0, 0.12);
        background-color: #FFF;
        overflow: hidden;
    }
    .cd-timeline-img img {
        width: auto;
        height: auto;
        top: 0;
        left: 0;
        max-width: none;
    }
    .cd-timeline-content {
        background-color: #F3F3F3;
        box-shadow: 0 3px 0 #E0E0E0;
    }
    .cd-timeline-content p {
        font-size:12px;
    }

    .cd-timeline-content::before {
        border-right-color: #F3F3F3;
    }
    @media only screen and (min-width: 1170px) {
        .cd-timeline-content::before {
            border-color: transparent;
            border-left-color: #F3F3F3;
        }
        .cd-timeline-block:nth-child(even) .cd-timeline-content::before {
            border-right-color: #F3F3F3;
        }
    }
    .cd-timeline-comm {
        position:absolute;
        left: 100%;
        top: 0;
        width: 25%;
        box-sizing: border-box;
        padding: 0 15px 44px;
        height: 100%;
        font-size:12px;
 
    }
    .cd-timeline-comm-inner {
        background: #f3f3f3;
        padding: 17.6px;
        max-height: 100%;
        border-radius: .25em;
        box-shadow: 0 3px 0 #E0E0E0;
       
    }

    .cd-timeline-split {
        height: 4px;
        width: 123.3%;
        position: absolute;
        background: #8C8C8C;
        top: -75px; left: 0;
        z-index: -1;
    }

    .cd-timeline-block-splitted {
        margin-top: 150px;
    }    


    .cd-timeline-content a {
        display:inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;        
    }
    
</style>
<section id="cd-timeline">
    <?php if ($this->data): ?>
    <?php $today = date('d.m.Y'); ?>
        


<a href='#today'><h1>перейти к СЕГОДНЯ, <?=$today?></h1></a>
        <div class="cd-timeline-img" id="cd-split">
            <a href='/meeting/list/new/project_id/<?=Zend_Controller_Front::getInstance()->getRequest()->getParam('project_id', 0)?>'><img border=0 src="/images/content-modules/timeline/bullet-add.png" /></a>
        </div>
<?
function date2comp($d)
{
    $d = explode('.', $d);
    return $d[2].$d[1].$d[0];
}
?>

    <?php foreach($this->data as $entry) : ?>
        <?php $date = ''; if($entry['date_']) {$date = new HM_Date($entry['date_']); $date = $date->get('dd.MM.YYY');} $bSplitter = $today && date2comp($date)>=date2comp($today); ?>
    <div class="cd-timeline-block <?php echo $bSplitter?'cd-timeline-block-splitted':''?>">
        <?php echo $bSplitter?'<div class="cd-timeline-split" id="today"></div>':''?>
        <?php if($bSplitter) $today = ''?>
        <div class="cd-timeline-img" id="cd-split">
            <img src="/images/content-modules/timeline/bullet-<?php echo $entry['type']?>.png" />
        </div>

        <div class="cd-timeline-content">
            <?php if ($entry['image']): ?>
                <img src="<?php echo $entry['image'];?>" />
            <?php endif; ?>
            <h2><?php echo $entry['name'];?></h2>
            <p><?php echo $entry['text'];?></p>

            <span class="cd-date"><?php echo $date;?></span>
        </div>

        <?php if (1 ||$entry['comments']): ?>
        <div class="cd-timeline-comm">
            <div class="cd-timeline-comm-inner">
                <?php echo $entry['comments'];?>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</section>
