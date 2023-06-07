<?php echo $this->doctype();?>
<html>
    <?php echo $this->partial('partials/head.tpl');?>
    <body>
        <div v-cloak id="hm-vue-app">
            <hm-vue-app>
                <?php echo $this->partial('partials/header.tpl');?>
                <v-main app>
                    <v-container z fluid>
                        <?php echo $this->layout()->content ?>
                    </v-container>
                </v-main>
                <?php echo $this->partial('partials/footer.tpl');?>
            </hm-vue-app>
        </div>
        <style>
            [v-cloak] {
                visibility: hidden;
            }
        </style>
        <script>
            var hmVueApp = new Vue({
                components: {
                    hmVueApp: hmVueApp
                }
            }).$mount('#hm-vue-app')
        </script>
    </body>
</html>

