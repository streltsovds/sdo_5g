<template>
    <div class="hm-old-news-item">
        <v-dialog v-model="isDescriptionShowed">
            <v-layout justify-center>
                <v-card flat class="old-news-full-text" style="min-width: 500px; max-width: 1280px;">
                    <v-container>
                        <v-card flat>
                            <v-card-title>
                                <span class="headline" v-html="news.name"></span>
                                <v-spacer></v-spacer>
                                <v-btn small fab absolute color="white" style="top:-15px;right:-15px;"
                                       @click="closeDescription" icon>
                                    <img src="../../assets/images/close.svg" style="width: 32px;"/>
                                </v-btn>
                            </v-card-title>
                            <v-divider></v-divider>
                            <v-card-text ref="newsBodyD" class="old-news-full-text__body" v-html="news.message">
                            </v-card-text>
                            <v-spacer></v-spacer>
                            <v-divider></v-divider>
                            <v-layout mt-3>
                                <v-flex sm5 style="position: relative">
                                    <v-icon>today</v-icon>
                                    <div style="position: absolute;top: 3px;left: 30px;">{{ postTime }}</div>
                                </v-flex>
                                <v-flex sm7 style="position: relative">
                                    <v-icon>account_circle</v-icon>
                                    <div style="position: absolute;top: 3px;left: 30px;">{{ news.author }}</div>
                                </v-flex>
                            </v-layout>
                        </v-card>
                    </v-container>
                </v-card>
            </v-layout>
        </v-dialog>
        <v-layout v-show="isMobileDescriptionShowed" row wrap class="hm-old-news-item__mobile-popup">
            <div class="data text-xs-center">
                {{ postTime }}
            </div>
            <div class="close-button">
                <img width="24" height="24" src="../../assets/images/close.svg"
                     @click="isMobileDescriptionShowed = false"/>
            </div>
            <v-flex xs12 class="old-news-body" mt-5 ref="newsBodyM">
                <div v-html="news.name" class="old-news-body__title"></div>
                <v-img max-height="100%" max-width="100%"
                       v-if="news.icon_url === null || news.icon_url === ''"
                       :src="require('../../assets/images/default_news.svg')"/>
                <v-img max-height="100%" max-width="100%" v-else :src="news.icon_url"/>
                <div v-html="news.announce" class="old-news-body__announce"></div>
                <div v-html="news.message" class="old-news-body__text"></div>
            </v-flex>
        </v-layout>
        <v-card class="old-news-card" :height="$vuetify.breakpoint.xsOnly ? '150' : '300'">
            <v-container fluid style="padding: 0;" fill-height>
                <v-layout>
                    <v-flex xs4 class="old-news-card__image">
                        <v-img height="100%" width="100%"
                               v-if="news.icon_url === null || news.icon_url === ''"
                               :src="$vuetify.breakpoint.xsOnly ? require('../../assets/images/default_news_mobile.svg') : require('../../assets/images/default_news.svg')"/>
                        <v-img height="100%" width="100%" v-else :src="news.icon_url"/>
                    </v-flex>
                    <v-flex xs8>
                        <v-container fill-height fluid :style="$vuetify.breakpoint.xsOnly ? 'padding-top: 5px;' : ''">
                            <v-layout column>
                                <div class="old-news-card__text-container">
                                    <p class="old-news-card__text-container__title align-bottom" v-html="news.name"></p>
                                    <p class="old-news-card__text-container__text" v-html="news.announce"></p>
                                </div>
                                <v-flex xs12 style="padding: 0">
                                    <v-layout row wrap align-end fill-height style="position: relative"
                                              :style="$vuetify.breakpoint.mdAndUp ? 'margin-top: 10px;' : ''">
                                        <div class="old-news-card__data">
                                            {{ postTime }}
                                            <like
                                                    class="old-news-card__data__like"
                                                    @up="performLike"
                                                    @down="performDislike"
                                                    :data="likeData"
                                            />
                                        </div>
                                        <div class="old-news-card__read-btn">
                                            <v-btn
                                                    flat
                                                    class="button"
                                                    :class="restNews ? 'rest-news-button' : ''"
                                                    :small="$vuetify.breakpoint.xsOnly"
                                                    :loading="isDescriptionShowed"
                                                    @click.prevent="showDescription"
                                            >
                                                Прочитать
                                                <v-icon v-if="!$vuetify.breakpoint.xsOnly" right>arrow_forward</v-icon>
                                            </v-btn>
                                        </div>
                                    </v-layout>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card>
    </div>
