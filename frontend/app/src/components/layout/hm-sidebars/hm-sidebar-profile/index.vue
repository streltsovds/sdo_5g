<template>
  <div class="hm-sidebar-profile">
    <div class="hm-sidebar-profile__header">

      <div class="hm-sidebar-profile__header__img"
           v-if="!dataSidebar.icon">
        <img :src="dataSidebar.default_icon">
        <div class="hm-sidebar-profile__header__img-info">
          <span>{{ dataSidebar.profile.name | truncate(100)}}</span>
        </div>
      </div>
      <div class="hm-sidebar-profile__header__image"
           v-else
           :style="{ backgroundImage: `url(${dataSidebar.icon})` }"
      >
        <div class="hm-sidebar-profile__header__image-info">
          <span>{{ dataSidebar.profile.name | truncate(100)}}</span>
        </div>
      </div>
      <div class="hm-sidebar-profile__header__image-edit" v-if="editUrl">
        <hm-sidebar-button-edit :link="editUrl"/>
      </div>
      
    </div>
    <div class="hm-sidebar-profile__body">
      <h2 class="hm-sidebar-profile__body-title">{{ dataSidebar.title }}</h2>
      <!-- <div class="hm-sidebar-profile__body-content">
        <h3 class="hm-sidebar-profile__body-content-title"><span>Заполнение профиля</span><span>(%)</span></h3>
      </div> -->
    </div>

  </div>
</template>

<script>
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
export default {
  components: {HmSidebarButtonEdit},
  props: {
    editUrl: {
      type: String,
      default: ""
    },
    dataSidebar: {
      type: Object,
      default: () => {}
    },
  },
  data() {
    return {
    }
  },
  computed:{

  },
  methods: {

  },
}
</script>

<style lang="scss">
.hm-sidebar-profile {
  width: 100%;
  height: 100%;
  &__header {
    width: 100%;
    height: 164px;
    position: relative;
    &__img {
      width: 100%;
      height: 164px;
      background: #a8c7ec;
      position: relative;
      display: flex;
      justify-content: center;
      > img {
        margin-top: -20px;
        width: 100px;
      }

      &-edit {
        position: absolute;
        bottom: -24px;
        right: 52px;
        z-index: 100;
      }

      &-info {
        width: 100%;
        height: 70px;
        position: absolute;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(30, 30, 30, 0.5);
        > span {
          margin-top: 4px;
          font-size: 16px;
          line-height: 20px;
          letter-spacing: 0.02em;
          color: #ffffff;
          text-align: center;
        }
      }
    }
    &__image {
      width: 100%;
      height: 164px;
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      position: relative;
      display: flex;
      justify-content: center;

      &-edit {
        position: absolute;
        bottom: -24px;
        right: 52px;
        z-index: 100;
      }

      &-info {
        width: 100%;
        height: 76px;
        position: absolute;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(30, 30, 30, 0.5);
        > span {
          margin-top: 4px;
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #ffffff;
          text-align: center;
        }
      }
    }
  }
  &__body {
    width: 100%;
    display: flex;
    flex-direction: column;
    padding: 26px 16px;
    box-sizing: border-box;

    &-title {
      font-family: Roboto, sans-serif;
      font-style: normal;
      font-weight: 500;
      font-size: 20px;
      line-height: 24px;
      text-align: center;
      letter-spacing: 0.02em;
      color: #1E1E1E;
      margin-bottom: 34px;
    }

    &-content {
      display: flex;
      flex-direction: column;
    }

    &-content-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-family: Roboto;
      font-style: normal;
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      display: flex;
      align-items: center;
      letter-spacing: 0.02em;
      color: #1E1E1E;
      margin-bottom: 26px;
    }
  }
}
</style>
