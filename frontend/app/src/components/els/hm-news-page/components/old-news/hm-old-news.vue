<template>
    <v-container fluid style="padding: 10px 0 0 0;">
        <v-layout class="hm-old-news" row wrap justify-space-between>
            <v-flex xs12 class="hm-old-news__filters">
                <v-layout row>
                    <v-flex xs6 sm6 md5 class="popularity-sort">
                        <v-layout justify-start>
                            <p style="padding-top: 20px; padding-right: 10px;">
                                {{$vuetify.breakpoint.xsOnly ? 'По популярности' : 'Сортировать по популярности'}}
                            </p>
                            <v-switch v-model="sortPopular" hide-details color="#297BDC"></v-switch>
                        </v-layout>
                    </v-flex>
                    <v-flex xs6 sm6 class="data-sort">
                        <v-layout :justify-end="$vuetify.breakpoint.smAndDown">
                            <v-btn
                                    :small="$vuetify.breakpoint.xsOnly"
                                    @click="isCalendarShown = !isCalendarShown"
                                    style="border-radius: 4px;"
                                    :style="$vuetify.breakpoint.xsOnly ? 'top: 5px !important;' : ''"
                                    color="primary"
                            >
                                {{ isCalendarShown ? 'закрыть' : displayed_date }}
                                <img :style="$vuetify.breakpoint.xsOnly ? 'margin-top: -3px; margin-left: -6px;':''"
                                     :width="$vuetify.breakpoint.xsOnly ? '38' : '46'"
                                     :height="$vuetify.breakpoint.xsOnly ? '37' : '38'"
                                     src="../../assets/images/date.svg"/>
                            </v-btn>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-flex>
            <v-flex xs12 class="hm-old-news__classifiers">
                <v-layout class="hm-old-news__classifiers__container">
                    <p v-for="(classifier, index) in shownClassifiers" @click="changeActiveClassifier(index)"
                       :class="activeClassifier === index ? 'active' : ''">{{classifier}}</p>
                </v-layout>
            </v-flex>

            <v-dialog max-width="320" v-model="isCalendarShown">
                <v-card style="position: relative;overflow:hidden;">
                    <v-btn @click="isCalendarShown = !isCalendarShown" fab absolute style="right: 0;" flat small>
                        <img src="../../assets/images/close.svg" height="24" width="24"/>
                    </v-btn>
                    <v-layout align-center justify-content-center>
                        <v-flex class="text-xs-center">
                            <v-date-picker class="elevation-0" :allowed-dates="allowDates" type="month"
                                           v-show="isCalendarShown" no-title @input="selectDate" :value="selected_date"
                                           locale="ru-ru" first-day-of-week="1"></v-date-picker>
                        </v-flex>
                    </v-layout>
                </v-card>
            </v-dialog>
            <v-flex xs12 v-for="newsData in mainNews" style="padding-left: 10px; padding-right: 10px;" :key="newsData.id">
                <hm-old-news-item
                        @likeAction="likeAction"
                        :news="newsData"
                        :downloadable-extensions="downloadableExtensions"
                ></hm-old-news-item>
            </v-flex>
            <v-flex xs12 style="padding-left: 10px; padding-right: 10px;">
                <v-layout row wrap>
                    <v-flex xs12 v-for="newsData in restNews" :key="newsData.id">
                        <hm-old-news-item
                                @likeAction="likeAction"
                                :news="newsData"
                                :downloadable-extensions="downloadableExtensions"
                        ></hm-old-news-item>
                    </v-flex>
                </v-layout>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import hmNewsItem from "../hm-news-item";
    import moment from "moment";
    import HmOldNewsItem from "./hm-old-news-item";

    moment.locale("ru");
    export default {
        props: {
            data: Array,
            downloadableExtensions: Array,
            withImage: {
                type: Boolean,
                default: false
            },
            classifiers: Array
        },
        data() {
            return {
                selected_date: null,
                isCalendarShown: false,
                sortPopular: false,
                activeClassifier: 0
            };
        },
        computed: {
            shownClassifiers() {
                return this.classifiers.filter((classifier) => {
                    let data = this.data
                        .filter(x => {
                            return (
                                this.selected_date ===
                                `${x.date.year()}-${this.addLeadingZero(x.date.month() + 1)}`
                            );
                        })
                        .filter(news => {
                            if (news.classifier === classifier || classifier === "Все новости") return news
                        })
                    if (data.length > 0) return classifier
                })
            },
            mainNews() {
                let displayedNews = this.displayedNews()
                return displayedNews.filter((news, index) => {
                    if (index < 3) return news
                })
            },
            restNews() {
                let displayedNews = this.displayedNews()
                return displayedNews.filter((news, index) => {
                    if (index >= 3) return news
                })
            },
            displayed_date() {
                if (this.$vuetify.breakpoint.xsOnly) {
                    let month = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(0, 1).splice(0, 1)[0]
                    let year = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(2, 1).splice(0, 1)[0]
                    return `${month[0].toUpperCase()}${month.slice(1, month.length)} ${year}`
                } else {
                    let month = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(0, 1).splice(0, 1)[0]
                    let year = moment(this.selected_date).format("MMMM D YYYY").split(' ').splice(2, 1).splice(0, 1)[0]
                    return `${month[0].toUpperCase()}${month.slice(1, month.length)} ${year}`
                }
            },
            restrictHeight() {
                return this.displayedNews.length > 8;
            },
            newsMonths() {
                return this.data.map(x => x.date.month() + 1).sort((a, b) => b - a);
            },
            newsYears() {
                return this.data.map(x => x.date.year()).sort((a, b) => b - a);
            },
            allowedDatesList() {
                return [...new Set(this.data.map(x => {
                    return `${x.date.year()}-${this.addLeadingZero(x.date.month() + 1)}`;
                }))]
            }
        },
        methods: {
            logout() {
                window.hm
                    ? window.hm.core.Console.log(...arguments)
                    : console.log(...arguments);
            },
            restNewsCardStyle(index) {
                if (index % 2 === 0 && index !== this.restNews.length - 1) {
                    return 'padding-right: 25px;'
                } else if (index !== this.restNews.length - 1) {
                    return 'padding-left: 25px;'
                }
            },
            changeActiveClassifier(index) {
                this.activeClassifier = index
            },
            displayedNews() {
                if (this.selected_date === null || this.selected_date === undefined) {
                    this.selected_date = [...this.allowedDatesList].pop();
                }
                return this.data
                    .filter(x => {
                        return (
                            this.selected_date ===
                            `${x.date.year()}-${this.addLeadingZero(x.date.month() + 1)}`
                        );
                    })
                    .filter(news => {
                        if (this.activeClassifier === 0) return news
                        else if (news.classifier === this.shownClassifiers[this.activeClassifier]) return news
                    })
                    .sort(this.sortToDate)
                    .sort((a, b) => {
                        if (this.sortPopular) {
                            return this.sortToLikes(a, b);
                        } else {
                            return 0;
                        }
                    });

            },
            ifLastNews(index) {
                if (this.restNews.length % 2 === 0) return false
                else return index === this.restNews.length - 1
            },
            sortToLikes(likeOne, likeTwo) {
                // с бОльшим количеством лайков первее
                const likeOneCount =
                    likeOne.like_count === null ? 0 : parseInt(likeOne.like_count, 10);
                const likeTwoCount =
                    likeTwo.like_count === null ? 0 : parseInt(likeTwo.like_count, 10);
                return likeTwoCount - likeOneCount;
            },
            sortToDate(newsA, newsB) {
                // сортирует так что новая новость первее
                if (newsA.date.isSame(newsB.date, "second")) {
                    return 0;
                } else if (newsA.date.isAfter(newsB.date, "second")) {
                    return -1;
                } else if (newsA.date.isBefore(newsB.date, "second")) {
                    return 1;
                } else {
                    return 0;
                }
            },
            addLeadingZero(val) {
                if (parseInt(val) < 10) {
                    return `0${val}`;
                } else {
                    return val;
                }
            },
            selectDate(val) {
                this.selected_date = val;

                this.isCalendarShown = false;
            },
            allowDates(val) {
                if (this.allowedDatesList.includes(val)) return val;
            },
            likeAction(event) {
                this.$emit("like", {...event, item_type: "ITEM_TYPE_NEWS"});
            }
        },
        components: {
            HmOldNewsItem,
            hmNewsItem
        }
    };
