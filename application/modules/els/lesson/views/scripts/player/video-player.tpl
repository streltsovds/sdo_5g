<div style="display: flex; flex-wrap: nowrap; background-color: #3D3D3D; height: 100%;">
    <hm-eclass-video
        :data='<?= $this->camera; ?>'
        title='Вебкамера'
        title-playlist='Сохраненные записи вебкамеры'
    ></hm-eclass-video>
    <hm-eclass-video
        :data='<?= $this->screen; ?>'
        title='Экран'
        title-playlist='Сохраненные записи экрана'
    ></hm-eclass-video>
</div>