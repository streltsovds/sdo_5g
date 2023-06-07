<template>
  <div id="sidebarResourceEnduser">
    <div class="sidebar-resource-enduser">
      <div class="sidebar-resource-enduser__icon">
        <div class="sidebar-resource-enduser__icon-default">
          <div class="sidebar-resource-enduser__icon-default-icon">
            <file-icon :type="dataSidebar.resource.type == 'external' ? dataSidebar.resource.filetype : dataSidebar.resource.type" />
          </div>
        </div>
        <div class="sidebar-resource-enduser__edit-icon" v-if="!!dataSidebar.editUrl">
          <hm-sidebar-button-edit :link="dataSidebar.editUrl" />
        </div>
      </div>
      <div class="sidebar-resource-enduser__info"
           :style="{paddingTop: dataSidebar.editUrl ? '47px' : ''}"
      >
        <h1 class="sidebar-resource-enduser__title">
          {{ dataSidebar.resource.title }}
        </h1>
        <div class="sidebar-resource-enduser__info-preview"
             v-if="dataSidebar.previewUrl"
        >
          <a :href="dataSidebar.previewUrl">
            <svg-icon name="visible" style="width: 16px; height: 13px" color="#FFFFFF" />
            <span>{{ _('Предварительный просмотр') }} </span>
          </a>
        </div>
        <div class="sidebar-resource-enduser__info-preview"
             v-if="dataSidebar.editMaterialUrl"
        >
          <a :href="dataSidebar.editMaterialUrl">
            <svg-icon name="editContent" style="width: 18px; height: 18px" color="#FFFFFF" />
            <span>{{ _('Редактировать содержимое') }} </span>
          </a>
        </div>
        <div class="sidebar-resource-enduser__info-description" v-if="dataSidebar.resource.description">
          <div class="sidebar-resource-enduser__info-description-title">
            <svg-icon name="reports" color=" #1F8EFA" />
            <span>{{ _('Описание') }}</span>
          </div>
          <div class="sidebar-resource-enduser__info-description-body">
            <span>{{ dataSidebar.resource.description }}</span>
          </div>
        </div>
        <div class="sidebar-resource-enduser__info-date">
          <svg-icon name="calendar" style="width: 20px; height: 18px;" color="#1F8EFA" />
          <span class="sidebar-resource-enduser__info-date-text">{{ _('Дата обновления:') }} </span>
          <span class="sidebar-resource-enduser__info-date-value">{{ dataSidebar.resource.updated | dateFormat }}</span>
        </div>
        <div class="sidebar-resource-enduser__info-classification" v-if="isSetResourceClassifiers">
          <div class="sidebar-resource-enduser__info-classification__title">
            <svg-icon name="classification" color="#4A90E2" />
            <span>{{ _('Классификация') }}</span>
          </div>
          <div class="sidebar-resource-enduser__info-classification__body">
            <ul>
              <li v-for="item in dataSidebar.resourceClassifiers" :key="item.classifier_id">
                <span>{{ item.name }}</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="sidebar-resource-enduser__rating">
          <div class="sidebar-resource-enduser__rating-left">
            <span class="sidebar-resource-enduser__rating-text">{{ _('Рейтинг') }}</span>
            <hm-rating :rating="dataSidebar.materialRating"
                       :resource="dataSidebar.resource"
                       :count="dataSidebar.materialRatingCount"
                       :readonly="false"
            />
          </div>
          <span class="sidebar-resource-enduser__rating-value">{{ dataSidebar.materialRating }}</span>
        </div>
        <div class="sidebar-resource-enduser__votes">
          <span class="sidebar-resource-enduser__votes-text">{{ _('Количество голосов:') }}</span>
          <span class="sidebar-resource-enduser__votes-value">{{ dataSidebar.materialRatingCount }}</span>
        </div>