</script>

<style lang="scss">
    @import "../mixins.scss";
    @import "../colors.scss";

    .hm-old-news {
        position: relative;
        padding-left: 4px;

        &__classifiers {
            padding-left: 10px;
            @media (max-width: 600px) {
                overflow-y: scroll;
            }

            &__container {
                min-width: 800px !important;
            }

            p {
                @include newsText(18px, bold, $black);
                margin-right: 24px;
                cursor: pointer;
                display: inline-block;

                &.active {
                    color: $blue;
                }
            }
        }

        &__filters {
            padding-left: 10px;
            padding-bottom: 10px;
            .data-sort {
                padding-top: 7px;
                @include newsText(15px, bold, $white);
                margin-left: -70px;
                @media (max-width: 600px) {
                    .v-btn__content {
                        margin-top: -3px;
                    }
                    margin-left: 0;
                }

                img {
                    padding-left: 20px;
                }
                .v-btn__content {
                    text-transform: none;
                }
            }

            .popularity-sort {
                p {
                    @include newsText(16px, normal, $light-gray);
                    @media (max-width: 600px) {
                        font-size: 13px;
                    }
                }
            }
        }
    }

    .restrictHeight {
        overflow-y: scroll;
        height: 800px;

        &:before {
            content: "";
            width: 100%;
            height: 20px;
            z-index: 1;
            @include scrimGradient(
                    $startColor: black,
                    $direction: to bottom,
                    $ease: ease-in-out,
                    $offsetStart: 0,
                    $offsetEnd: 100
            );
            position: absolute;
            top: 0;
            left: 3px;
            opacity: 0.2;
        }
    }

    @-webkit-keyframes fadeInCalendar {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fadeInCalendar {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @-webkit-keyframes fadeOutCalendar {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    @keyframes fadeOutCalendar {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    .fadeCalendar-enter-active,
    .fadeCalendarIn {
        -webkit-animation-duration: 0.5s;
        animation-duration: 0.5s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .fadeCalendar-leave-active,
    .fadeCalendarOut {
        -webkit-animation-duration: 0.3s;
        animation-duration: 0.3s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .fadeCalendar-enter-active,
    .fadeCalendarIn {
        -webkit-animation-name: "fadeInCalendar";
        animation-name: "fadeInCalendar";
    }

    .fadeCalendar-leave-active,
    .fadeCalendarOut {
        -webkit-animation-name: "fadeOutCalendar";
        animation-name: "fadeOutCalendar";
    }

    .news-item:not(:last-of-type) {
        margin-bottom: 1rem;
    }

    .news-item {
        z-index: 0;

        &.elevation-2 {
            .v-card {
                box-shadow: none !important;
            }
        }

        .news-item__image {
            height: 1px;
            min-height: 100%;
        }
    }
</style>