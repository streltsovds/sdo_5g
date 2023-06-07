<?php
if (!$this->news->show) {
    echo _('Содержимое скрыто администратором');
} else {
    echo '<div class="hm-user-content">' . $this->news->message . '</div>';

    if ($this->resource) {
        // TODO materialResponsive отключен, т. к. не предназначен ни для вывода
        //  нескольких материалов на странице,
        //  ни для помещения внутрь <hm-dependency>
//        echo $this->materialResponsive($this->resource);
        echo $this->materialView($this->resource);
    }
}
?>
