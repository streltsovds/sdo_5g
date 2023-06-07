<template>
  <div class="kbase-card">
    <hm-kbase-icon :el="dataCard" />
    <div class="kbase-card-data">
      <div class="kbase-card-data__title">
        <span>{{ _(textTitle) }}</span>
      </div>
      <div class="kbase-card-data__classifiers" v-if="dataCard.classifiers.length">
        <swiper :options="ClassifiersOptions">
          <swiper-slide v-for="(el, i) in dataCard.classifiers" :key="i">
            <span>{{ el }}</span>
          </swiper-slide>
        </swiper>
      </div>
      <div class="kbase-card-data__tags">
        <swiper :options="ClassifiersOptions">
          <swiper-slide v-for="(el, i) in dataCard.tag" :key="i">
            <span>#{{ el }}</span>
          </swiper-slide>
        </swiper>
      </div>
    </div>
  </div>
</template>

<script>
import 'swiper/dist/css/swiper.css'
import FileIcon from "@/components/icons/file-icon/index";
import { swiper, swiperSlide } from "vue-awesome-swiper";
import hexDec from '@/utilities/hexDec';
import HmKbaseIcon from "@/components/els/kbase/icon/index";

export default {
  name: "kBaseCard",
  components: {FileIcon, swiper, swiperSlide, HmKbaseIcon},
  props:{
    dataCard: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      alphaColor: '',
      testColorClassifiers: 'rgba(132, 63, 160, 0.7);',
      ClassifiersOptions: {
        slidesPerView: 'auto',
        spaceBetween: 10,
      }
    }
  },
  computed: {
    styleIcon() {
      return this.dataCard.url !== ''  ? {backgroundImage: `url(${this.dataCard.url})`} : '';
    },
    TypeIcon() {
      return this.dataCard.type !== '' ? this.dataCard.type : 'default';
    },
    textTitle() {
      return this.dataCard.title.length > 70 ? this.dataCard.title.slice(0,67) + '...' : this.dataCard.title;
    },
    alphaCol() {
      return {backgroundColor: this.alphaColor}
    },
  },
  methods: {},
}
</script>

<style lang="scss">
.kbase-card {
  width: 330px;
  height: 140px;
  display: flex;
  background: #FFFFFF;
  box-shadow: 5px 5px 25px rgba(179, 179, 179, 0.25);
  border-radius: 4px;
  &-icon {
    width: 113px;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 4px 0 0 4px;
  }
  &-data {
    width: calc(100% - 113px);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    padding: 10px 12px 12px 12px;
    box-sizing: border-box;
    &__title {
      width: 100%;
      height: 39px;
      overflow: hidden;
      display: flex;
      > span {
        font-weight: 500;
        /*:TODO где конфиги???*/
        font-size: 14px;
        line-height: 21px;
        letter-spacing: 0.02em;
        color: #4A4A4A;

      }
    }
    &__classifiers {
      width: 100%;
      height: 26px;
      display: flex;
      overflow: hidden;
      > div {
        .swiper-wrapper {
          height: 26px;
          > div {
            border: 1px solid rgba(132, 63, 160, 0.2);
            background: rgba(218, 211, 253, 0.2);
            border-radius: 4px;
            box-sizing: border-box;
            > span {
              padding: 4px 12px;
              font-weight: normal;
              font-size: 12px;
              line-height: 18px;
              letter-spacing: 0.15px;
              color: rgba(132, 63, 160, 0.7);
            }
          }
        }
      }
    }
    &__tags {
      margin-top: 10px;
      width: 100%;
      display: flex;
      overflow: hidden;
      .swiper-container {
        margin: 0;
      }
      .swiper-wrapper {
        height: 26px;
        > div {
          background: rgba(230, 230, 230, 0.5);
          border-radius: 30px;
          width: auto;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          cursor: grab;
          > span {
            padding: 4px 12px;
            font-weight: normal;
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 0.15px;
            color: #3E4E6C;
          }
        }
      }
    }
  }
}
</style>
