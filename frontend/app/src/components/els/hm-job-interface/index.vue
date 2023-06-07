<template>
  <div class="hm-job-interface">
    <div class="hm-job-interface__task">
      <div class="hm-job-interface__task-top">
        <!--<div class="hm-job-interface__task-top__title" v-if="task.title">
          <span>{{ task.title }}</span>
        </div>-->
        <div class="hm-job-interface__task-top__from" v-if="task.created_by">
          <span>{{ task.created_by }}, дата редактирвания варианта задания: {{ task.created ? formatDate(task.created) : '' }}</span>
        </div>
      </div>
      <div class="hm-job-interface__task-bot" v-if="variant && variant.item">
        <div class="hm-job-interface__task-bot__description" v-if="variant.item.description">
          <span v-html="_(variant.item.description)"></span>
        </div>
        <div class="hm-job-interface__task-bot__files" v-if="variant && variant.files">
          <a class="hm-job-interface__task-bot__files-file" v-for="(file, i) in variant.files" :key="i" :href="file.url" download>
            <div v-if="file.type === 'image'"  class="files-file" :style="{backgroundImage: `url(${file.url})`}"></div>
            <div v-else class="files-type">
              <file-icon :small="true" :type="file.type" />
            </div>
            <div class="files-info">
              <div><span>{{ _(file.displayName) }}</span></div>
              <div><span>{{ sizeFiles(file.size) }}</span></div>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="hm-job-interface__correspondence">
      <div
        class="hm-job-interface__correspondence-message"
        v-for="(el, i ) in dataJob"
        :key="i"
        :style="{backgroundColor: getRole(el) === 'teacher' ? messageColoring[0] : messageColoring[1] }"
      >
        <div class="hm-job-interface__correspondence-message-icon">
          <svg-icon :name="getTypeMessage(el.type, 'icon')" :title-icon="false" :color="getTypeMessage(el.type, 'color','#4A90E2' )"/>
        </div>
        <div class="hm-job-interface__correspondence-message-data">
          <div class="hm-job-interface__correspondence-message-data-info">
            <span>{{ _( el[getRole(el)].name ) }} </span>
