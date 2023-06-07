<?php echo $this->doctype();?>
<html lang="ru">
<?php echo $this->partial('partials/head.tpl');?>
<body class="print">
<div v-cloak id="hm-vue-app">
    <v-app>
        <v-main style="width: 100%;" app>
            <v-container fill-height style="width: 100%;" :grid-list-xs="$vuetify.breakpoint.xsOnly" :grid-list-sm="$vuetify.breakpoint.smOnly" :grid-list-md="$vuetify.breakpoint.mdOnly" :grid-list-lg="$vuetify.breakpoint.lgOnly" :grid-list-xl="$vuetify.breakpoint.xlOnly" fluid>
                <v-layout column fill-height>
                    <v-flex>
                        <?php echo $this->layout()->content;?>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-main>
    </v-app>
</div>
<?php echo $this->VueScript(); ?>

<?php echo $this->headScript(); ?>

<?php echo $this->inlineScript(); ?>

<script>
  /* проверка работы скриптов */
  window.addEventListener('DOMContentLoaded', function () {
    /* возвращаем к работе консоль*/
    if (window.hm) {
      window.console = window.hm.core.Console;
    }
    console.log('DOMContentLoaded!');
  });
</script>
</body>
</html>