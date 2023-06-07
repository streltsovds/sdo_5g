<template>
  <div class="hm-task-preview">
    <div class="hm-task-preview__body">
      <div class="hm-task-preview__body-el" v-for="( el, i ) in getVariant" :key="i">
        <div class="hm-task-preview__body-el__title">
          <span v-html="el.name"></span>
        </div>
        <div class="hm-task-preview__body-el__content">
          <div class="hm-task-preview__body-el__content-description">
            <span v-html="el.description"></span>
          </div>
          <div class="hm-task-preview__body-el__content-files">
            <div class="hm-task-preview__body-el__content-files__el" v-for="(file, i) in el.files" :key="`file${i}`">
              <div class="files__el-left">
                <div v-if="file.type === 'image'" class="files-file" :style="{backgroundImage: `url(${file.url})`}"></div>
                <file-icon v-else :small="true" :type="file.type" />
              </div>
              <div class="files__el-right">
                <div class="files__el-right-name" >
                  <a v-if="file.url" :href="file.url" download>{{ _(file.displayName) }}</a>
                  <span v-else>{{ _(file.displayName) }}</span>
                </div>
                <div class="files__el-right-size" v-if="file.size">
                  <span>{{ sizeFiles(file.size) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <hm-empty v-if="!getVariant.length">
        <div class="hm-task-preview-no-data" v-html="this.noDataMessage"></div>
      </hm-empty>
    </div>
    
  </div>
</template>

<script>
import FileIcon from "../../icons/file-icon/index";
import HmEmpty from "@/components/helpers/hm-empty"

export default {
  components: { 
    FileIcon, 
    HmEmpty
  },
  props: {
    task: {
      type: [Array, Object],
      default: () => {}
    },
    variants: {
      type: [Array, Object],
      default: () => {}
    },
    noDataMessage: {
      type: String,
      default: 'Отсутствуют данные для отображения'
    }
  },
  data() {
    return {

    }
  },
  computed: {
    getVariant() {
      return this.variants;
    }
  },
  methods: {
    sizeFiles(size) {
      let form = 1024;
      if(size > 800) return Math.ceil(size / form) + 'kb';
      else if(size > 800000) return Math.ceil( size / form / form) + 'mb';
      else return size + 'b';
    },
  }
}
</script>

<style lang="scss">
.hm-task-preview {
  width: 100%;
  height: auto;
  display: flex;
  flex-direction: column;
  &__title {
    > span {
      font-weight: 300;
      font-size: 34px;
      line-height: 32px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
    margin-bottom: 31px;
  }
  &__body {
    padding: 26px;
    width: 100%;
    min-height: 83vh;
    background: #FFFFFF;
    box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
    border-radius: 4px;
    &-el {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.14);
      border-radius: 4px;
      &__title{
        background: rgba(212, 227, 251, 0.4);
        padding: 16px 26px;
        > span {
          font-weight: 500;
          font-size: 20px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #44556B;
        }
      }
      &__content{
        padding: 16px 26px;
        &-files {
          margin-top: 20px;
          display: flex;
          flex-wrap: wrap;
          &__el {
            min-height: 45px;
            display: flex;
            flex-wrap: nowrap;
            margin-right: 24px;
            .files__el-left {
              width: 40px;
              height: 100%;
              display: flex;
              justify-content: center;
              align-items: center;
              margin-right: 8px;
              .files-file {
                width: 40px;
                height: 100%;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                margin-right: 10px;
                text-decoration: none;
              }
            }
            .files__el-right {
              width: auto;
              height: 100%;
              display: flex;
              flex-direction: column;
              &-name {
                margin-top: 10px;
                > span, > a {
                  text-decoration: none;
                  font-weight: 500;
                  font-size: 14px;
                  line-height: 21px;
                  letter-spacing: 0.02em;
                  color: #131313;
                }
              }
              &-size {
                > span {
                  font-size: 16px;
                  line-height: 24px;
                  letter-spacing: 0.02em;
                  color: rgba(19, 19, 19, 0.7);
                }
              }
            }
          }
        }
      }
      &:not(:last-child) {
        margin-bottom: 16px;
      }
    }
  }
}

.hm-task-preview-no-data {
  text-align: center;
}
</style>