<!--        <div class="sidebar-resource-enduser__history">-->
<!--          <div class="sidebar-resource-enduser__history-title">-->
<!--            <svg-icon name="history" color="#4A90E2" />-->
<!--            <span>{{ _('История изменений') }}</span>-->
<!--          </div>-->
<!--          <ul class="sidebar-resource-enduser__history-list">-->
<!--            <li v-if="!isSetRevisions">-->
<!--              <span>{{ _('Это первая версия ресурса, история изменений пуста.') }}</span>-->
<!--            </li>-->
<!--            <li v-else v-for="(revision,index) in dataSidebar.revisions" :key="index">-->
<!--              <span>{{ revision }}</span>-->
<!--            </li>-->
<!--          </ul>-->
<!--        </div>-->
        <div class="sidebar-resource-enduser__related-res" v-if="isSetRelated">
          <div class="sidebar-resource-enduser__related-res-title">
            <svg-icon name="link" color="#4A90E2" />
            <span>{{ _('Связанные ресурсы') }}</span>
          </div>
          <v-carousel
            v-model="currentSlideIndex"
            :show-arrows="Object.keys(dataSidebar.relatedResources).length > 1"
            hide-delimiters
            height="120px"
          >
            <v-carousel-item
              v-for="(resource, key) in dataSidebar.relatedResources"
              :key="key"
            >
              <div class="sidebar-resource-enduser__related-res-type">
                <div class="sidebar-resource-enduser__related-res-type-icon-wrapper2">
                  <div class="sidebar-resource-enduser__related-res-type-icon-wrapper1">
                    <file-icon :type="resource.type == 'external' ? resource.filetype : resource.type" />
                  </div>
                </div>
              </div>
            </v-carousel-item>
          </v-carousel>
          <div class="sidebar-resource-enduser__related-res-wrapper">
            <p class="sidebar-resource-enduser__related-res-body-title">
              {{ currentRelatedResource.title }}
            </p>
            <p class="sidebar-resource-enduser__related-res-description">
              {{ currentRelatedResource.description }}
            </p>
          </div>
        </div>
        <div class="sidebar-resource-enduser__info-mark" v-if="isSetResourceTags">
          <div class="sidebar-resource-enduser__info-mark__title">
            <svg-icon name="Mark" color="#4A90E2" />
            <span>{{ _('Метки') }}</span>
          </div>
          <div class="sidebar-resource-enduser__info-mark__body">
            <div v-for="(item, i) in dataSidebar.resourceTags" :key="i">
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
import HmRating from "@/components/els/hm-rating";
import moment from 'moment';
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";

export default {
  components: { SvgIcon, FileIcon, HmRating, HmSidebarButtonEdit},
  filters:{
    dateFormat(dataText){
      return moment(dataText).format('DD.MM.YYYY');
    }
  },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  data(){
    return {
      currentSlideIndex: 0
    }
  },
  computed:{
    isSetResourceClassifiers() {
      return !!Object.keys(this.dataSidebar.resourceClassifiers).length
    },
    isSetResourceTags() {
      return !!Object.keys(this.dataSidebar.resourceTags).length
    },
    isSetRevisions() {
      return !!Object.keys(this.dataSidebar.revisions).length
    },
    isSetRelated() {
      return !!Object.keys(this.dataSidebar.relatedResources).length
    },
    currentRelatedResource(){
      const resKey = Object.keys(this.dataSidebar.relatedResources)[this.currentSlideIndex];
      return this.dataSidebar.relatedResources[resKey];
    }
  }
};
</script>

<style lang="scss">
#sidebarResourceEnduser {
  width: 100%;
  height: 100%;
  .sidebar-resource-enduser {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: auto;
    &__title{
      font-weight: 500;
      font-size: 18px;
      line-height: 24px;
      letter-spacing: .02em;
      color: #1e1e1e;
      margin-bottom: 10px;
    }
    &__votes{
      display: flex;
      justify-content: space-between;
      margin-top: 18px;
      &-text{
        font-weight: 500;
        font-size: 14px;
        margin-right: 12px;
      }
      &-value{
        color: #3E4E6C;
      }
    }
    &__rating{
      display: flex;
      justify-content: space-between;
      margin-top: 18px;
      button{
        padding: 0;
      }
      &-text{
        font-weight: 500;
        font-size: 14px;
        margin-right: 12px;
      }
      &-left{
        display: flex;
      }
      &-value{
        color: #3E4E6C;
      }
    }
    &__icon {
      width: 100%;
      height: 215px;
      min-height: 215px;
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
      max-height: 60%;
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
    &__info-date{
        font-weight: 500;
        font-size: 16px;
        margin-top: 18px;
      > svg{
        margin-right: 13px;
      }
      &-value{
        color: #1F8EFA;
      }
    }
    &__related-res{
      margin-top: 20px;
      width: 100%;
      display: flex;
      flex-direction: column;
      &-title {
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
      &-body-title{
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 12px;
      }
      &-description{
        font-size: 12px;
        color: #3E4E6C;
        min-height: 150px;
      }
      &-type{
        display: flex;
        justify-content: center;
        height: 100%;
      }
      &-type-icon-wrapper2{
        display: flex;
      }
      &-type-icon-wrapper2{
        display: flex;
        align-self: center;
      }
    }
    &__history {
      margin-top: 20px;
      width: 100%;
      display: flex;
      flex-direction: column;
      &-title {
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
      &-list{
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
    &__edit-icon{
      position: absolute;
      right: 18px;
      bottom: 0px;
    }
    }
  }
</style>
