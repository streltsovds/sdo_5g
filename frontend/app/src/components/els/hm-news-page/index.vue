<template>
<!--    <v-layout column fill-height v-if="hasHmApp" style="margin-top: -30px;">-->
<!--    <v-layout column fill-height style="margin-top: -30px;">-->
    <v-flex v-if="isLoading">
        <v-alert :value="true" transition="fade" type="info">
            <span class="mr-3 body-2">Загрузка...</span>
            <v-progress-circular
                    :size="20"
                    indeterminate
            ></v-progress-circular>
        </v-alert>
    </v-flex>
    <v-flex v-else>
        <v-layout>
            <v-flex xs12 class="news-page">
                <hm-news
                    @like="performLike"
                    :data="news"
                    :actions="actions"
                    :classifiers="classifiers"
                    :with-image="withImage"
                    :downloadable-extensions="downloadableExtensions"
                ></hm-news>
            </v-flex>
        </v-layout>
    </v-flex>

</template>

<script>
    import hmNews from "./components/hm-news";
    import HmOldNews from "./components/old-news/hm-old-news";

    import "vue2-animate/dist/vue2-animate.min.css";
    import axios from "axios";

    axios.defaults.headers.common["XREQUESTEDWITH"] = "XMLHttpRequest";

    import moment from "moment";
    moment.locale("ru");

    import data from "./components/data";

    export default {
        props: {
            url: String,
            actions: Array,
            debug: Boolean,
            downloadableExtensions: {
                type: Array,
                default: () => ["pdf", "docx", "doc", "xsl", "xslx"]
            },
            withImage: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                incoming_data: [],
                incoming_news: [],
                incoming_documents: [],
                incoming_resources: [],
                isExitLoading: false,
                hasHmApp: false,
                isLoading: false
            };
        },
        computed: {
            isMobile() {
                if (document.querySelector("body").classList.contains("is-mobile")) {
                    return true;
                } else {
                    return false;
                }
            },
            news() {
                return [
                    ...this.incoming_news.map(news => {
                        const date = moment(news.created);
                        news.date = date;
                        return news;
                    })
                ];
            },
            classifiers() {
                let classifiers = [...new Set(this.incoming_news.map(news => news.classifiers).flat())].filter(news => news);

                if(classifiers.length) {
                    classifiers.unshift("Все новости");
                }
                return classifiers
            },
            documents() {
                return this.incoming_documents.map(resourse => {
                    const date = moment(resourse.time);
                    resourse.time = date;
                    return resourse;
                });
            },
            resourses() {
                return [...this.incoming_resources];
            },
            windowW() {
                return window.innerWidth;
            },
            ieNoOverflow() {
                if (document.querySelector("body").classList.contains("ie-no-overflow")) {
                    if (this.windowW <= 980) {
                        return "max-width: 920px;";
                    } else {
                        return "max-width: 1030px;";
                    }
                } else {
                    return null;
                }
            }
        },
        created() {
            if (document.querySelector("body").classList.contains("has-hm-app")) {
                this.hasHmApp = true;
            } else {
                this.hasHmApp = false;
            }
            this.init();
        },
        methods: {
            logout() {
                window.hm
                    ? window.hm.core.Console.log(...arguments)
                    : console.log(...arguments);
            },
            performLike(event) {
                // типы айтемов, должно быть синхронизировано с бэкэндом
                const item_types = {
                    ITEM_TYPE_NEWS: 3,
                    ITEM_TYPE_RESOURCES: 4
                };
                const data = {
                    item_type: item_types[event.item_type],
                    item_id: event.id,
                    like_type: event.type.toUpperCase()
                };
                axios
                    .post(this.incoming_data.like_url, data)
                    .then(response => response.data.result.count_like)
                    .then(count => {
                        if (count && data.item_type === item_types.ITEM_TYPE_NEWS) {
                            this.updateNews(event.id, event.type, count);
                        } else if (
                            count &&
                            data.item_type === item_types.ITEM_TYPE_RESOURCES
                        ) {
                            this.updateResources(event.id, event.type, count);
                        }
                    });
            },
            updateNews(id, type, count) {
                this.incoming_news = this.incoming_news.map(news => {
                    if (news.id == id) {
                        (news.like = type === "like" ? "1" : "0"), (news.like_count = count);
                    }
                    return news;
                });
            },
            updateResources(id, type, count) {
                this.incoming_documents = this.incoming_documents.map(resource => {
                    if (resource.id == id) {
                        (resource.like = type === "like" ? "1" : "0"),
                            (resource.like_count = count);
                    }
                    return resource;
                });
            },
            getData() {
                this.isLoading = true;
                axios.get(this.url).then(response => {
                    if (response.data) {
                        this.incoming_data = {...response.data};
                        this.incoming_news = [...response.data.news];
                        this.incoming_documents = [...response.data.resources];
                        this.incoming_resources = [...response.data.our];
                        this.$forceUpdate();
                    }
                    this.isLoading = false;
                })
                    .finally(() => this.isLoading = false);
            },
            initiateExit() {
                this.isExitLoading = true;
                let data = {
                    event_id: "close_window"
                };
                window.COMMON_DATA = data;
                parent.window.postMessage(data, "*");
            },
            init() {
                if (this.url) {
                    this.getData();
                } else {
                    this.incoming_data = {...data};
                    this.incoming_news = [...this.incoming_data.news];
                    this.incoming_documents = [...this.incoming_data.resources];
                    this.incoming_resources = [...this.incoming_data.our];
                }
            }
        },
        components: {
            HmOldNews,
            hmNews,
        }
    };
</script>


<style lang="scss">

    @import "./components/mixins.scss";
    @import "./components/colors.scss";

    .news-page__tab-item {
        font-style: normal;
        font-weight: bold;
        font-size: 30px;
        text-transform: none;
        padding-bottom: 20px;
        max-width: none;
        margin-right: 20px;
        a:not(.v-tabs__item--active) {
            color: #9d9d9d;
        }
        @media (max-width: 599px) {
            font-size: 26px;
        }
    }

    #news-page-old {
        .v-tabs__bar {
            z-index: 1;
            background-color: white !important;
        }
    }

    .news-page {
        position: relative;

        .v-tabs__container {
            margin: 20px 0 0 12px;
            @media (max-width: 600px) {
                margin: 20px 0 0 3px;
            }
        }
        .v-tabs__bar {
            z-index: 1;
            background-color: #fafafa !important;
        }
        .v-tabs__items {
            overflow: visible;
            z-index: 2;
        }
        a:hover {
            color: $black;
        }
        .v-tabs__slider-wrapper {
            padding: 0 10px;
            .v-tabs__slider {
                height: 3px;
            }
        }
    }

    .v-toolbar {
        position: fixed !important;
        z-index: 9;
    }

    ::before,
    ::after {
        box-sizing: inherit;
    }

    .content {
        position: relative;
        box-sizing: border-box;

        & * {
            box-sizing: inherit;
            background-repeat: no-repeat;
            verical-align: inherit;
        }

        & .application--wrap {
            min-height: unset;
        }
    }

    .headline p {
        margin: 0 !important;
    }

    .tmc-header {
        z-index: 100 !important;
    }
</style>
