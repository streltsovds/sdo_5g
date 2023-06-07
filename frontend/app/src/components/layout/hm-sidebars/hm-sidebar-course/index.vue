<template>
  <div id="sidebarCourse">
    <div class="sidebar-course">
      <div class="sidebar-course__icon">
        <div class="sidebar-course__icon-img" v-if="dataSidebar.course.img" />
        <div class="sidebar-course__icon-default" v-else>
          <div class="sidebar-course__icon-default-icon">
            <file-icon type="scorm" />
          </div>
        </div>
        <div class="sidebar-course__edit-icon" v-if="!!dataSidebar.editUrl">
          <hm-sidebar-button-edit :link="dataSidebar.editUrl" />
        </div>
      </div>

      <div class="sidebar-course__info"
           :style="{paddingTop: dataSidebar.editUrl ? '47px' : ''}"
      >
        <h1 class="sidebar-course__title">
          {{ dataSidebar.course.Title }}
        </h1>
        <div class="sidebar-course__info-preview"
             v-if="dataSidebar.previewUrl"
        >
          <a :href="dataSidebar.previewUrl">
            <svg-icon name="visible" style="width: 16px; height: 13px" color="#FFFFFF" />
            <span>Предварительный просмотр </span>
          </a>
        </div>
        <div class="sidebar-course__info-preview"
             v-if="dataSidebar.editMaterialUrl"
        >
          <a :href="dataSidebar.editMaterialUrl">
            <svg-icon name="editContent" style="width: 18px; height: 18px" color="#FFFFFF" />
            <span>{{ _('Редактировать содержимое') }} </span>
          </a>
        </div>
        <div class="sidebar-course__info-description" v-if="dataSidebar.course.description">
          <div class="sidebar-course__info-description-title">
            <svg-icon name="reports" color=" #1F8EFA" />
            <span>Описание</span>
          </div>
          <div class="sidebar-course__info-description-body">
            <span>{{ dataSidebar.course.description }}</span>
          </div>
        </div>
        <div class="sidebar-course__info-classification" v-if="Object.keys(dataSidebar.courseClassifiers).length > 0 || dataSidebar.courseClassifiers.length > 0">
          <div class="sidebar-course__info-classification__title">
            <svg-icon name="classification" color="#4A90E2" />
            <span>Классификация</span>
          </div>
          <div class="sidebar-course__info-classification__body">
            <ul>
              <li v-for="(item, index) in dataSidebar.courseClassifiers" :key="index">
                <span>{{ item.name }}</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="sidebar-course__info-mark" v-if="Object.keys(dataSidebar.courseTags).length > 0 || dataSidebar.courseTags.length > 0">
          <div class="sidebar-course__info-mark__title">
            <svg-icon name="Mark" color="#4A90E2" />
            <span>Метки</span>
          </div>
          <div class="sidebar-course__info-mark__body">
            <div v-for="(item, i) in dataSidebar.courseTags" :key="i">
              <span># {{ item }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>


import SvgIcon from "@/components/icons/svgIcon";
import FileIcon from "@/components/icons/file-icon/index";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
export default {
  components: {HmSidebarButtonEdit, SvgIcon, FileIcon},
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
};
</script>

<style lang="scss">
#sidebarCourse {
  width: 100%;
  height: 100%;
  .sidebar-course {
    width: 100%;
    height: 100%;
    &__title{
      font-weight: 500;
      font-size: 18px;
      line-height: 24px;
      letter-spacing: .02em;
      color: #1e1e1e;
      margin-top: 10px;
    }
    &__icon {
      width: 100%;
      height: 215px;
      position: relative;
      &::before{
        content: '';
        display: block;
        height: 130px;
        background-color: #D4E3FB;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
      }
      &-default {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        flex-direction: column;
        position: relative;
        &-icon {
          margin-top: 90px;
          position: relative;
          &::before{
            content: '';
            display: block;
            position: absolute;
            background-color: #A8C7EC;
            border-radius: 50%;
            top: -40px;
            left: -50px;
            width: 160px;
            height: 160px;
          }
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
      &-edit {
        position: absolute;
        bottom: -24px;
        right: 52px;
      }
    }
    &__info {
      width: 100%;
      padding: 16px;
      max-height: 70%;
      overflow: auto;
      &-preview {
        width: 100%;
        height: 36px;
        > a {
          width: 100%;
          height: 100%;
          text-decoration: none;
          background: #FF9800;
          border-radius: 4px;
          display: flex;
          justify-content: center;
          align-items: center;
          > svg {
            margin-right: 8px;
            margin-top: 4px;
          }
          > span {
            font-weight: 500;
            font-size: 14px;
            line-height: 21px;
            letter-spacing: 0.02em;
            color: #FFFFFF;
          }
        }
      }
      &-description {
        width: 100%;
        margin-top: 26px;
        display: flex;
        flex-direction: column;
        &-title {
          height: 24px;
          width: 100%;
          display: flex;
          align-items: center;
          > svg {
            margin-right: 13px;
          }
          > span {
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
        }
        &-body {
          width: 100%;
          max-height: 90px;
          overflow: hidden;
          margin-top: 12px;
          > span {
            font-weight: normal;
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 0.15px;
            color: #3E4E6C;
          }
        }
      }
      &-classification {
        margin-top: 26px;
        width: 100%;
        display: flex;
        flex-direction: column;
        &__title {
          display: flex;
          align-items: center;
          > svg {
            margin-right: 13px;
          }
          > span {
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
        }

        &__body {
          width: 100%;
          > ul {
            margin-top: 7px;
            > li {
              > span {
                font-weight: normal;
                font-size: 12px;
                line-height: 18px;
                letter-spacing: 0.15px;
                color: #70889E;
              }
              &:not(:last-child) {
                margin-bottom: 8px;
              }
            }
          }
        }
      }

      &-mark {
        width: 100%;
        display: flex;
        flex-direction: column;
        margin-top: 26px;
        &__title {
          height: 24px;
          width: 100%;
          display: flex;
          align-items: center;
          > svg {
            margin-right: 13px;
          }
          > span {
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
        }
        &__body {
          margin-top: 6px;
          display: flex;
          flex-wrap: wrap;
          > div {
            background: rgba(230, 230, 230, 0.4);
            border-radius: 30px;
            padding:  4px 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 8px;
            margin-bottom: 12px;
            > span {
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
    &__edit-icon{
      position: absolute;
      right: 18px;
      bottom: 0px;
    }

  }
}
</style>
