<template>
    <div class="hm-htmlpage hm-user-content">
        <v-card class="pa-5" v-html="page.text">
        </v-card>
    </div>
</template>

<script>

    export default {
        name: "HmHtmlpage",
        components: {},
        props: {
            page: {
                type: Object,
                required: true
            },
        },
        data() {
            return {}
        },
        watch: {},
        mounted() {},
        methods: {},
    };
</script>

<style lang="scss">
    .hm-htmlpage {

    }
</style>
