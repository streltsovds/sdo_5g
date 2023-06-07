<template>
  <div id="sidebarCatalog">
    <div class="sidebar-catalog">
      <div v-if="dataSidebar.catalogUrl" class="sidebar-catalog__all-courses">
        <a :href="dataSidebar.catalogUrl">
          <svg-icon style="margin-top: -5px;" color="#FFFFFF" name="coursesCatalog" />
          <span>Каталог курсов</span>
        </a>
      </div>
      <div class="sidebar-catalog__recommended">
        <div class="sidebar-catalog__recommended-title">
          <svg-icon color="#1F8EFA" name="education" />
          <span>Рекомендуемые курсы</span>
        </div>
        <div class="sidebar-catalog__recommended-body">
          <carousel-catalog :data-carousel="allCoursesArr" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import CarouselCatalog from "@/components/layout/hm-sidebars/hm-sidebar-catalog/carouselCatalog/carouselCatalog";
export default {
  components: { CarouselCatalog, SvgIcon },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  computed: {
    allCoursesArr() {
      let arr = [];
      for (let item in this.dataSidebar.relatedSubjects) {
        arr.push(this.dataSidebar.relatedSubjects[item]);
      }
      return arr;
    },
  },
  mounted() {
  },
  methods: {},
};
</script>

<style lang="scss">
#sidebarCatalog {
  width: 100%;
  height: 100%;
  padding: 16px;
  overflow: auto;
  .sidebar-catalog {
    width: 100%;
    height: 100%;
    &__all-courses {
      width: 100%;
      height: 44px;
      background: #ff9800;
      border-radius: 4px;
      &:hover {
        background: #FFAE36;
      }
      &:active {
        background: #D17D00;
      }
      > a {
        text-decoration: none;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 3px;
        > svg {
          margin-right: 12px;
        }
        > span {
          font-weight: 500;
          font-size: 14px;
          line-height: 24px;
          letter-spacing: 0.16px;
          text-transform: uppercase;
          color: #ffffff;
        }
      }
    }
    &__recommended {
      width: 100%;
      display: flex;
      flex-direction: column;
      margin-top: 26px;
      &-title {
        > svg {
          margin-right: 12px;
        }
        > span {
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1e1e1e;
        }
      }
      &-body {
        width: 100%;
        margin-top: 16px;
      }
    }
  }
}
</style>
