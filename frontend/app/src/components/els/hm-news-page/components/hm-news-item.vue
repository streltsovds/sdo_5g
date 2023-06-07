<template>
    <div class="hm-news-item">
        <v-dialog v-model="isDescriptionShowed" max-width="1185px" width="100%">
            <v-layout justify-center width="100%">
                <v-card text class="news-full-text" style="width: 100%; max-width: 1185px;">
                    <v-container>
                        <v-card text ref="newsBodyD">
                            <v-card-title>
                                <span class="headline" v-html="news.name"></span>
                                <v-btn small fab absolute color="white" style="top:-15px;right:-15px;"
                                       @click="closeDescription" icon>
                                    <svg
                                        width="24"
                                        height="24"
                                        style="width: 32px;"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path d="M12.5992 12L16.9246 7.67466C17.0899 7.50879 17.0902 7.24026 16.9246 7.07466C16.7582 6.90879 16.4899 6.90879 16.324 7.07466L11.999 11.3997L7.67362 7.07466C7.50855 6.90879 7.23922 6.90879 7.07362 7.07466C6.90775 7.24026 6.90775 7.50879 7.07362 7.67466L11.3987 12L7.07389 16.3248C6.90802 16.4907 6.90802 16.7592 7.07389 16.9253C7.15682 17.008 7.26535 17.0499 7.37389 17.0496C7.48269 17.0499 7.59122 17.008 7.67389 16.9253L11.999 12.6003L16.324 16.9253C16.407 17.008 16.5158 17.0499 16.6243 17.0499C16.7326 17.0499 16.8414 17.008 16.9246 16.9253C17.0902 16.7592 17.0902 16.4907 16.9243 16.3248L12.5992 12Z" fill="black"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0 12C0 5.38347 5.38347 0 12 0C18.6168 0 24 5.38347 24 12C24 18.6165 18.6168 24 12 24C5.38347 24 0 18.6168 0 12ZM0.849265 11.9999C0.849265 18.1485 5.85167 23.1509 12.0002 23.1509C18.149 23.1509 23.1511 18.1485 23.1511 12.0002C23.1511 5.85115 18.149 0.848748 12.0002 0.848748C5.8514 0.848748 0.849265 5.85088 0.849265 11.9999Z" fill="black"/>
                                    </svg>
                                </v-btn>
                            </v-card-title>
                            <v-divider></v-divider>
                            <v-card-text class="news-full-text__body" v-html="news.message">
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
        <v-layout v-show="isMobileDescriptionShowed" row wrap class="hm-news-item__mobile-popup">
          <div class="hm-news-item__mobile-popup-wrapper">
            <div class="hm-news-item__mobile-popup-title-container">
              <div v-html="news.name" class="news-body__title"></div>
              <div class="close-button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" @click="isMobileDescriptionShowed = false">
                    <path d="M12.5992 12L16.9246 7.67466C17.0899 7.50879 17.0902 7.24026 16.9246 7.07466C16.7582 6.90879 16.4899 6.90879 16.324 7.07466L11.999 11.3997L7.67362 7.07466C7.50855 6.90879 7.23922 6.90879 7.07362 7.07466C6.90775 7.24026 6.90775 7.50879 7.07362 7.67466L11.3987 12L7.07389 16.3248C6.90802 16.4907 6.90802 16.7592 7.07389 16.9253C7.15682 17.008 7.26535 17.0499 7.37389 17.0496C7.48269 17.0499 7.59122 17.008 7.67389 16.9253L11.999 12.6003L16.324 16.9253C16.407 17.008 16.5158 17.0499 16.6243 17.0499C16.7326 17.0499 16.8414 17.008 16.9246 16.9253C17.0902 16.7592 17.0902 16.4907 16.9243 16.3248L12.5992 12Z" fill="black"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 12C0 5.38347 5.38347 0 12 0C18.6168 0 24 5.38347 24 12C24 18.6165 18.6168 24 12 24C5.38347 24 0 18.6168 0 12ZM0.849265 11.9999C0.849265 18.1485 5.85167 23.1509 12.0002 23.1509C18.149 23.1509 23.1511 18.1485 23.1511 12.0002C23.1511 5.85115 18.149 0.848748 12.0002 0.848748C5.8514 0.848748 0.849265 5.85088 0.849265 11.9999Z" fill="black"/>
                </svg>
              </div>
            </div>
            <div class="news-body" ref="newsBodyM">
              <v-img max-height="100%" max-width="100%"
                v-if="news.icon_url === null || news.icon_url === ''"
                :src="require('../assets/images/default_news.svg')"/>
              <v-img max-height="100%" max-width="100%" v-else :src="news.icon_url"/>
              <div v-html="news.announce" class="news-body__announce"></div>
              <div v-html="news.message" class="news-body__text"></div>
            </div>
            <div class="hm-news-item__mobile-popup-date-wrapper">
              <div class="date">
                {{ postTime }}
              </div>
              <div class="author">
                <v-icon class="author__icon">account_circle</v-icon>
                <div>{{ news.author }}</div>
              </div>
            </div>
          </div>
        </v-layout>
        <v-card
          class="news-card"
        >
          <div class="news-card__image">
            <v-img
              height="100%"
              width="100%"
              v-if="news.icon_url === null || news.icon_url === ''"
              :src="$vuetify.breakpoint.xsOnly ? require('../assets/images/default_news_mobile.svg') : require('../assets/images/default_news.svg')"
            />
            <v-img height="100%" width="100%" v-else :src="news.icon_url"/>
          </div>
          <div class="news-card__content-wrapper">
            <div class="news-card__text-container">
              <div class="news-card__text-container__title-wrapper">
                <p class="news-card__text-container__title" v-html="news.name"></p>
                <div class="news-card__text-container__data">
                  {{ postTime }}
                </div>
              </div>
              <p class="news-card__text-container__text" v-html="news.announce"></p>
            </div>
            <div class="news-card__buttons-wrapper">
              <like
                class="news-card__like"
                @up="performLike"
                @down="performDislike"
                :data="likeData"
              />
              <div class="news-card__read-btn">
                <v-btn
                  text
                  class="button"
                  :class="restNews ? 'rest-news-button' : ''"
                  :small="$vuetify.breakpoint.xsOnly"
                  :loading="isDescriptionShowed"
                  @click.prevent="showDescription"
                >
                  {{ _('Подробнее') }}
                </v-btn>
              </div>
            </div>
          </div>
        </v-card>
    </div>
