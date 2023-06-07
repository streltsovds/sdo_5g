<template>
  <div id="sidebarExtrasManager">
    <div class="sidebar-extras-manager">
      <div class="sidebar-extras-manager__img"
        v-if="!dataSidebar.subject.image">
        <img :src="dataSidebar.subject.icon">
        <div class="sidebar-extras-manager__img-info">
          <span>{{ dataSidebar.subject.name }}</span>
        </div>
        <div class="sidebar-extras-manager__image-edit" v-if="dataSidebar.showEdit">
          <hm-sidebar-button-edit :link="dataSidebar.editUrl"/>
        </div>
      </div>
      <div class="sidebar-extras-manager__image"
        v-else
        :style="{ backgroundImage: `url(${dataSidebar.subject.image})` }"
      >
        <div class="sidebar-extras-manager__image-info">
          <span>{{ dataSidebar.subject.name }}</span>
        </div>
        <div class="sidebar-extras-manager__image-edit" v-if="dataSidebar.showEdit">
          <hm-sidebar-button-edit :link="dataSidebar.editUrl"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import FileIcon from "@/components/icons/file-icon/index";
import SvgIcon from "@/components/icons/svgIcon";
import EditActionButton from "@/components/els/subject/lessons/editActionButton";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
export default {
  components: { HmSidebarButtonEdit, EditActionButton, SvgIcon, FileIcon },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  computed: {

  },
  methods: {

  },
};
</script>

<style lang="scss">
#sidebarExtrasManager {
  width: 100%;
  height: 100%;
  .sidebar-extras-manager {
    width: 100%;
    height: 100%;
    overflow: auto;
    &__img {
      width: 100%;
      height: 164px;
      background: #a8c7ec;
      position: relative;
      display: flex;
      justify-content: center;
      > img {
        margin-top: 20px;
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
}
</style>
