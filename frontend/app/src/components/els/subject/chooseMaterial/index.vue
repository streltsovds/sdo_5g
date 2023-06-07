<template>
  <div class="choose-material">
    <v-radio-group v-model="radios" :name="strId" :mandatory="false">
      <div class="choose-material__el" v-for="(el, key) in dataChoose" :key="key">
        <v-radio :value="`${el.id}-${el.type}`" />
        <file-icon :type="getFileIconType(el)" small />
        <span>{{ el.title }}</span>
        <v-tooltip v-if="el.viewUrl" bottom>
          <v-btn
            slot="activator"
            :href="el.viewUrl"
            @click.stop
            text
            icon
            color="primary"
          >
            <svg-icon
              name="openNew"
              color="#bbb"
              style="margin-righ: 0px; width: 18px"
              title="Просмотр материала"
            />
          </v-btn>
          <span>Просмотр материала</span>
        </v-tooltip>

      </div>
    </v-radio-group>
  </div>
</template>

<script>
import FileIcon from "@/components/icons/file-icon/index";
import SvgIcon from "@/components/icons/svgIcon";

export default {
  components: {FileIcon, SvgIcon},
  props: {
    dataChoose: {
      type: Array,
      default: () => []
    },
    id: {
      type: String,
      default:''
    }
  },
  data() {
    return {
      radios: ''
    }
  },
  computed: {
    strId() {
      return this.id.replace(/\s+/g,'')
    }
  },
  mounted() {

  },
  methods: {
    getFileIconType(el){
      let type;

      if(el.filetype){
        if(el.filetype === 'unknown'){
          type = el.subtype;
        }else{
          type = el.filetype
        }
      }else{
        if(el.type === 'course'){
          type = el.subtype;
        }else{
          type = el.type;
        }
        
      }

      return type;
    }
  }
}
</script>

<style lang="scss">
#fieldset-materialstab {
  box-shadow: none!important;
}
.choose-material {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  > .v-input {
    padding: 0!important;
    margin: 0!important;
    .v-input__control {
      width: 100%!important;
      .v-input__slot {
        margin-bottom: 0!important;
        > div {
          .choose-material__el {
            width: 100%;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            &:not(:last-child) {
              margin-bottom: 16px;
            }
            > .v-radio {
              margin-bottom: 0!important;
              margin-right: 19px !important;
              > div {
                margin-right: 0!important;
              }
            }
            .file-icon {
              margin-right: 12px;
            }
            > span {
              font-weight: normal;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #000000;

            }
          }
        }
      }
    }
  }
}
</style>
