<template>
  <div class="subject-wrapper" ref="subjectWrapper">
    <swiper
      v-if="coursesIsSet"
      ref="mySwiper"
      :options="swiperOption"
    >
      <template>
        <swiper-slide v-for="(el, i) in dataSub" :key="i">
          <subject-card-course :data-card="el" />
        </swiper-slide>
        <div class="swiper-scrollbar" slot="scrollbar" />
      </template>
    </swiper>
    <hm-empty v-else>
        {{ _('Нет данных для отображения') }}
      </hm-empty>
  </div>
</template>

<script>
import 'swiper/dist/css/swiper.css'
import subjectCardCourse from '@/components/els/subject/cardCourse/subjectCardCourse'
import { swiper, swiperSlide } from "vue-awesome-swiper";
import HmEmpty from "@/components/helpers/hm-empty"

export default {
  components: {subjectCardCourse, swiper, swiperSlide, HmEmpty },
  props: {
    dataSubject: {
      type: [Object, Array],
      default: () => {}
    }
  },
  data() {
    return {
      dataSub: this.dataSubject || {},
      swiperOption: {
        slidesPerView: 'auto',
        spaceBetween: 30,
        grabCursor: true,
        scrollbar: {
          el: '.swiper-scrollbar',
          draggable: true
        },
        mousewheel: true
      },

    }
  },
  computed: {
    coursesIsSet(){
      return Object.keys(this.dataSub).length > 0;
    }
  },
  mounted() {
    this.resize();
    window.addEventListener('resize', this.resize)
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.resize)
  },
  methods: {
    resize() {
      let elSwiper = this.$refs.paginationSwiper;

      if (!elSwiper) {
        return
      }

      elSwiper.style.width = `${this.$refs.subjectWrapper.offsetWidth}px`;
    }
  }
}
</script>

<style lang="scss">
.subject-wrapper {
  width: 100%;
  /*height: 100%;*/
  // height: 418px;
  .subjectCardCourse {
    margin: auto;
    .subject-card-course__info-classifiers {
      display: block;
    }
  }

  .swiper-wrapper {
    padding-bottom: 30px;
  }

  .swiper-slide {
    width: 360px;
  }
}
@media(max-width: 768px) {
  .subject-wrapper {
    .swiper-wrapper {
      padding: 0 16px;
      padding-bottom: 30px;
    }
    .swiper-slide {
      width: 230px;
      margin-right: 16px !important;
      &:last-child {
        margin-right: 0 !important;
      }
    }
  }
}
</style>
