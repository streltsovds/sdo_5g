<template>
  <div class="hm-news-banner-block_dialog">
    <!-- <v-dialog v-model="dialog"
              :fullscreen="$vuetify.breakpoint.xsOnly">
      <v-card class="overflowhidden">
        <v-card :flat="$vuetify.breakpoint.xsOnly"
                :color="$vuetify.breakpoint.xsOnly ? 'transparent' : null"
                class="hm-news-banner-block_dialog-card">
          <v-card-title class="hm-news-banner-block_dialog-title pa-0 pl-3 pb-2 pt-2">
            <span class="headline" v-html="item.name !== '' ? item.name : item.announce"></span>
            <v-spacer></v-spacer>
            <v-btn small fab absolute class="hm-news-banner-block_dialog-btn" @click="$emit('close')" icon>
              <v-icon large>close</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text ref="itemBody"
                       class="hm-news-banner-block_dialog-text"
                       :style="$vuetify.breakpoint.smAndUp ? 'max-height: 400px; overflow-y: auto;' : ''"
                       v-html="item.message">
          </v-card-text>
          <v-spacer></v-spacer>
          <v-divider></v-divider>
          <v-card-actions>
            <v-list dense>
              <v-list-item>
                <v-list-item-action>
                  <v-icon>today</v-icon>
                </v-list-item-action>
                <v-list-item-title>{{ postTime }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-action>
                  <v-icon>account_circle</v-icon>
                </v-list-item-action>
                <v-list-item-title>{{ item.author }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card-actions>
        </v-card>
      </v-card>
    </v-dialog> -->

    <!-- Взято со страницы новостей для единообразия -->

    <v-dialog v-model="dialog" max-width="1185px" width="100%">
            <v-layout justify-center width="100%">
                <v-card text class="news-full-text" style="width: 100%; max-width: 1185px;">
                    <v-container>
                        <v-card text ref="newsBodyD">
                            <v-card-title>
                                <span class="headline" v-html="item.name !== '' ? item.name : item.announce"></span>
                                <v-btn small fab absolute color="white" style="top:-15px;right:-15px;"
                                       @click="$emit('close')" icon>
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
                            <v-card-text class="news-full-text__body" v-html="item.message">
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
                                    <div style="position: absolute;top: 3px;left: 30px;">{{ item.author }}</div>
                                </v-flex>
                            </v-layout>
                        </v-card>
                    </v-container>
                </v-card>
            </v-layout>
        </v-dialog>
  </div>
</template>
<script>
  import moment from "moment";

  moment.locale("ru");
  export default {
    props: {
      item: {
        type: Object,
        required: true
      },
      isOpen: {
        type: Boolean
      }
    },
    data() {
      return {
        dialog: this.isOpen
      }
    },
    computed: {
      isMobile() {
        return document.querySelector("body").classList.contains("is-mobile");
      },
      postTime() {
        if (!this.item.created) return;
        return moment(this.item.created, "YYYY-MM-DD HH:mm:ss").format(
          "DD[.]MM[.]YYYY [в] HH[:]mm"
        );
      },
    },
    watch: {
      isOpen() {
        this.dialog = this.isOpen;
      },
      dialog(v) {
        if (!v) setTimeout(() => this.$emit("close"), 500);
      }
    },
    mounted() {
      this.$refs.itemBody.addEventListener("click", this.downloadForApp);
      //   console.log(this.item);
    },
    methods: {
      downloadForApp(event) {
        const { href } = event.target;
        if (
          !(event.target instanceof HTMLAnchorElement) ||
          !this.isMobile ||
          !this.checkHref(href)
        ) return;

        event.preventDefault();
        let data = {
          event_id: "download_file",
          url: href
        };
        window.COMMON_DATA = data;
        parent.window.postMessage(data, "*");

      },
      checkHref (href) {
        if (!href) return false;

        if (href.includes("content-type")) return true;
        const extension = href.split(".").pop();
        return this.checkExtension(extension);
      },
      checkExtension (ext) {
        this.downloadableExtensions.includes(ext);
      }
    }
  }
</script>
<style lang="scss" scoped>
  .hm-news-banner-block_dialog {
    &-card {
      min-height: 200px;
    }
    &-title {
      position: relative;
      padding-right: 60px !important;
    }
    &-btn {
      top: 6px;
      right: 6px;
    }
    &-text img {
      max-width: 100%;
      height: auto;
    }
  }
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
      padding: 0;
    }

    .container {
      padding-right: 32px;
      padding-left: 32px;
      max-width: 100%;
    }
  }
</style>