</template>

<script>
    import moment from "moment";
    import like from "../newsComponents/like";

    const DOWNLOAD_FILE_EVENT = "download_file";
    const CLICK_EVENT = "click";
    const CONTENT_TYPE = "content-type";
    const HTTP_LINK = "http";
    const HTTPS_LINK = "https";
    // const DEFAULT_EXTENSIONS = ["pdf", "docx", "doc", "xsl", "xslx"];

    // const downloadAbleExtensions =
    //   window.HmNews.downloadableExtensions || DEFAULT_EXTENSIONS;
    //
    // const checkExtension = ext => downloadAbleExtensions.includes(ext);
    //
    // const checkHref = href => {
    //   if (!href) return false;
    //   if (href.includes(CONTENT_TYPE)) return true;
    //   const extension = href.split(".").pop();
    //   return checkExtension(extension);
    // };

    moment.locale("ru");
    export default {
        props: {
            news: {
                type: Object,
                default: () => {
                }
            },
            downloadableExtensions: {
                type: Array,
                default: () => ["pdf", "docx", "doc", "xsl", "xslx"]
            },
            restNews: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                isDescriptionShowed: false,
                isMobileDescriptionShowed: false,
                publicPath: process.env.BASE_URL
            };
        },
        computed: {
            postTime() {
                if (this.$vuetify.breakpoint.xsOnly) {
                    let date = moment(this.news.created).format('L').split('.')
                    let year = date[2].slice(2, 4)
                    date.splice(2, 1, year)
                    return date.join('.')
                } else {
                    return moment(this.news.created, "YYYY-MM-DD").format("LL").split('г.')[0];
                }
            },
            isMobile() {
                if (document.querySelector("body").classList.contains("is-mobile")) {
                    return true;
                } else {
                    return false;
                }
            },
            likeData() {
                const count = parseInt(this.news.like_count, 10);
                return {
                    count: count ? count : 0,
                    hasUserLike: parseInt(this.news.like, 10) ? true : false
                };
            },
            avatarLetter() {
                return this.news.author.substr(0, 1);
            }
        },
        mounted() {
            this.$refs.newsBodyM.addEventListener(CLICK_EVENT, event => {
                const {href} = event.target;
                if (
                    event.target instanceof HTMLAnchorElement &&
                    this.isMobile &&
                    this.checkHref(href)
                ) {
                    event.preventDefault();
                    this.$nextTick().then(() => {
                        let data = {
                            event_id: DOWNLOAD_FILE_EVENT,
                            url: href
                        };
                        window.COMMON_DATA = data
                        parent.window.postMessage(data, "*");
                    });
                }
            });
            this.$refs.newsBodyD.addEventListener(CLICK_EVENT, event => {
                const {href} = event.target;
                if (
                    event.target instanceof HTMLAnchorElement &&
                    this.isMobile &&
                    this.checkHref(href)
                ) {
                    event.preventDefault();
                    this.$nextTick().then(() => {
                        let data = {
                            event_id: DOWNLOAD_FILE_EVENT,
                            url: href
                        };
                        window.COMMON_DATA = data
                        parent.window.postMessage(data, "*");
                    });
                }
            });
        },
        methods: {
            getIconPath(iconName) {
                return iconName ? require(`../../assets/images/${iconName}`) : ''
            },
            showDescription() {
                if (this.$vuetify.breakpoint.xsOnly) {
                    window.scrollTo(0, 0)
                    this.isMobileDescriptionShowed = true;
                } else this.isDescriptionShowed = true;
            },
            showDescriptionFromCard(event) {
                if (this.$vuetify.breakpoint.smAndDown) {
                    if (event.target.closest(".nobubbling")) return;
                    this.isDescriptionShowed = true;
                }
            },
            closeDescription() {
                this.isDescriptionShowed = false;
            },
            performLike() {
                this.$emit("likeAction", {
                    id: this.news.id,
                    type: "like"
                });
            },
            performDislike() {
                this.$emit("likeAction", {
                    id: this.news.id,
                    type: "dislike"
                });
            },
            checkExtension(ext) {
                this.downloadableExtensions.includes(ext);
            },
            checkHref(href) {
                if (!href) return false;

                if (href.includes(CONTENT_TYPE) || href.includes(HTTP_LINK) || href.includes(HTTPS_LINK)) return true;
                const extension = href.split(".").pop();
                return this.checkExtension(extension);
            }
        },
        components: {
            like
        }
    };
