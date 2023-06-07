<template>
  <div id="sidebarTask">
    <div class="sidebar-task">
      <div class="sidebar-task__icon">

        <div class="sidebar-task__icon-default">
          <div class="sidebar-task__icon-default-icon">
            <file-icon type="task" />
          </div>
        </div>

        <div class="sidebar-task__edit-icon"
             v-if="dataSidebar.editUrl"
        >
          <hm-sidebar-button-edit :link="dataSidebar.editUrl" />
        </div>
      </div>

      <div class="sidebar-task__info"
           :style="{paddingTop: dataSidebar.editUrl ? '47px' : ''}"
      >
        <h1 class="sidebar-task__title">
          {{ dataSidebar.task.title }}
        </h1>
        <div class="sidebar-task__info-preview"
             v-if="dataSidebar.previewUrl"
        >
          <a :href="dataSidebar.previewUrl">
            <svg-icon name="visible" style="width: 16px; height: 13px" color="#FFFFFF" />
            <span>Предварительный просмотр </span>
          </a>
        </div>

        <div class="sidebar-task__info-description" v-if="dataSidebar.task.description">
          <div class="sidebar-task__info-description-title">
            <svg-icon name="reports" color=" #1F8EFA" />
            <span>Описание</span>
          </div>
          <div class="sidebar-task__info-description-body">
            <span>{{ dataSidebar.task.description }}</span>
          </div>
        </div>
        <div class="sidebar-task__info-classification" v-if="Object.keys(dataSidebar.taskClassifiers).length > 0 || dataSidebar.taskClassifiers.length > 0">
          <div class="sidebar-task__info-classification__title">
            <svg-icon name="classification" color="#4A90E2" />
            <span>Классификация</span>
          </div>
          <div class="sidebar-task__info-classification__body">
            <ul>
              <li v-for="(item, i) in dataSidebar.taskClassifiers" :key="i">
                <span>{{ item.name }}</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="sidebar-task__info-mark" v-if="Object.keys(dataSidebar.taskTags).length > 0 || dataSidebar.taskTags.length > 0">
          <div class="sidebar-task__info-mark__title">
            <svg-icon name="Mark" color="#4A90E2" />
            <span>Метки</span>
          </div>
          <div class="sidebar-task__info-mark__body">
            <div v-for="(item, i) in dataSidebar.taskTags" :key="i">
              <span>#{{ item }}</span>
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
  computed: {
    // проверка на пустоту всех файлов
    emptyData() {
      return (Object.keys(this.dataSidebar.taskTags).length === 0 || this.dataSidebar.taskTags.length === 0) &&
        (Object.keys(this.dataSidebar.taskClassifiers).length === 0 || this.dataSidebar.taskClassifiers.length === 0) &&
        !this.dataSidebar.task.description
    }
  }
};
</script>

<style lang="scss">
#sidebarTask {
  width: 100%;
  height: 100%;
  .sidebar-task {
    width: 100%;
    height: 100%;
    &__title{
      font-weight: 500;
      font-size: 18px;
      line-height: 24px;
      letter-spacing: .02em;
      color: #1e1e1e;
      margin-bottom: 10px;
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

      .hm-slider-empty-block {
        margin-top: 15px;
        > div {
          margin: 0!important;
        }
      }
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
            font-weight: 300;
            font-size: 14px;
            line-height: 21px;
            letter-spacing: 0.02em;
            color: #FFFFFF;
          }
        }
      }
      &-description {
        width: 100%;
        margin-top: 20px;
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
        margin-top: 20px;
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
        margin-top: 20px;
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
