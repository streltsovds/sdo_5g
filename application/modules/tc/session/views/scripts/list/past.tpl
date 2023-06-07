<script>
    $( document ).ready(
        function () {
            if ($("button#go-back").size() == 0) {
                $("div#grid").after('<button id="go-back" onclick="history.back();">Назад</button>')
            }
        }
    );
</script>
<?php echo $this->grid; ?>