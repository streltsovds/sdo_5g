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

    </div>
</v-card>