</script>


<style lang="scss">
    @import "../colors.scss";
    @import "../mixins.scss";

    .old-news-full-text {
        img {
            width: 100%;
            height: auto;
        }

        .v-card {
            box-shadow: none !important;
        }
    }

    .hm-old-news-item {
        &__mobile-popup {
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            padding: 20px 40px 40px 40px;
            height: 100vh;
            width: 100vw;
            overflow-y: scroll;
            background-color: $white;

            .data {
                @include newsText(13px, bold, $gray);
                position: absolute;
                left: 40px;
            }

            .close-button {
                cursor: pointer;
                position: absolute;
                right: 40px;
            }

            .old-news-body {
                &__text, &__announce {
                    @include newsText(13px, normal, $black);
                    margin-top: 16px;
                }

                &__title {
                    @include newsText(15px, bold, $black);
                    margin-bottom: 8px;
                }
            }
        }

        .old-news-card {
            margin-bottom: 26px;
            box-shadow: 0px 1px 5px rgba(0, 0, 0, 0.2), 0px 3px 4px rgba(0, 0, 0, 0.12), 0px 2px 4px rgba(0, 0, 0, 0.14);
            border-radius: 4px;

            &__image {
                padding: 0 !important;
            }

            &__text-container {
                height: 150px;
                position: relative;
                overflow: hidden;
                @media(max-width: 600px) {
                    height: 700px;
                    &__text, &__title {
                        margin-bottom: 0 !important;
                    }
                    &__text {
                        font-size: 12px !important;
                        /* Для переопределения стилей внутри v-html */
                        h1, h2, h3, h4, h5, h6, p, div, span {
                            font-size: 12px !important;
                        }
                    }
                    &__title {
                        font-size: 15px !important;
                    }
                    &:after {
                        content: "";
                        text-align: right;
                        position: absolute;
                        bottom: 0;
                        right: 0;
                        width: 70%;
                        height: 1.2em;
                        background: linear-gradient(to right, rgba(255, 255, 255, 0), rgba(255, 255, 255, 1) 50%);
                    }
                }

                &__title {
                    @include newsText(20px, bold, $black);
                    margin-bottom: 16px;
                }

                &__text {
                    margin-bottom: 0;
                    @include newsText(16px, normal, $black);
                    /* Для переопределения стилей внутри v-html */
                    h1, h2, h3, h4, h5, h6, p, div, span {
                        @include newsText(16px !important, normal !important, $black !important);
                        margin-bottom: 0;
                    }
                }
            }

            &__data {
                @include newsText(15px, bold, $gray);
                position: absolute;
                left: 0;
                bottom: 10px;

                @media (min-width: 1264px) and (max-width: 1904px) {
                    left: 5px;
                }
                @media (min-width: 1904px) {
                    left: 13px;
                }

                @media (max-width: 600px) {
                    font-size: 12px !important;
                    bottom: -10px;
                    left: 0;
                }

                &__like {
                    position: absolute;
                    right: -45px;
                    top: -13px;
                    @media (max-width: 600px) {
                        top: -2px;
                    }
                }
            }

            &__read-btn {
                position: absolute;
                display: inline-block;
                right: 0;
                bottom: 0;

                .v-btn__content {
                    margin-top: -2px !important;
                }

                @media (max-width: 600px) {
                    right: -17px;
                    bottom: -16px;
                    .v-btn__content {
                        margin-top: -3px !important;
                    }
                }

                .button {
                    @include newsText(14px, normal, $white !important);
                    width: 150px;
                    background-color: $orange !important;
                    box-shadow: 0px 1px 5px rgba(0, 0, 0, 0.2), 0px 3px 4px rgba(0, 0, 0, 0.12), 0px 2px 4px rgba(0, 0, 0, 0.14);
                    border-radius: 2px;
                    @media (max-width: 600px) {
                        font-size: 11px;
                        width: 79px;
                        height: 20px;
                    }
                }
            }
        }
    }
</style>