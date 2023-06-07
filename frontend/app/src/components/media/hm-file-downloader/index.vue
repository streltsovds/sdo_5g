<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on }">
      <div class="hm-file-downloader"
           v-on="on"
           v-cloak
           @drop.prevent="addFile"
           @dragover.prevent="fileAboveElement"
           @dragleave.prevent="fileAboveElementNone"
           :style="fileAboveElementFlag ? style : {}">
        <div class="hm-file-downloader__placeholder" v-if="!downloader">
          <span v-if="text">{{ text }}</span>
          <div class="hm-file-downloader__placeholder-info" v-if="infoMessage.length > 0">
            <div class="hm-file-downloader__placeholder-info">
              <span >{{ messageType.error > 0 ? `файлов не загружено ${messageType.error} ;` : '' }}   {{  messageType.info > 0 ? `файлов загружено ${messageType.info}` : ''  }}</span>
            </div>
          </div>
        </div>
        <div class="hm-file-downloader__placeholder" v-else>
          <div class="placeholder-animation-download"></div>
          <div class="hm-file-downloader__placeholder-title">
            <span>{{ textDownloader }} {{ textNameFile }}</span>
          </div>
        </div>
      </div>
    </template>
    <span v-html="placeholder"></span>
  </v-tooltip>
</template>

<script>
import Axios from 'axios'
export default {
  props: {
    placeholder: {
      type: String,
      default: ''
    },
    text: {
      type: String,
      default:''
    },
    url: {
      type: String,
      default: '',
    },
    hash: {
      type: String,
      default: ''
    },
    subject: {
      type: [String,Array,Object],
      default: ''
    },
    subjectId: {
      type: [String,Array,Object],
      default: ''
    }
  },
  data() {
    return {
      files: [],
      downloader: false, // флаг для отображения процесса загрузки
      fileAboveElementFlag: false, //флаг для отображения когда файл над элементом
      textDownloader: 'загрузка',
      infoMessage: []
    }
  },
  computed: {
    // styleMessageInfo() {
    //   if(this.infoMessage.type === `error`) return {'color': 'rgba(255,83,68,0.79)'}
    //   else if(this.infoMessage.type === `info`) return {'color': 'rgba(122,147,255,0.75)'}
    // },
    textNameFile() {
      return this.files.length === 1 ? `файла - ${this.files[0].name}` : 'файлов'
    },
    uploadDisabled() {
      return this.files.length === 0
    },
    style() {
      return {border: `border: 2px dashed red;`, background: 'gray'}
    },
    messageType() {
        let type = {'error':0, 'info': 0};
        this.infoMessage.forEach(el => {
            el.type === 'error' ? type.error++ : type.info++
        })
        return type;

    }
  },
  watch: {
    files(data) {
      if(data.length > 0) {
        this.upload()
      }
    },
  },
  methods: {
    fileAboveElement() {
      if(!this.fileAboveElementFlag) {
        this.fileAboveElementFlag = true
      }
    },
    fileAboveElementNone() {
      if(this.fileAboveElementFlag) {
        this.fileAboveElementFlag = false
      }
    },
    addFile(e) {
      let droppedFiles = e.dataTransfer.files;
      if (!droppedFiles) return;
      ([...droppedFiles]).forEach(f => {
        this.files.push(f);
      });
    },
    upload: function () {
      this.fileAboveElementFlag = !this.fileAboveElementFlag
      this.downloader = !this.downloader;
      this.infoMessage = []
      let formData = new FormData();
      formData.append('reqId', ( +new Date()).toString(16) + Math.floor(1000 * Math.random()).toString(16));
      formData.append('cmd', 'upload');
      formData.append('target', this.hash);
      this.files.forEach((f, x) => {
        formData.append('upload[]', f);
      });
      formData.append('mtime[]', new Date().getTime());
      formData.append('ts',  Math.round((new Date()).getTime() / 1000));
      formData.append('upload_path[]', this.hash);
      formData.append('dropWith', 0);
      Axios.post(this.url, formData)
        .then(res=> {
          if(res.data.errorData) {
            for (let err in res.data.errorData){
              this.infoMessage.push({type:'error', text:`файл ${err} не загрузился`})
            }
          } else if(res.data.error) {
              this.infoMessage.push({type:'error', text:`какие то проблемы`})
          }
          this.files = [];
          this.downloader = !this.downloader;
          if(res.data.extraMaterials && res.data.extraMaterials.length > 0 ) {
            res.data.extraMaterials.forEach(info => {
              this.infoMessage.push({type:'info', text:`файл ${info.filename} загрузился`})
            });
            console.log('зашел в res.data.extraMaterials && res.data.extraMaterials.length > 0 ? отправляю ', res.data.extraMaterials)
            this.$emit('dataRes',res.data.extraMaterials)
          }
          else if( res.data.lessons && res.data.lessons.length > 0) {
            res.data.lessons.forEach(info => {
              this.infoMessage.push({type:'info', text:`файл ${info.lessonTitle} загрузился`})
            });
              console.log('res.data.lessons && res.data.lessons.length > 0 отправляю ', res.data.extraMaterials)
            this.$emit('dataRes',res.data.lessons)
          }
        })
        .catch(err=> {
          console.log(err);
          this.files = [];
        })

    }
  },

}
</script>

<style lang="scss">
  .hm-file-downloader {
    width: 100%;
    height: auto;
    min-height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px dashed #ccc;
    border-radius: 4px;
    position: relative;
    cursor: pointer;
    padding: 8px 0;
    &__placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      > span {
        font-weight: 300;
        font-size: 16px;
        color: #bbb;
      }
      &-info {
        margin-top: 3px;
        height: auto;
        display: flex;
        flex-wrap: wrap;
        > span {
          font-size: 12px;
        }
        > div {
          display: flex;
          > span {
            font-size: 12px;
          }
          &:not(:last-child) {
            margin-right: 5px;
          }
        }
      }

      &-title {
        margin-top: 5px;
        > span {
          font-size: 12px;
        }
      }
    }

    .placeholder-animation-download {
      width: 2vh;
      height: 2vh;
      border: 1px solid #5f65ff;
      border-bottom-color: transparent;
      border-radius: 50%;
      animation: dowloadFile .4s linear infinite;
    }
    @keyframes dowloadFile {
      0%{
        transform: rotate(0deg) scale(1);
      }
      50% {
        transform: rotate(180deg) scale(1.1);
      }
      100%{
        transform: rotate(360deg) scale(1);
      }
    }
  }
</style>
