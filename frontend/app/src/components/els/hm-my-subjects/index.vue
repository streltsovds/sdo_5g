<template>
  <div id="hmMySubjects" ref="hmMySubjects" :style="activeStyle">
    <div class="switch-position d-flex align-center pt-2"
         style="justify-content: flex-end; padding-right: 8px; position: absolute; top: 0; right: 28px;"
    >
      <div
        style="height: 100%; width: auto; display: flex; justify-content: center; align-items: center"
      >
        <hm-switch-checkmark
          v-model="showActual"
          :label="_('Показать все')"
          :title="_('Показать все курсы, включая заявки и завершенные')"
          @change="handleShowAll"
          label-left-side
        />
      </div>
    </div>
    <div class="hm-my-subjects">
      <template v-if="sortedSubjectsDataAll && sortedSubjectsDataAll.length">
        <template v-for="(subject,index) in sortedSubjectsDataAll">
          <hm-my-subjects-card :subject="subject" :key="index" />
        </template>
      </template>
      <template v-else>
        <hm-empty>Нет данных для отображения</hm-empty>
      </template>
    </div>
  </div>
</template>

<script>
import HmMySubjectsCard from "./partials/card.vue";
import HmSwitchCheckmark from "@/components/controls/hm-switch-checkmark";
import Axios from 'axios'
import HmEmpty from "../../helpers/hm-empty/index";

export default {
  name: "HmMySubjects",
  components: {HmEmpty, HmSwitchCheckmark, HmMySubjectsCard },
  props: {
    subjectsData: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      showActual: this.$root.view.showAll === 'true',
      subjectsDataAll: [],
      activeStyle: {}
    };
  },
  computed: {
    showAll() {
      return this.$root.view.showAll === 'true'
    },
    sortedSubjectsDataAll() {
      return (this.subjectsDataAll !== undefined && this.subjectsDataAll.length) ? [...this.subjectsDataAll].sort((a,b) => a.subjectTitle - b.subjectTitle) : [];
    },
  },
  mounted() {
    this.showActual = this.showAll
    this.subjectsDataAll = this.subjectsData
    this.styleCherkmark()
  },
  methods: {
    handleShowAll() {
      Axios.get(`/subject/my?showAll=${!this.showActual}&switcher=list`)
        .then(res=> {
          this.subjectsDataAll = res.data.subjectUsers
        })
        .catch(err=> console.log(err))
    },
    styleCherkmark() {
      let DOM = this.$refs.hmMySubjects.parentNode.parentNode.children;
      if(DOM) {
        for(let i =0; i < DOM.length; i++) {
          if(DOM[i].classList.contains('layout-content-header')) {
            this.activeStyle = {};
            break;
          } else {
            this.activeStyle = {marginTop: `35px`};
          }
        }
      }
    },
  }
};
</script>

<style lang="scss">
#hmMySubjects {
  .hm-my-subject-card {
    border-radius: 4px !important;
  }
  .v-list-item {
    padding: 0 !important;
    .v-avatar {
      width: 30px !important;
      height: 30px !important;
      margin: 0 !important;
      > img {
        top: 0 !important;
      }
    }
  }
}
.v-list.hm-mysubject-list {
  padding: 0!important;
  > div {
    .hm-card-link {
      > a {
        > div {
          > div {
            margin-right: 0!important;
            margin-left: 0!important;
          }
        }
      }
    }
  }
}
@media(max-width: 1024px) {
  #hmMySubjects {
    .hm-my-subject-card {
      border-radius: 0 !important;
    }
  }
  .hm-my-subjects {
    display: flex;
    flex-wrap: wrap;
  }
}
@media(max-width: 959px) {
  .switch-position {
    top: -11px !important;
    right: 8px !important;
  }
}
@media(max-width: 820px) {
  .hm-my-subjects {
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
  }
}
</style>
