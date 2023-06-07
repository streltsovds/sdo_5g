<hm-workflow-short
        :states='<?= json_encode($this->states) ?>'
        :url='<?= json_encode($this->get_url) ?>'
        :id='<?= json_encode($this->model->getPrimaryKey())?>'
></hm-workflow-short>