</template>

<script>
    import moment from "moment";
    import like from "./newsComponents/like";

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
                    let year = date[2].slice(2,4)
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
            if(this.$refs.newsBodyD) {
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
            }
        },
        methods: {
            getIconPath(iconName) {
                return iconName ? require(`../assets/images/${iconName}`) : ''
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
    @import "colors.scss";
    @import "mixins.scss";

    .news-full-text {
        img {
            width: 100%;
            height: auto;
        }

        .v-card {
            box-shadow: none !important;
        }

        .v-card__title,
        .v-card__text {
          padding: 16px 0;
        }

        .headline {
          font-weight: 500;
        }

        .container {
          padding-right: 32px;
          padding-left: 32px;
          max-width: 100%;
        }
    }

    .hm-news-item {
        &__mobile-popup {
          position: fixed;
          top: 0;
          right: 0;
          z-index: 1000;
          width: 100vw;
          height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(0, 0, 0, 0.4);
          padding: 16px;
          margin: 0 !important;
          &-wrapper {
            padding: 26px 16px;
            height: min-content;
            max-height: 100%;
            width: 100%;
            background-color: $white;
            border-radius: 8px;
          }
          &-title-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 12px;
            border-bottom: 1px solid #DADADA;
          }
          &-date-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            width: 100%;
            border-top: 1px solid #DADADA;
            .date {
              font-weight: normal;
              font-size: 12px;
              line-height: 17px;
              letter-spacing: 0.1px;
              color: #B9C3D2;
            }
            .author {
              display: flex;
              align-items: center;
              font-weight: normal;
              font-size: 12px;
              line-height: 15px;
              letter-spacing: 0.02em;
              color: #666666;
              &__icon {
                margin-right: 8px;
              }
            }
          }

          .close-button {
            cursor: pointer;
            margin-left: 10px;
          }

          .news-body {
            display: flex;
            flex-direction: column;
            margin-top: 0;
            overflow-y: auto;
            max-height: 60vh;
            width: calc(100% + 29px);
            margin: 0 -16px;
            padding: 0 16px;
            margin-right: -13px;
            padding-right: 13px;
            &::-webkit-scrollbar {
              width: 4px;
              height: 4px;
            }
            &::-webkit-scrollbar-thumb:hover {
              background: #70889E;
            }
            &::-webkit-scrollbar-track {
              -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
              border-radius: 4px;
            }
            &::-webkit-scrollbar-thumb {
              background-color: #706e6e;
              border-radius: 4px;
            }
            &__text, &__announce {
              font-style: normal;
              font-weight: 300;
              font-size: 14px;
              line-height: 24px;
              color: #1E1E1E;
              margin-top: 16px;
            }

            &__title {
              font-weight: normal;
              font-size: 18px;
              line-height: 21px;
              letter-spacing: 0.02em;

              color: #1E1E1E;
            }
          }
      }

        .news-card {
          display: flex;
          height: 250px;
          font-family: Roboto, sans-serif;
          margin-bottom: 26px;

          &__image {
            width: 33%;
            max-width: 477px;
            height: 100%;
            .v-image {
              border-radius: 4px 0 0 4px;
            }
          }

          &__content-wrapper {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            overflow: hidden;
            padding: 26px;
            padding-bottom: 22px;
          }
          &__text-container {
            display: flex;
            flex-direction: column;

            &__title-wrapper {
              display: flex;
              justify-content: space-between;
              align-items: flex-start;
              margin-bottom: 16px;
            }

            &__title {
              font-style: normal;
              font-weight: 500;
              font-size: 24px;
              line-height: 28px;
              letter-spacing: 0.02em;
              color: #1E1E1E;
              margin-right: 10px;
              margin-bottom: 0 !important;
              height: 56px;
              overflow: hidden;
              -webkit-line-clamp: 2;
              display: -webkit-box;
              -webkit-box-orient: vertical;
            }
            &__data {
              font-style: normal;
              white-space: nowrap;
              font-weight: normal;
              font-size: 13px;
              line-height: 24px;
              letter-spacing: 0.15px;
              color: #B9C3D2;
              margin-top: 5px;
            }
            &__text {
              font-style: normal;
              font-weight: 300;
              font-size: 16px;
              line-height: 24px;
              color: #1E1E1E;
              margin-bottom: 0 !important;
              max-height: 100px;
              overflow: hidden;
              -webkit-line-clamp: 3;
              display: -webkit-box;
              -webkit-box-orient: vertical;
            }
          }
          &__buttons-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 22px 26px;
            padding-top: 0;
            background-color: inherit;
          }
          &__read-btn {
            .button {
              border: 1px solid #B6D3F3;
              border-radius: 4px;
              font-style: normal;
              font-weight: normal;
              font-size: 14px;
              line-height: 16px;
              letter-spacing: 0.02em;
              color: #2960A0;
              padding: 6px 36px !important;
            }
          }
          &__like {
            display: flex;
            align-items: center;
            padding: 0 !important;
          }
        }

        @media(max-width: 1280px) {
          .news-card {
            margin-bottom: 20px;
            &__content-wrapper {
              padding: 22px;
              padding-bottom: 18px;
            }
            &__text-container {
              &__title {
                font-size: 20px;
                line-height: 24px;
                height: 48px;
              }
              &__text {
                font-size: 14px;
                line-height: 20px;
                -webkit-line-clamp: 5;
              }
            }
          }
        }

        @media(max-width: 1024px) {
          .news-card {
            margin-bottom: 16px;
            &__content-wrapper {
              padding: 20px;
              padding-bottom: 16px;
            }
            &__text-container {
              &__title {
                font-size: 18px;
                line-height: 22px;
              }
            }
          }
        }

        @media(max-width: 768px) {
          .news-card {
            margin-bottom: 10px;
            flex-direction: column;
            height: min-content;
            &__read-btn .button {
              height: 28px !important;
            }
            &__image {
              width: 100%;
              max-width: 100%;
              height: 178px;
              min-height: 178px;
              & .v-image {
                border-radius: 4px 4px 0 0;
              }
            }
            &__content-wrapper {
              height: min-content;
              padding: 16px;
              padding-bottom: 26px;
            }
            &__buttons-wrapper {
              margin-top: 12px;
            }
            &__text-container {
              &__title-wrapper {
                margin-bottom: 12px;
              }
              &__title {
                font-size: 18px;
                line-height: 21px;
                margin-right: 0;
                -webkit-line-clamp: 3;
                height: min-content;
              }
              &__text {
                margin-bottom: 24px;
                font-size: 12px;
                line-height: 16px;
                -webkit-line-clamp: 8;
                max-height: 128px;
              }
              &__data {
                position: absolute;
                bottom: 27px;
                left: 65px;
              }
            }
          }
        }
    }
</style>
