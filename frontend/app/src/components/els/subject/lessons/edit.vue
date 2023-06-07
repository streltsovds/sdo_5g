<template>
  <div class="subject-lessons-edit">
    <draggable
        v-if="dataLessons.length || dataSections.length"
        class="subject-lessons-edit__draggable"
        v-model="dataLessons"
        @start="drag = true"
        @end="drag = false"
        :scroll-sensitivity="150"
        :force-fallback="true"
        group="lessons"
    >
      <manager-lesson
        v-for="(lesson, i) in dataLessons"
        :lesson="lesson"
        :key="`lesson-${i}`"
      />
    </draggable>

<!--    <p class="subject-lessons-edit__message" v-if="!dataLessons.length && dataSections.length">Перетащите занятие из раздела или создайте новое</p>-->

    <hm-empty v-if="!dataLessons.length && !dataSections.length" empty-type="full" :sub-label="generateURLCreate" />

    <sectionLessons
      @change="changeSectionLessons"
      v-for="section in dataSections"
      :deleteUrl="section.deleteUrl"
      :name="section.name"
      :key="section.section_id"
      :lessons="section.lessons"
      :id="section.section_id"
      :expanded="section.expanded"
    />

    <div class="dropdown-loader">
      <hm-file-downloader
              :url="generateURLDropdownLoader"
              :hash="hash"
              @dataRes="dataResParent"
              text="Или перетащите файлы сюда"
              placeholder='Будет автоматически создано занятие с типом "Информационный ресурс" на основе загруженного файла.<br>
      При загрузке файла HTML-сайт (.zip) будет создано занятие с типом "Информационный ресурс".<br>
      При загрузке файла в формате SCORM или TinCan/xAPI (.zip) будет создано занятие с типом "Учебный модуль".<br>
      При загрузке файлов формата TXT и XLSX, заполненных по специальному шаблону, будет создано занятие с типом "Тест"; пример файлов на странице импорта теста в Базе знаний.<br>'
      />
    </div>
  </div>
</template>
<script>


import ManagerLesson from "@/components/els/subject/lessons/managerLesson";
import HmFileDownloader from '@/components/media/hm-file-downloader/index'
import HmEmpty from "@/components/helpers/hm-empty"
import draggable from "vuedraggable"
import Axios from "axios"
import SectionLessons from "./section";
export default {
  components: {
    ManagerLesson,
    draggable,
    HmFileDownloader,
    HmEmpty,
    SectionLessons
  },
  props: {
    lessons: {
      type: Array,
      default: () => []
    },
    sections: {
      type: Array,
      default: () => []
    },
    subjectId: [Number,String],
    hash: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      dataLessons:[],
      dataSections:[],
      addNewLessons: false,
    }
  },
  computed: {
    generateURLDropdownLoader() {
      return `/storage/index/elfinder/subject_id/${this.subjectId}/subject/subject-lessons`;
    },
    generateURLCreate() {
      return 'План занятий в курсе не создан.' +
              ` <a href='/subject/lesson/create/subject_id/${this.subjectId}'>` +
              'Создать занятие' +
              '</a>';
    }
  },
  watch: {
    dataLessons(data) {
      if(!this.addNewLessons) {
        let newObj = {
          lessons: data.map(el => {return el.lessonId}),
          sections: this.dataSections.map(section => {
            const obj = {
              id: section.section_id,
              lessons: section.lessons.map(el => {return el.lessonId})
            }
            return obj;
          })
        }
        this.changeLessons({subject_id:this.subjectId, order: newObj})
      }
      this.addNewLessons  = false
    }
  },
  mounted() {
    this.dataLessons = this.lessons;
    this.dataSections = this.sections;
  },
  methods: {
    async changeLessons(data) {
      return Axios.post(`/subject/lessons/order`,data)
        .then((res) => res )
        .catch((err) => err )
    },
    dataResParent(data) {
      this.addNewLessons = true;
      this.dataLessons = this.dataLessons.concat(data)
    },
    changeSectionLessons() {
      let newObj = {
        lessons: this.dataLessons.map(el => {return el.lessonId}),
        sections: this.dataSections.map(section => {
          const obj = {
            id: section.section_id,
            lessons: section.lessons.map(el => {return el.lessonId})
          }
          return obj;
        })
      }
      this.changeLessons({subject_id:this.subjectId, order: newObj})
    }
  }
};
</script>
<style lang="scss">
  .subject-lessons-edit {
    position: relative;
    &__message {
      position: absolute;
      width: 80%;
      text-align: center;
      top: 50px;
      left: 50%;
      transform: translate(-50%, 0);
      border: 1px dashed #ccc;
      padding: 10px;
    }
    &__draggable {
      /*min-height: 150px;*/
    }
    > div:not(.dropdown-loader) {
      box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
    }
    .dropdown-loader {
      margin-top: 22px;
      height: 80px;
      display: flex
    }
    & .hm-file-downloader {
      background: rgba(153, 217, 189, 0.15);
      & span {
        color: #1f1f1f;
      }
      border-color: #1f1f1f;
    }
  }
</style>
