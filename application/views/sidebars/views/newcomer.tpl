<?php
$user = count($this->model->user) ? $this->model->user->current() : false;
$photoSrc = ($user && $user->getPhoto()) ? $user->getPhoto() : Zend_Registry::get('config')->src->default->photo;
?>
<v-card style="position: relative;">
    <v-img :aspect-ratio="250/250" class="secondary" max-height="250" src="/<?php echo $photoSrc;?>" contain>
        <div class="bottom-gradient fill-height"></div>
    </v-img>

    <div style="padding: 20px;">

        <?php echo $this->workflow($this->model);?>

        <h2><?php echo _('Документы');?></h2>
        <ul>
            <li><a href="<?php echo $this->url(['module' => 'newcomer', 'controller' => 'list', 'action' => 'print-forms', 'newcomer_id' => $this->model->newcomer_id]);?>"><?php echo _('План адаптации');?></a></li>
        </ul>

    </div>
</v-card>