<!--            <span>{{ getRole(el) === 'teacher' ? 'Преподователь' : 'Ученик' }}</span>-->
            <span class="info-date">{{ formatDate(el.date) }}</span>
          </div>
          <div class="hm-job-interface__correspondence-message-data-message">
            <span>{{ _(el.message) }}</span>
          </div>
          <div class="hm-job-interface__correspondence-message-data-files">
            <a class="hm-job-interface__correspondence-message-data-files-file" v-for="(file, i) in el.files" :key="i" :href="file.url" download>
              <div v-if="file.type === 'image'"  class="files-file" :style="{backgroundImage: `url(${file.url})`}"></div>
              <div v-else class="files-type">
                <file-icon :small="true" :type="file.type" />
              </div>
              <div class="files-info">
                <div><span>{{ _(file.displayName) }}</span></div>
                <div><span>{{ sizeFiles(file.size) }}</span></div>
              </div>
            </a>
          </div>
        </div>
        <div class="hm-job-interface__correspondence-message__assessment" v-if="el.type === 'assessment'">
          <span>{{ el.mark }}</span>
        </div>
      </div>
    </div>
    <div class="hm-job-interface__message" v-if="chatIsActive">
      <div class="hm-job-interface__message-icon">
        <div>
          <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.648 1.52206C12.9964 1.47041 13.3509 1.44313 13.7058 1.44313C17.1764 1.44313 20 3.91171 20 6.94667C20 8.10873 19.5913 9.21892 18.8182 10.1585C18.6244 10.3937 18.5451 10.4727 18.5253 10.491C18.3831 10.6247 18.3069 10.8669 18.3244 10.9655L18.4033 11.4002V11.4006L18.7238 13.1677C18.7971 13.5722 18.5969 13.7525 18.5076 13.8115C18.4589 13.8435 18.3649 13.8909 18.2344 13.8909C18.1249 13.8909 17.9896 13.8573 17.8349 13.752L16.368 12.7554L15.7518 12.3366C15.6516 12.2682 15.428 12.2248 15.3104 12.2514L15.3074 12.2521C15.2407 12.2665 14.3927 12.45 13.7053 12.45C13.2049 12.45 12.7091 12.3961 12.2273 12.2946C10.9624 13.077 9.42 13.5385 7.75511 13.5385C6.89111 13.5385 5.81889 13.3032 5.77356 13.2932C5.60222 13.2547 5.302 13.3126 5.15667 13.4119L4.38867 13.9334L2.56111 15.1752C2.38511 15.2951 2.23333 15.3333 2.11156 15.3333C1.97178 15.3333 1.87156 15.2828 1.81978 15.249C1.72289 15.1851 1.50644 14.9885 1.58844 14.5362L1.98756 12.3348V12.3339L2.086 11.7917C2.11533 11.6291 2.00111 11.3013 1.80978 11.1218C1.79311 11.1059 1.69644 11.012 1.452 10.715C1.452 10.7148 1.452 10.7147 1.45196 10.7145C1.45193 10.7144 1.45187 10.7143 1.45178 10.7141C0.502 9.56013 0 8.19593 0 6.7698C0 3.03697 3.47889 0 7.75533 0C9.60889 0 11.3124 0.571529 12.648 1.52206ZM18.0435 9.97065C18.0526 9.96122 18.1184 9.89366 18.2732 9.7053C18.9397 8.89564 19.2921 7.94197 19.2923 6.94828C19.2923 4.30597 16.7859 2.15625 13.7054 2.15625C13.6448 2.15625 13.5845 2.15963 13.5242 2.16301C13.4941 2.1647 13.464 2.16639 13.4339 2.16765C14.721 3.37667 15.5103 4.99444 15.5103 6.77141C15.5103 8.71541 14.5648 10.4698 13.0557 11.7052C13.2708 11.7264 13.4874 11.7392 13.7054 11.7392C14.3076 11.7392 15.091 11.572 15.1545 11.5585C15.1569 11.558 15.1583 11.5577 15.1586 11.5576C15.4643 11.4899 15.8897 11.5719 16.1481 11.7481L16.7641 12.1669L17.9714 12.9871L17.707 11.5297V11.5292L17.6281 11.0941C17.5637 10.7366 17.7414 10.254 18.0423 9.97184C18.0423 9.97184 18.0427 9.97145 18.0435 9.97065Z" fill="#70889E"/>
          </svg>
        </div>
      </div>
      <div class="hm-job-interface__message-form">
        <div class="hm-job-interface__message-form__fileDownload">
          <input id="files_input" type="file" @change="previewFiles" multiple>
          <div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M4.73755 20.2087C2.38992 17.8536 2.43367 14.0559 4.79324 11.7094L13.2211 3.32814C15.0016 1.55736 17.8959 1.55721 19.6766 3.32814C21.4401 5.08181 21.4422 7.9113 19.6766 9.66704L12.33 16.9659C11.1305 18.1588 9.17065 18.1421 7.99145 16.927C6.8554 15.7562 6.89184 13.9007 8.04979 12.7491L13.8252 7.01337C14.0736 6.76673 14.4806 6.76251 14.7343 7.00396L15.6528 7.87814C15.9065 8.11962 15.9108 8.51532 15.6625 8.76196L9.88768 14.4971C9.68952 14.6942 9.6773 15.0217 9.86164 15.2117C10.0373 15.3927 10.3134 15.3957 10.492 15.2181L17.8386 7.91927C18.6266 7.1356 18.6266 5.8597 17.8381 5.0756C17.0671 4.30892 15.8309 4.30853 15.0596 5.0756L6.63173 13.4568C5.235 14.8459 5.21347 17.0943 6.58388 18.4691C7.95035 19.8399 10.1592 19.8416 11.5282 18.4802L18.4413 11.6053C18.6895 11.3584 19.0965 11.354 19.3504 11.5953L20.2695 12.4689C20.5234 12.7102 20.528 13.1059 20.2798 13.3527L13.3667 20.2276C10.973 22.608 7.10113 22.5798 4.73755 20.2087Z" fill="#979797"/>
            </svg>
          </div>
        </div>
        <div class="hm-job-interface__message-form__elForm">
          <div class="elForm-text">
            <textarea ref="textareaForm" :placeholder="_('Сообщение...')" v-model="textForm" v-resize="'none'" @input="testtest()"></textarea>
          </div>
          <div class="elForm-file">
            <div class="elForm-file-img" v-for="(el, i) in files" :key="i">
              <div class="elForm-file-img-left">
                <img v-if="el.type === 'image'" :id="`img-${i}`">
                <file-icon v-else :small="true" :type="el.type" />
              </div>
              <div class="elForm-file-img-right">
                <div class="elForm-file-img-right-name">
                  <span>{{ _(el.name) }}</span>
                </div>
                <div class="elForm-file-img-right-size">
                  <span>{{ _(sizeFiles(el.size)) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="hm-job-interface__message-form__actions">
          <modal-category
            :users="users"
            v-if="categoryModal"
            :modal-category="category"
            @category="categoryParent"
          />
          <modal-assesment v-if="AssesmentModal" @assCount="assCountParent" />
          <div class="hm-job-interface__message-form__actions-category modal-job" @mouseover="categoryHover = true" @mouseleave="categoryHover = false" @click="categoryModal = !categoryModal">
            <v-tooltip bottom>
              <template  v-slot:activator="{ on: onTooltip }">
                <span v-on="onTooltip">
                  <svg v-if="!iconCategory" class="modal-job" width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path class="modal-job" d="M16 0H2C0.9 0 0 0.9 0 2V16C0 17.1 0.9 18 2 18H6L9 21L12 18H16C17.1 18 18 17.1 18 16V2C18 0.9 17.1 0 16 0ZM10 16H8V14H10V16ZM12.1 8.3L11.2 9.2C10.4 9.9 10 10.5 10 12H8V11.5C8 10.4 8.4 9.4 9.2 8.7L10.4 7.4C10.8 7.1 11 6.6 11 6C11 4.9 10.1 4 9.00004 4C7.90004 4 7.00004 4.9 7.00004 6H5.00004C5.00004 3.8 6.80004 2 9.00004 2C11.2 2 13 3.8 13 6C13 6.9 12.6 7.7 12.1 8.3Z"
                    :fill="categoryHover ? '#4A90E2' : '#979797'"
              />
            </svg>
                  <svg-icon v-else :title-icon="false" class="modal-job" width="18" height="21" :name="iconCategory.icon" :color="iconCategory.color" :fill="categoryHover ? iconCategory.color : '#979797'" ref="iconUpdate" />
                </span>
              </template>
              <span>{{ _('Выберите категорию') }}</span>
            </v-tooltip>
          </div>
          <div class="hm-job-interface__message-form__actions-category modal-assesment" v-if="getFromUSer === 'teacher' && this.type === 'assessment'" @mouseover="AssesmentHover = true" @mouseleave="AssesmentHover = false" @click="AssesmentModal = !AssesmentModal">
            <v-tooltip bottom>
              <template  v-slot:activator="{ on: onTooltip }">
                <span v-on="onTooltip">
                  <svg v-if="!iconAssesment" class="modal-assesment" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path class="modal-assesment" fill-rule="evenodd" clip-rule="evenodd" d="M2 0H16C17.1 0 18 0.9 18 2V16C18 17.1 17.1 18 16 18H2C0.9 18 0 17.1 0 16V2C0 0.9 0.9 0 2 0ZM13 14C13.55 14 14 13.55 14 13V8C14 7.45 13.55 7 13 7C12.45 7 12 7.45 12 8V13C12 13.55 12.45 14 13 14ZM9 14C9.55 14 10 13.55 10 13V5C10 4.45 9.55 4 9 4C8.45 4 8 4.45 8 5V13C8 13.55 8.45 14 9 14ZM5 14C5.55 14 6 13.55 6 13V11C6 10.45 5.55 10 5 10C4.45 10 4 10.45 4 11V13C4 13.55 4.45 14 5 14Z"
                    :fill="AssesmentHover ? '#4A90E2' : '#979797'"
              />
            </svg>
                  <div v-else class="modal-assesment hm-job-interface__message-form__actions-category-assesment"><span class="modal-assesment">{{ iconAssesment }}</span></div>
                </span>
              </template>
              <span>{{ _('Поставить оценку') }}</span>
            </v-tooltip>
          </div>
          <div
            class="hm-job-interface__message-form__actions-start"
            :class="{disabled: !stateButton}"
            @click="setSaveMessage">
            <v-tooltip bottom>
              <template  v-slot:activator="{ on: onTooltip }">
                <span v-on="onTooltip">
                  <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M19.7366 2.03889L2.38553 12.047C1.70796 12.4362 1.79406 13.3794 2.46789 13.6638L6.44721 15.3331L17.2023 5.85648C17.4082 5.67308 17.7001 5.95379 17.5242 6.16713L8.50613 17.1521V20.165C8.50613 21.0483 9.57303 21.3963 10.0971 20.7563L12.4742 17.8632L17.1386 19.8169C17.6702 20.0415 18.2766 19.7084 18.374 19.1357L21.0693 2.96709C21.1966 2.21105 20.3842 1.66461 19.7366 2.03889Z" fill="black"/>
                  <mask id="mask0" mask-type="alpha" maskUnits="userSpaceOnUse" x="1" y="1" width="21" height="21">
                    <path d="M19.7366 2.03889L2.38553 12.047C1.70796 12.4362 1.79406 13.3794 2.46789 13.6638L6.44721 15.3331L17.2023 5.85648C17.4082 5.67308 17.7001 5.95379 17.5242 6.16713L8.50613 17.1521V20.165C8.50613 21.0483 9.57303 21.3963 10.0971 20.7563L12.4742 17.8632L17.1386 19.8169C17.6702 20.0415 18.2766 19.7084 18.374 19.1357L21.0693 2.96709C21.1966 2.21105 20.3842 1.66461 19.7366 2.03889Z" fill="white"/>
                  </mask>
                  <g mask="url(#mask0)">
                    <rect width="23" height="23"/>
                  </g>
                </svg>
                </span>
              </template>
                <span>{{ _('Отправить сообщение') }}</span>
            </v-tooltip>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import ModalCategory from "./comonentsJob/modalCategory";
import ModalAssesment from "./comonentsJob/modalAssesment";
import FileIcon from "../../icons/file-icon/index";
export default {
  components: {FileIcon, ModalAssesment, ModalCategory, SvgIcon},
  props: {
    lessonId: {
      type: [Number,String],
      default: ''
    },
    userId: {
      type: [Number, String],
      default: ''
    },
    isEndUser: {
      type: [String,Number, Array,Object, Boolean],
      default: ''
    },
    lesson: {
      type: [Object, Array],
      default: () => {}
    },
    variant: {
      type: [Object, Array],
      default: () => {}
    },
    task: {
      type: [Object, Array],
      default: () => {}
    },
  },
  data() {
    return {
      url: '/task/conversation/get-conversations/',
      dataJob: null,
      textForm: '', // тип сообщения
      chatIsActive: true, // активна ли переписка
      files: [],
      types: ['Выдано задание','Вопрос тьютор','Ответ тьютора','Решение на проверку','Требования на доработку','Выставлена оценка','Не выполнено'],
      type: this.isEndUser ? 'question' : 'answer',
      categoryHover: false,
      AssesmentHover: false,
      categoryModal: false,
      AssesmentModal: false,
      assesmentCount: null,
      users: 'user', // флаг отвечает кто зашел, пока что так как пользователь
      messageColoring: ['rgba(250,243,216,0.4)','rgba(212,227,251,0.4)'],
      stateButton: true,
      category: [
        {
          title:'Задать вопрос',
          name:'question',
          access: ['user'],
          icon: 'Question',
          color:'#4A90E2'
        },
        {
          title:'Простое сообщение',
          name:'message',
          access: ['user'],
          icon: 'MessageFloor',
          color:'#FFC850'
        },
        {
          title:'Поставить оценку',
          name:'assessment',
          access: ['manager'],
          icon:'Jackdaw',
          color:'#05C985'
        },
        {
          title:'Ответ преподавателя',
          name:'answer',
          access: ['manager'],
          icon:'EducationFill',
          color:'#39B24A'
        },
        {
          title:'требования на доработку',
          name:'requirements',
          access: ['manager'],
          icon:'requirements',
          color:'#FF8364'
        },
        {
          title:'Решение на проверку',
          name:'to_prove',
          access: ['user'],
          icon:'Category',
          color:'#FF7474'
        },
      ],
      iconAssesment: null,
      iconCategory: null,
    }
  },
  watch: {
    iconCategory(data) {
      setTimeout(() => {
        for(let i = 0, el = this.$refs.iconUpdate.$el.children; i < el.length; i++) {
          el[i].classList += 'modal-job';
        }
      }, 300)
    },
    categoryModal(data) {
      if(data) document.addEventListener('click', this.categoryModalActive);
    },
    AssesmentModal(data) {
      if(data) document.addEventListener('click', this.assesmentModalActive);
    },
    dataJob(data) {
      this.searchIsActive()
    }
  },
  computed: {
    urlSave() {
      return `/task/conversation/add-conversation/lesson_id/${this.lessonId}/user_id/${this.userId}`
    },
    getFromUSer() {
      return this.isEndUser === 1 ? 'user' : 'teacher'
    }
  },
  methods: {
    searchIsActive() {
      let active = false;
      this.dataJob.map(el => {
        if(el.mark) {
          this.chatIsActive = false;
        }
      })
    },
    testtest($event){
      if(this.textForm.length === 0) {
        event.target.style.height = 36 + 'px';
      } else {
        const padding = event.target.offsetHeight - event.target.clientHeight;
        event.target.style.height = event.target.scrollHeight + padding + 'px';
      }

    },
    sizeFiles(size) {
      let form = 1024;
      if(size > 800) return Math.ceil(size / form) + 'kb';
      else if(size > 800000) return Math.ceil( size / form / form) + 'mb';
      else return size + 'b';
    },
    // метод по отображению иконок в сообщении
    getTypeMessage(type, elName, standard = 'Question') {
      let name = standard;
      this.category.find(el => {
        if(el.name === type) {
          name = el[elName];
        }
      });
      return name;
    },
    getRole(el) {
      if(el.teacher) {
        return 'teacher';
      }
      return 'user';
    },
    categoryParent(data) {
      this.type = data;
      this.iconCategory = this.category.find(el => el.name === data);
      if(this.type !== 'assessment') {
        this.assesmentCount = null;
        this.iconAssesment = null;
      }
    },
    assCountParent(data) {
      this.assesmentCount = data;
      this.iconAssesment = data;
      this.type = 'assessment';
      this.iconCategory = this.category.find(el => el.name === this.type)
      this.AssesmentModal = false;
    },
    urlGenerate() {
      return `/task/conversation/get-conversations/lesson_id/${this.lessonId}/user_id/${this.userId}`
    },
    getData() {
      this.$axios
        .get(this.urlGenerate())
        .then(res=> {
          this.dataJob = res.data.conversations;
        })
        .catch(err=> console.log(err))
    },
    setSaveMessage() {
      if(!this.stateButton) return;

      if(this.type === 'assessment' && !this.assesmentCount) {
        alert('Нужно выставить оценку');
        return false;
      }

      if (!(this.files.length > 0 || this.textForm !== '' || this.assesmentCount)) {
        alert('Пустое сообщение нельзя отправлять');
        return false;
      }

        let options = new FormData();
        options.append('message', this.textForm);
        if(this.assesmentCount) {
          options.append('score', this.assesmentCount);
          options.append('type', 'assessment');
        } else {
          options.append('type', this.type);
        }
        for(let i in this.files) {
          if(this.files[i].file.name && this.files[i].file.type) {
            options.append('file[]', this.files[i].file)
          }
        }
        this.stateButton = false;
        this.$axios({
          method: 'post',
          url:this.urlSave,
          data: options,
          headers: {'Content-Type': 'multipart/form-data'}
        })
          .then(res=> {
            if(res && res.data && res.status === 200) {
              this.dataJob.push(res.data.conversation)
            }
          })
          .catch(err=> console.log(err))
          .finally(() => {
            this.type = 'question';
            this.iconAssesment = null;
            this.iconCategory = null;
            this.textForm = '';
            this.files = [];
            this.$refs.textareaForm.style.height = '36px';
            this.stateButton = true;
          })

    },
    previewFiles(event) {
      this.files = [];
      for(let i =0; i <  event.target.files.length; i++) {
        this.files.push({file: event.target.files[i], name: event.target.files[i].name, size:event.target.files[i].size, type:event.target.files[i].type.split('/')[0]})
      }
      for(let i =0; i <  event.target.files.length; i++) {
        this.readUrl(this.files[i].file, i)
      }
      document.getElementById('files_input').value = ''// очистка инпута
    },
    // метод по представлению данных в формате base64
    readUrl(input, i) {
      if (input) {
        let reader = new FileReader();
        reader.onload = function (e) {
          document.getElementById(`img-${i}`).setAttribute('src', e.target.result);
        };
        reader.readAsDataURL(input)
      }
    },
    categoryModalActive(e) {
      if(!e.target.classList.contains('modal-job')) {
        document.removeEventListener('click', this.categoryModalActive);
        this.categoryModal = false;
      }
    },
    assesmentModalActive(e) {
      if(!e.target.classList.contains('modal-assesment')) {
        document.removeEventListener('click', this.assesmentModalActive);
        this.AssesmentModal = false;
      }
    },
    /**
     * метод форматирования размера
     * @data  - размер в байтах
     * @param - dj xnj gthtdjlbv
     */
    formatSize(data, param = 'kb') {
      switch (param) {
        case 'mb':
          return (Number(data) / 1024 / 1024).toFixed(2) + 'mb';
        default :
          return (Number(data) / 1024).toFixed(2)  + 'kb';
      }
    },
    formatDate(data) {
      return data.split(" ")[0].split("-").reverse().join(".") + " " + data.split(" ")[1].split(".")[0];
    }
  },
  mounted() {
    this.getData();
    this.users = this.isEndUser === 1 ? 'user' : 'manager';
  }

}
</script>

<style lang="scss">
  .hm-job-interface__message-form__actions-start {
    cursor: pointer;
    & rect {
      fill: #979797;
    }
    &:hover {
      & rect {
        fill: rgb(37, 116, 207);
      }
    }
  }
  .hm-job-interface__message-form__actions-start.disabled {
    opacity: 0.5;
    &:hover {
      & rect {
        fill: #979797;
      }
    }
  }
  .hm-job-interface {
    width: 100%;
    height: 100%;

    &__task {
      width: 100%;
      height: auto;
      background-color: rgba(212, 227, 251, 0.4);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.14);
      border-radius: 4px;
      margin-bottom: 52px;
      &-top {
        width: 100%;
        /*height: 91px;*/
        padding: 16px 26px;
        &__title {
          margin-bottom: 16px;
          > span {
            font-weight: 500;
            font-size: 20px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #44556B;
          }
        }
        &__from {
          > span {
            font-weight: 500;
            font-size: 14px;
            line-height: 21px;
            letter-spacing: 0.02em;
            color: #70889E;
          }
        }
      }
      &-bot {
        width: 100%;
        min-height: 57px;
        background: white;
        border-radius: 0 0 4px 4px;
        display: flex;
        flex-direction: column;
        padding: 0 26px 17px 26px;
        &__description {
          width: 100%;
          height: auto;
          padding: 16px 0 17px 0;
          > span {
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
        }
        &__files {
          display: flex;
          > div:not(:last-child) {
            margin-right: 8px;
          }
          &-file {
            height: 40px;
            display: flex;
            flex-wrap: nowrap;
            margin-right: 10px;
            .files-file {
              width: 40px;
              height: 100%;
              background-size: cover;
              background-position: center;
              background-repeat: no-repeat;
              margin-right: 10px;
              text-decoration: none;
            }
            .files-type {
              display: flex;
              width: 40px;
              height: 100%;
              justify-content: center;
              align-items: center;
            }
            .files-info {
              display: flex;
              flex-direction: column;
              align-items: flex-start;
              justify-content: flex-end;
              div {
                display: flex;
                > span {
                  font-size: 12px;
                  line-height: 18px;
                  letter-spacing: 0.15px;
                  color: #131313;
                }
              }
            }
          }
        }
      }
    }

    &__correspondence {
      width: 100%;
      height: auto;
      display: flex;
      flex-direction: column;
      margin-bottom: 74px;
      &-message {
        width: 100%;
        height: auto;
        min-height: 88px;
        display: flex;
        flex-wrap: nowrap;
        border-radius: 10px;
        padding: 20px;
        position: relative;
        &:not(:last-child) {
          margin-bottom: 10px;
        }
        &-icon {
          width: 48px;
          height: 48px;
          display: flex;
          justify-content: center;
          align-items: center;
          background: #FFFFFF;
          /*border: 2.5px solid rgba(81, 129, 184, 0.5);*/
          border-radius: 50%;
        }
        &-data {
          width: calc(70% - 48px);
          height: auto;
          display: flex;
          flex-direction: column;
          padding-left: 16px;
          &-info {
            margin-bottom: 3px;
            > span {
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #131313;
            }
            > span:nth-child(1) {
              font-weight: 500;
            }
            .info-date {
              margin-left: 50px;
              color: rgba(112, 136, 158, 0.7);
              mix-blend-mode: normal;
            }
          }
          &-message {
            margin-bottom: 12px;
            > span {
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #131313;
            }
          }
          &-files {
            display: flex;
            > div:not(:last-child) {
              margin-right: 8px;
            }
            &-file {
              height: 40px;
              display: flex;
              flex-wrap: nowrap;
              margin-right: 10px;
              .files-file {
                width: 40px;
                height: 100%;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                margin-right: 10px;
                text-decoration: none;
              }
              .files-type {
                display: flex;
                width: 40px;
                height: 100%;
                justify-content: center;
                align-items: center;
              }
              .files-info {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: flex-end;
                div {
                  display: flex;
                  > span {
                    font-size: 12px;
                    line-height: 18px;
                    letter-spacing: 0.15px;
                    color: #131313;
                  }
                }
              }
            }
          }
        }
        &__assessment {
          position: absolute;
          right: 0;
          top: 0;
          width: 81px;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: center;
          background: rgba(81, 129, 184, 0.3);
          border-radius: 0 10px 10px 0;
          > span {
            font-weight: bold;
            font-size: 36px;
            line-height: 48px;
            letter-spacing: 0.02em;
            color: #44556B;
          }
        }
      }
    }

    &__message {
      width: 100%;
      min-height:44px;
      display: flex;
      flex-wrap: nowrap;
      border-radius: 10px;
      padding: 20px;
      background-color: #fff;
      &-icon {
        width: 66px;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        > div {
          width: 48px;
          height: 48px;
          border-radius: 50%;
          display: flex;
          justify-content: center;
          align-items: center;
          background: #D4E3FB;
        }
      }
      &-form {
        width: calc(100% - 66px);
        height: auto;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        flex-wrap: nowrap;
        background: #F5F5F5;
        border-radius: 4px;
        &__fileDownload {
          width: 40px;
          height: 100%;
          position: relative;
          > div {
            width: 100%;
            height: 44px;
            background: #DADADA;
            border-radius: 4px 0 0 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
          }
          > input {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
          }
        }
        &__elForm {
          width: calc(100% - 190px);
          height: 100%;
          padding: 0 0 0 0;
          margin-left: 11px;
          box-sizing: border-box;
          position: relative;
          display: flex;
          flex-direction: column;
          .elForm-text {
            display: flex;
            position: relative;
            > textarea {
                width: 100%;
                height: 36px;
                padding-top: 12px;
                &:focus, &:active, &:hover {
                  border: none;
                  outline: none;
                }
            }
            .hm-job-interface__message-form__elForm-text {
              font-style: normal;
              font-weight: 300;
              font-size: 16px;
              line-height: 24px;
              letter-spacing: 0.02em;
              color: #131313;
            }
            > input {
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              opacity: 0;
            }
            .hm-job-interface__runner {
              animation: runerInterfacveForm .9s ease-in-out infinite;
            }
          }
          .elForm-file {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            height: auto;
            margin-top: 12px;
            &-img {
              display: flex;
              flex-wrap: nowrap;
              height: auto;
              margin: 0 40px 20px 0;
              &-left {
                width: 40px;
                height: 40px;
                margin-right: 10px;
                > img {
                  width: 100%;
                  height: 100%;
                }
              }
              &-right {
                display: flex;
                flex-direction: column;
                max-width: 100px;
                > div {
                  display: flex;
                  justify-content:flex-start;
                  align-items: center;

                }
              }
            }
          }
        }
        &__actions {
          width: 140px;
          display: flex;
          justify-content: flex-end;
          align-items: flex-start;
          margin-top: 11px;
          position: relative;
          > div:not(.modal__category__job):not(.modal-asses) {
            cursor: pointer;
            width: calc(100% / 3);
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
          }
        }
      }
    }
    .hm-job-interface__message-form__actions-category-assesment {
      padding: 2px 6px;
      background: #D4E3FB;
      border-radius: 2px;
      box-sizing: border-box;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      > span {
        font-weight: 500;
        font-size: 16px;
        line-height: 20px;
        letter-spacing: 0.02em;
        color: #1E1E1E;
      }
      &:hover {
        background: #FDE1D9;
        border: 0.6px solid #4A90E2;
      }
    }

    @keyframes runerInterfacveForm {
      0%{opacity: 1}
      50%{opacity: 0}
      100%{opacity: 1}
    }

  }
</style>
