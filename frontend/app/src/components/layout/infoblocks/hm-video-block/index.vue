<template>
  <div class="INFOBLOCK infoblock-video-block" ref="infoBlockVideo">
    <slot v-if="!video" />
    <div v-else v-html="video" />
    <div class="infoblock-video-block__title" v-if="nameVideo">
      <v-progress-linear
        class="infoblock-video-loader"
        v-if="isLoading"
        :indeterminate="true"
        background-opacity="0"
        color="success"
      />
      <span>{{ nameVideo }}</span>
    </div>
    <hm-empty v-if="videos.length === 0">
      <p>Видеролики не созданы. <a v-if="isAdmin" :href="sowEditLink" target="_blank">Создать</a></p>
    </hm-empty>
    <div class="infoblock-video-block__slider" v-else>
      <slider-video
        :videos="videos"
        :active-video="activeVideo"
        :edit-url="sowEditLink"
        @activeEl="activeElparent"
        :disabled="videos.length === 0"
        :is-admin="isAdmin"
      />
    </div>

  </div>
</template>
<script>
import { mapActions } from "vuex";
import SliderVideo from "@/components/layout/infoblocks/hm-video-block/sliderVideo";
import HmEmpty from "@/components/helpers/hm-empty"

export default {
  components: {
    SliderVideo,
    HmEmpty
  },
  props: {
    editUrl: {
      type: String,
      default: null
    },
    videoUrl: {
      type: String,
      default: null
    },
    videos: {
      type: Array,
      default: () => []
    },
    showEdit: {
      type: [Boolean, Number, String],
      default: false
    },
    currentRole: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isLoading: false,
      video: this.videos[0] ? this.videos[0].embedded_code : null,
      nameVideo: this.videos[0] ? this.videos[0].name : null,
      activeVideo: 0,
      style: {
        padding:'0',
        background: '#1e1e1e',
        borderRadius: '4px',
        boxShadow: '0px 11px 15px rgba(0, 0, 0, 0.2), 0px 9px 46px rgba(0, 0, 0, 0.12), 0px 24px 38px rgba(0, 0, 0, 0.14)'
      },
    };
  },
  computed: {
    sowEditLink() {
      return this.showEdit === '0' ? null : this.editUrl
    },
    isAdmin(){
      return this.currentRole === 'admin';
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    loadVideo(data) {
      if (this.isLoading) return;
      this.isLoading = true;
      this.$axios
        .get(`${this.videoUrl}${data.id}`)
        .then(r => {
          if (r.status !== 200 || !r.data) throw new Error();
          this.video = r.data;
          const videoWrapper = document.createElement('div');
          videoWrapper.innerHTML = this.video;

          const videoEl = videoWrapper.children[0];

          if(videoEl.getAttribute('height')){

            const h = videoEl.getAttribute('height');
            const w = videoEl.getAttribute('width');

            // замена размеров при получении видоса
            let widthFrame = videoEl.getAttribute('width');
            let heightFrame = videoEl.getAttribute('height');
            let widthInfoBlockVideo = document.getElementsByClassName('infoblock-video-block')[0].offsetWidth;

            let difference = widthInfoBlockVideo / Number(widthFrame);
            if(!widthFrame.includes('%') && !heightFrame.includes('%')) {
              videoEl.setAttribute('width', widthInfoBlockVideo);
              videoEl.setAttribute('height', Number(heightFrame * difference))
            }
            this.video = videoEl.outerHTML;
          }
          this.activeVideo = data.active;
          this.nameVideo = this.videos.find(el => el.videoblock_id === data.id).name;
        })
        .catch(e => {
          console.error(e);
          this.addErrorAlert("Произошла ошибка при загрузке видео");
        })
        .finally(() => (this.isLoading = false));
    },
    initComp() {
      for(let i in this.style) {
        this.$refs.infoBlockVideo.parentNode.style[i] = this.style[i]
      }
      // замена размеров при получении видоса
      let widthFrame = this.video.split('width=\"')[1].split('\"')[0];
      let heightFrame = this.video.split('height=\"')[1].split('\"')[0];
      let widthInfoBlockVideo = document.getElementsByClassName('infoblock-video-block')[0].offsetWidth;
      let difference = widthInfoBlockVideo / Number(widthFrame);
      if(!widthFrame.includes('%') && !heightFrame.includes('%')) {
        this.video = this.video.replace(/ width="(.*?)" /, ` width="${widthInfoBlockVideo}" `);
        this.video = this.video.replace(/ height="(.*?)" /, ` height="${Number(heightFrame * difference)}" `);
      }
    },
    /**
     * обработка переключения видосика
     * @param data
    */
    activeElparent(data) {
      this.loadVideo(data);
    }
  },
  mounted() {
    if(this.video) {
      this.initComp();
    }
  }
};
</script>
<style lang="scss">
.infoblock-video-block {
  max-height: none !important;
  overflow: hidden;
  border-radius: 4px;
  overflow: hidden!important;
  & iframe {
    width: 100% !important;
  }
  &__title {
    width: 100%;
    height: 56px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 0 30px 0 26px;
    box-sizing: border-box;
    margin-top: -8px;
    position: relative;
    .infoblock-video-loader {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 2px;
    }
    > span {
      font-weight: normal;
      font-size: 20px;
      line-height: 34px;
      letter-spacing: 0.02em;
      color: #FFFFFF;
      white-space: nowrap;
      overflow: hidden;
      padding-right: 5px;
      text-overflow: ellipsis;
    }
  }
  &__slider {
    width: 100%;
    height: 84px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    background: #424242;
  }
}
@media(max-width: 768px) {
  .infoblock-video-block {
    max-width: 100%;
    &__title {
      height: 30px;
      & span  {
        font-size: 14px;
        line-height: 12px;
      }
    }
    &__slider {
      height: 50px;
    }
  }
}
</style>
