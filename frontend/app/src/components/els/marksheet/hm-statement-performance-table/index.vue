<template>
<div>
  <modal-comment :header-comment="_('Комментарий к результату')"
    v-if="flagComment"
    :comment="dataComment"
    @closeModal="closeModalParent"
  />
  <div class="statement-performance-selects">
    <v-text-field
      class="statement-performance-selects__select"
      v-model="dataFilters.currentPerson"
      @keydown.enter="changePersonsFilter"
      label="ФИО"
      outlined
    ></v-text-field>

    <v-select
      class="statement-performance-selects__select"
      :items="getGroupsSelectItems()"
      v-model="dataFilters.currentGroup"
      item-text='name'
      item-value="value"
      @change="changeGroupsFilter"
      label="Группа/Подгруппа"
      :clearable="true"
      outlined
    ></v-select>

    <!-- <hm-date-range-field
      class="statement-performance-selects__select-date"
    /> -->

  </div>
  <div class="statement-performance" :class="{'active-mark': flagMark }">
    <div class="statement-performance__header" style="display: flex;">
      <div class="statement-performance__header-fio" ref="headerFIO">
        <div>
          <v-checkbox class="statement-performance__users_check_all"
                      v-model="usersSelectAllCheckboxValue"
          />
          <span>{{ _('ФИО') }}</span>
        </div>
      </div>
      <div class="statement-performance__header-content">
        <div class="statement-performance__header-content-item" v-for="(el, i ) in sortedLessons" :key="i">
          <div class="stPer-icon-header" :style="{backgroundImage: `url(${el.icon})`}" />
          <div class="stPer-icon-text">
            <a v-if="el.resultUrl" :href="el.resultUrl">
              <span :title="el.title">{{ _(el.title) }}</span>
            </a>
            <span :title="el.title" v-else>{{ _(el.title) }}</span>
          </div>
        </div>
      </div>
      <div class="statement-performance__header-result">
        <div>
          <img src="/images/icons/diploma.svg">
          <span>{{ _('Итог') }}</span>
        </div>
      </div>
    </div>
    <div class="statement-performance__container">
      <div class="statement-performance__container-content__row" v-for="(row, i) in usersObjects" :key="i">
        <div class="statement-performance__container-fio__content">
          <div class="statement-performance__container-fio__content-el">
            <v-checkbox v-model="row.selectedUserCheckbox" />
            <hm-card-link
              :url="row.cardUrl"
              title="Карточка пользователя"
              rel="pcard"
              float=" "
            >
              <v-list-item-title class="testtestest">
                <v-list-item-avatar>
                  <svg-icon name="ListItemsInverted" color="#70889E" />
                </v-list-item-avatar>
              </v-list-item-title>
            </hm-card-link>
            <div class="dataUser">
              <span>{{ _(row.name) }}</span>
              <p
                v-if="row.studyGroups && row.studyGroups.length > 0"
                class="statement-performance__container-fio__groups"
                :title="groupsTitle(row.studyGroups)"
                :style="{maxWidth: getWidthGroupsTitle()}"
              >
                {{groupsTitle(row.studyGroups)}}
              </p>
              <!-- <div v-if="row.studyGroups && row.studyGroups.length > 0">
                <span v-for="(gr, grI) in row.studyGroups" :key="grI">{{ _(gr.name) }}</span>
              </div> -->
            </div>
          </div>
        </div>
        <div class="statement-performance__container-content__row">
          <div class="statement-performance__container-content__row-cell"
               :data-uesr-id="i"
               :data-cell-id="cell.id"
               v-for="(cell) in sortedLessons"
               :key="cell.id"
               :class="{
                 'emprt-lesson-in-user' : true,
                 'active-cell-row': activeCell === `${i}-${cell.id}`
               }"
               @click="activeRowCell(i, cell.id, typeMark(i, cell.id))"
          >
            <div v-if="getUserCommentEvent(i, cell.id)">
              <div class="statement-performance__container-content__row-cell__scale"
                   v-if="cell.scale === ScaleType.CONTINUOUS"
              >
                <!--                :TODO додумать как  Input без v-model-->
                <input :id="`inputFocus${activeCell}`"
                       v-if="activeCell === `${i}-${cell.id}`"
                       @input="inputtest($event,i, cell.id )"
                       type="text"
                >
                <span v-else-if="row.lessonsTotal[cell.id].mark != -1">{{ row.lessonsTotal[cell.id].mark }}</span>
              </div>
              <div class="statement-performance__container-content__row-cell__bin"
                   v-if="cell.scale === ScaleType.BINARY"
              >
                <svg-icon :color="Number(row.lessonsTotal[cell.id].mark) === 1 ? '#05C985' : '#DADADA'"
                          @click.native="ActiveMarkBin(i, cell.id,row.lessonsTotal[cell.id].mark)"
                          width="34"
                          height="25.5"
                          name="Checkmark"
                />
              <!--                <span>{{  row.lessonsTotal[cell.id].mark  }}</span>-->
              </div>
              <div class="statement-performance__container-content__row-cell__tryp"
                   v-if="cell.scale === ScaleType.TERNARY"
              >
                <svg-icon :color="Number(row.lessonsTotal[cell.id].mark) === 1 ? '#05C985' : '#DADADA'"
                          @click.native="ActiveMarkTri(i, cell.id,row.lessonsTotal[cell.id].mark, 1)"
                          width="24"
                          height="18"
                          name="Checkmark"
                />
                <svg-icon :color="Number(row.lessonsTotal[cell.id].mark) === 0 ? '#EE423D' : '#DADADA'"
                          @click.native="ActiveMarkTri(i, cell.id,row.lessonsTotal[cell.id].mark, 0)"
                          width="16"
                          height="18"
                          name="cross"
                />
              </div>
              <v-tooltip bottom>
                <template v-slot:activator="{ on: onTooltip }">
                  <div class="user-lesson-comments"
                       v-if="usersObjects[i].lessonsTotal[cell.id].comments && usersObjects[i].lessonsTotal[cell.id].comments !== ''"
                       v-on="onTooltip"
                       @click="addComment(i, cell.id)"
                  />
                  <div class="user-lesson-comments__no"
                       v-else
                       v-on="onTooltip"
                       @click="addComment(i, cell.id)"
                  />
                </template>
                <span v-if="usersObjects[i].lessonsTotal[cell.id].comments && usersObjects[i].lessonsTotal[cell.id].comments !== ''">{{ _(usersObjects[i].lessonsTotal[cell.id].comments) }}</span>
                <span v-else>{{ _('Ввести комментарий') }}</span>
              </v-tooltip>
            </div>
          </div>
        </div>

        <div class="statement-performance__container-result__content">
          <div class="statement-performance__container-result__content__row"
               @click="activeCell = `total-${i}`"
          >
            <div class="statement-performance__container-result__content__row-scale"
                 v-if="subject.scale_id === ScaleType.CONTINUOUS"
            >
              <input :id="`inputFocus${activeCell}`"
                     v-if="activeCell === `total-${i}`"
                     :value="getValueInput(row.subjectsTotal.mark)"
                     @input="inputTotal($event, i)"
                     :disabled="flagMark"
                     type="text"
              >
              <span v-else-if="row.subjectsTotal.mark != -1">{{ row.subjectsTotal.mark }}</span>
            </div>
            <div class="statement-performance__container-content__row-cell__bin"
                 v-if="subject.scale_id === ScaleType.BINARY"
            >
              <svg-icon :color="Number(row.subjectsTotal.mark) === 1 ? '#05C985' : '#DADADA'"
                        @click.native="saveTotal(i,row.subjectsTotal.mark,subject.scale_id )"
                        width="34"
                        height="25.5"
                        name="Checkmark"
              />
            </div>
            <div class="statement-performance__container-content__row-cell__tryp"
                 v-if="subject.scale_id === ScaleType.TERNARY"
            >
              <svg-icon :color="Number(row.subjectsTotal.mark) === 1 ? '#05C985' : '#DADADA'"
                        @click.native="saveTotal(i,row.subjectsTotal.mark,subject.scale_id,1 )"
                        width="24"
                        height="18"
                        name="Checkmark"
              />
              <svg-icon :color="Number(row.subjectsTotal.mark) === 0 ? '#EE423D' : '#DADADA'"
                        @click.native="saveTotal(i,row.subjectsTotal.mark,subject.scale_id,0 )"
                        width="16"
                        height="18"
                        name="cross"
              />
            </div>
            <v-tooltip v-if="row.subjectsTotal.comments && row.subjectsTotal.comments !== -1" bottom>
              <template v-slot:activator="{ on: onTooltip }">
                <div class="user-lesson-comments" v-on="onTooltip" />
              </template>
              <span>{{ row.subjectsTotal.comments }}</span>
            </v-tooltip>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hm-empty v-if="Object.values(usersObjects).length === 0" empty-type="full" />
  <template v-if="pages > 1">
    <v-pagination
            v-model="page"
            :length="pages"
            @input="changePage"
    ></v-pagination>
  </template>

  <div class="statement-performance__actions">
    <div class="selects">
      <v-select
        v-model="chosenAction"
        :items="userActions"
        :label="_('Действия с выбранными слушателями:')"
        dense
        outlined
      />
      <v-btn class="statement-performance__btn-action row-act"
             @click="actionWithSelectedUsers"
             :disabled="!actionWithSelectedUsersEnabled"
             :loading="isLoading"
             color="primary"
             style="
              text-transform: none;
              font-style: normal;
              font-weight: normal;
              font-size: 12px;
              line-height: 18px;
              padding: 9px 16px;
            "
      >
        {{ _pl("Выполнить для n строк", selectedUsersIds.length) }}
      </v-btn>
    </div>
    <div class="files-save">
      <a :href="urls.print" target="_blank"> <svg-icon name="Printer" color="#FFFFFF" /></a>
      <a :href="urls.excel" download=""> <svg-icon name="FileExcel" color="#FFFFFF" /></a>
      <a :href="urls.word" download=""> <svg-icon name="FileWord" color="#FFFFFF" /></a>
    </div>
  </div>
</div>
</template>

<script>
import axios from "axios";
import Vue from "vue";

import HmEmpty from "@/components/helpers/hm-empty"

import XlsxFileMixin from "@/utilities/mixins/XlsxFileMixin.js";
import DocFileMixin from "@/utilities/mixins/DocFileMixin.js";
import NativePrintMixin from "@/utilities/mixins/NativePrintMixin";
import globalActions from "@/store/modules/global/const/actions"

import ScaleType from "./lib/ScaleType.ts";

import hmGradeInput from "@/components/els/marksheet/hm-grade-input";
import HmDateRangeField from "@/components/forms/hm-date-range-field";

import GradeWithComment from "@/components/els/marksheet/hm-statement-performance-table/lib/GradeWithComment";
import SvgIcon from "../../../icons/svgIcon";
import HmCardLink from "@/components/els/hm-card-link";
import {
  clone as cloneShallow,
  cloneDeep,
  forOwn,
  get as getByPath,
  // mapValues,
  // pickBy,
  random,
  // transform,
  values,
} from "lodash"
import filter from "lodash/filter"
import ModalComment from "./modalComment";
import {decline} from "@/utilities";

export const ACTION_GRADUATE = 'ACTION_GRADUATE';
export const ACTION_TOTAL_SCORE = 'ACTION_TOTAL_SCORE';

import { objectToFormData } from 'object-to-formdata';
import {mapActions} from "vuex";

export default {
  name: 'HmStatementPerformanceTable',
  components: {
    ModalComment,
    SvgIcon,
    HmDateRangeField,
    HmCardLink,
    hmGradeInput,
    HmEmpty
  },
  mixins: [XlsxFileMixin, DocFileMixin, NativePrintMixin],
  props: {
    currentGroup: {
      type: [Array,Object, String,Number],
      default: null
    },
    currentPerson: {
      type: [Array,Object, String,Number],
      default: null
    },
    groups: {
      type: [Array, Object],
      default: () => []
    },
    persons: {
      type: [Array, Object],
      default: () => {}
    },
    subject: {
      type: Object,
      default: () => {}
    },
    trainedEndpoint: {
      type: String,
      default: () => ""
    },
    rateEndpoint: {
      type: String,
      default: () => ""
    },
    urls: {
      type: [ Array, Object ],
      default: () => {}
    },
    pages: {
      type: [String,Number],
      default: 1
    }
  },
  data() {
    return {
      page: 1,
      activeCell: '',
      chosenAction: "",
      chosenGroupName: null,
      dataComment: '',
      // dataPersons: this.persons,
      usersSelectAllCheckboxValue: false,
      // choseGroup: this.currentGroup || this.groups['0'],
      commentUser: {
        userID: '',
        lessonId:''
      },
      // события по юзерам
      /**
       * Пример
       * {
       *   selectedUser: false, - строка выбранная
       *   selectedUserCheckbox: false, - выбранный через checkbox
       *   name: '' - Имя пользователя
       * }
       */
      eventUsers: [],
      ////
      filterDateRangeString: null,
      flagComment: false,
      flagMark: false,
      inputData: '',
      inputId: '',
      inputNewValue: '',
      inputNewData: [],

      // copy
      // subjectsTotalCurrent: JSON.parse(JSON.stringify(this.subjectsTotal)),

      timeOut: null,

      /**
       * Инициализируются в
       * @see this.initComponent()
       **/
      usersObjects: {},
      ScaleType,
      tableData: null,
      sortedLessons: {},
      dataFilters: {}
    };
  },
  computed: {
    _groups() {
      let arr = [];
      for ( let el in this.groups ) {
        arr.push(this.groups[el])
      }
      return arr;
    },
    actionWithSelectedUsersEnabled() {
      return this.chosenAction && this.selectedUsersIds.length > 0;
    },
    userActions() {
      return filter([
        {
          text: this._("Перевести в прошедшие обучение"),
          value: ACTION_GRADUATE,
        },
        /**
         * Решили, что и при автоматическом выставлении оценок
         *   должна быть возможность вручную поправить оценку
         */
        // this.subject.auto_mark ? null :
        {
          text: this._("Выставить оценку за курс"),
          value: ACTION_TOTAL_SCORE,
        },
      ]);
    },
    // activeUsers() {
    //   let user_ids = [];
    //   for(let i in this.usersObjects) {
    //     if(this.usersObjects[i].selectedUserCheckbox) {
    //       user_ids.push(i)
    //     }
    //   }
    //   return user_ids;
    // },
    filterDateRange() {
      if (!this.filterDateRangeString) {
        return [];
      }
      return this.filterDateRangeString
        .split(",")
        .map(date => {
          return this.parseRuDate(date)
        });
    },

    filteredHeaders() {
      return this.filterTableHeadersByDate(this.headers, this.filterDateRange)
    },
    isLoading() {
      let { getters } = this.$store;

      if (!getters.componentIsLoading) {
        return false;
      }

      return getters.componentIsLoading(this.$options.name);
    },
    studyGroups() {
      let result = [];

      for (let user of this.users) {
        user.studyGroups.map(g => {
          result.push(g.name);
        });
      }
      return [...new Set(result)]
    },
    filteredUsers() {
      let users = cloneShallow(this.users); // copy

      let result = users.filter(user => {
        if (!this.chosenGroupName) {
          return true;
        }

        return user.studyGroups.find(
          g => g.name === this.chosenGroupName
        );
      });

      return result;
    },
    /** пользователи, выделенные галочками */
    selectedUsersIds() {
      let result = [];
      for(let i in this.usersObjects) {
        if(this.usersObjects[i].selectedUserCheckbox) {
          result.push(i)
        }
      }
      return result;
    },
  },
  watch: {
    inputData(data) {
      if(Number(data)) {
        if(data > 100) {
          this.inputData = 100
        } else if(data < 0) {
          this.inputData = 0
        }
      } else {
        this.inputData = data.slice(0, data.length-1)
      }
      this.saveAssesment()
    },
    activeCell(data) {
      this.inputData = '';
      this.inputId =  this.activeCell;
      setTimeout(() => {
        if(document.getElementById(`inputFocus${this.activeCell}`)) {
          document.getElementById(`inputFocus${this.activeCell}`).focus();
        }
      }, 0)
      // this.usersObjects.forEach(el =>  {
      //   if(Number(el.id) === Number(data.split('-')[0])) {
      //     this.$set(el.lessonsTotal[data.split('-')[1]], 'mark', el.lessonsTotal[data.split('-')[1]].mark)
      //   }
      // })
    },
    usersSelectAllCheckboxValue(selected) {
      this.usersSelectAll(selected);
    },
    eventUsers(data) {
      console.log('изменился')
    },
    /////
    dateTo: function() {
      this.filterTableHeadersByDate();
    },
    dateFrom: function() {
      this.filterTableHeadersByDate();
    }
  },
  created() {
    // this.setUsersObjects();
    let users = values(this.$props.persons);
    let schedules = values(cloneDeep(this.$props.schedules));
    schedules = schedules.map(schedule => {
      let type = { type: this.randomCellType() };
      Object.assign(schedule, type);
      return schedule;
    });

    let scores = this.$props.scores;

    if (!scores) {
      console.error('HmStatementPerformanceTable: scores not defined');
    }

    this.users = users.map(user => {

      let userId = user.MID || user.id;

      let userObject = {
        name: user.FirstName + " " + user.LastName + " " + user.Patronymic,
        id: userId,
        total: new GradeWithComment({
          value: scores ? scores[userId + "_total"].mark : null,
          comment: scores ? scores[userId + "_total"].comment : null,
        }),
        certificate: new GradeWithComment({
          value: scores ? scores[userId + "_certificate"] : null,
        }),
        studyGroups: user.studyGroups,
        schedules: {},
        filteredSchedules: []
      };

      for (let schedule of schedules) {
        let scheduleStatus = this.$props.scores[userId + "_" + schedule.SHEID]
          .V_STATUS;
        let scheduleComment = this.$props.scores[
          user.MID + "_" + schedule.SHEID
        ].comment;
        let scheduleId = schedule.SHEID;
        userObject.schedules[scheduleId] = {
          status: scheduleStatus === -1 ? "" : scheduleStatus,
          type: schedule.type,
          comment: scheduleComment,
          dialog: false,
          id: scheduleId
        };
      }

      forOwn(userObject.schedules, (userSchedule) => {
        let type = userSchedule.type;
        if (type === "binary" || type === "ternary") {
          let binaryValue = { value: this.randomBinaryValue() };
          Object.assign(userSchedule, binaryValue);
        } else {
          let continuousValue = { value: this.randomContiniousValue() };
          Object.assign(userSchedule, continuousValue);
        }
      });

      userObject.filteredSchedules = {...userObject.schedules};
      return userObject;
    });

    // this.filteredUsers = this.users;

    schedules.forEach(schedule => {
      let header = {
        cellDisplayType: "gradeInput",
        text: schedule.title,
        id: schedule.SHEID,
        beginTime: schedule.begin,
        // TODO fn
        value: 'schedules[' + schedule.SHEID + ']',
        endTime: schedule.end,
        timeType: schedule.timetype,
        sortable: false,
        align: "center"
      };
      this.headers.splice(1, 0, header);
    });
  },
  beforeMount() {
    this.getUsersEvents();
  },
  mounted() {
    this.getFilters();
    this.getUsersObjects(this.getDataUrl());
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert", "addAlert"]),
    ...mapActions("notifications", ["addSuccessNotification"]),
    groupsTitle(data) {
      const text = [];
      data.forEach(item => {
        text.push(item.name)
      });
      return text.join(', ');
    },
    getWidthGroupsTitle() {
      return `${this.$refs.headerFIO.offsetWidth - 100}px`;
    },
    getDataUrl() {
      const url = `/marksheet/index/get-marksheet-data/subject_id/${this.subject.subid}`
        + `/page/${this.page}`
        + `${this.dataFilters.currentGroup ? `/current_group/${this.dataFilters.currentGroup}` : ''}`
        + `${this.dataFilters.currentPerson ? `/search_user/${this.dataFilters.currentPerson}` : ''}`
        + `/`;

      return url;
    },
    getFilters() {
      this.dataFilters = {
        currentGroup: this.currentGroup,
        currentPerson: ""
      }
    },
    changePersonsFilter(e) {
      this.dataFilters.currentPerson = e.target.value;
      this.page = 1;
      this.getUsersObjects(this.getDataUrl())

    },
    changeGroupsFilter(val) {
      this.dataFilters.currentGroup = val;
      this.getUsersObjects(this.getDataUrl())
    },
    changePage(val) {
      this.page = val;
      this.getUsersObjects(this.getDataUrl());
    },
    getGroupsSelectItems() {
      const arrayItems = [];
      if(this.groups.groups) {
        Object.entries(this.groups.groups).forEach(([key, value]) => {
            const obj = {
                name: value,
                value: key,
                type: "group"
            };
            arrayItems.push(obj);
        });
      }
      if(this.groups.subgroups) {
        Object.entries(this.groups.subgroups).forEach(([key, value]) => {
            const obj = {
                name: value,
                value: key,
                type: "subgroup"
            };
            arrayItems.push(obj);
        });
      }
      return arrayItems;
    },
    async ajaxPostRequestWithProgress(url, params) {
      let { dispatch } = this.$store;

      let response = null;
      dispatch(globalActions.setLoadingOn, this.$options.name, { root: true });

      let formData = objectToFormData(params);

      try {
        response = await axios({
          url,
          method: 'post',
          data: formData,
        });
      } catch (e) {
        console.error(error);
      }

      dispatch(globalActions.setLoadingOff, this.$options.name, { root: true });
      return response;
    },
    /** TODO перенести subjectsTotalCurrent в usersObjects */
    userDeleteId(userId) {
      Vue.delete(this.usersObjects, userId);
      Vue.delete(this.usersObjects.subjectsTotal, userId)
    },
    usersSelectAll(select) {
      for(let i in this.usersObjects) {
        Vue.set(this.usersObjects[i], 'selectedUserCheckbox', !!select);
      }
    },
    closeModalParent(data) {
      data.save ? this.saveComment(data.comment) : this.flagComment = false;
    },
    saveComment(comment) {
      this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
      let formData = new FormData();
      const id = this.commentUser.userID.replace('_', '');
      formData.append('userId', id);
      formData.append('lessonId',this.commentUser.lessonId);
      formData.append('comment', comment);
      this.$axios.post(this.urls.setComment, formData)
        .then(res=> {
          if(res.data) {
            this.$set(this.usersObjects[`_${res.data.userId}`].lessonsTotal[res.data.lessonId], 'comments', res.data.comment);
          }
        })
        .catch(err=> console.warn(err))
        .finally(() => {
          this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
          this.flagComment = false;
        })
    },
    /**
     * добавление коммента, редактирование
     */
    addComment(userID, lessonId) {
      this.flagComment = true;
      this.dataComment = this.usersObjects[userID].lessonsTotal[lessonId].comments ? this.usersObjects[userID].lessonsTotal[lessonId].comments : '';
      this.commentUser.lessonId = lessonId;
      this.commentUser.userID = userID;
    },
    /**
     * метод обработчки ажатия кнопочки
     */
    inputtest(e, userID, lessonId) {
      // по условию проверяю на число
      if(!Number(e.target.value) && e.target.value != 0) {
        e.target.value = e.target.value.slice(0,e.target.value.length-1)
      } else {
        window.clearTimeout(this.timeOut);
        if(e.target.value > 100) e.target.value = 100;
        // if(e.target.value <= 0) e.target.value = 0;
        // else e.target.value = Number(e.target.value);
        this.timeOut = window.setTimeout(() => {
          this.saveMarkInServe(userID, lessonId, e.target.value );
        },1000)
      }
    },
    getValueInput(value) {
      if (this.flagMark) return this.inputNewValue;
      if(value >= 0) {
        return value;
      } else {
        return '';
      }
    },
    // Обработка итоговой оценки
    inputTotal(e, userID) {
      const id = userID.replace('_', '');
      this.inputNewValue = e.target.value;
      if(!Number(e.target.value) && e.target.value != 0) {
        e.target.value = e.target.value.slice(0,e.target.value.length-1)
      }
      if(e.target.value > 100) e.target.value = 100;
      // if(e.target.value <= 0) e.target.value = 0;
      window.clearTimeout(this.timeOut);
      this.timeOut = window.setTimeout(() => {
        let formData = new FormData();
        formData.append('lessonId','total');
        formData.append('userId', id);
        formData.append('score', e.target.value);
        let save = confirm(this._('Вы уверены, что хотите выставить оценку?'));
        if(save) {
          this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
          this.flagMark = true;
          this.$axios
            .post(this.urls.setScore, formData)
            .then(res=> {
              if(res.data) {
                this.$set(this.usersObjects[`_${res.data.userId}`].subjectsTotal, 'mark', Number(res.data.score));
              }
            })
            .catch(err=> console.log(err))
            .finally(() => {
              this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
              this.flagMark = false
              this.activeCell = '';
            })
        }
      },1000)
    },
    saveMarkInServe(idUser,idLesson, newMark) {
      const id = idUser.replace('_', '');
      this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
      this.flagMark = true;
      let formData = new FormData();
      formData.append('lessonId',idLesson);
      formData.append('userId', id );
      if(newMark === '') {
        formData.append('score', newMark);
      } else {
        formData.append('score', Number(newMark));
      }
      this.$axios.post(this.urls.setScore, formData)
        .then(res=> {
          if(res.data) {
            this.$set(this.usersObjects[`_${res.data.userId}`].lessonsTotal[res.data.lessonId ], 'mark', Number(res.data.score));
            this.activeCell = '';
          }
        })
        .catch(err=> console.warn(err))
        .finally(() => {
          this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
          this.flagMark = false;
        })
    },
    typeMark(userID, lessonId) {
      let newObj = {};
      newObj.mark = this.getUserCommentEvent(userID, lessonId).mark;

      switch (this.sortedLessons[`_${lessonId}`].scale) {
        case ScaleType.CONTINUOUS:
          newObj = Object.assign(newObj, this.typeScale());
          break;
        case ScaleType.BINARY:
          newObj = Object.assign(newObj, this.typeBinary());
          break;
        case ScaleType.TERNARY:
          newObj = Object.assign(newObj, this.typeTriphy());
          break;
      }
      return newObj;
    },
    typeScale(userID, lessonId){
      return {
        type: ScaleType.CONTINUOUS,
        preview: '0-100'
      }
    },
    typeBinary(userID, lessonId) {
      return {
        type:ScaleType.BINARY,
        preview: '-1/1'
      }
    },
    typeTriphy(userID, lessonId) {
      return {
        type: ScaleType.TERNARY,
        preview: '-1/0/1'
      }
    },
    ActiveMarkNumber() {
      this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
    },
    saveTotal(userId,mark, type, active = null) {
      const id = userId.replace('_', '');
      let formData = new FormData();
      let newMark = null;
      if(Number(type) === ScaleType.CONTINUOUS) newMark = mark;
      else if(Number(type) === ScaleType.BINARY) newMark = Number(mark) === 1 ? -1 : 1;
      else if(Number(type) === ScaleType.TERNARY) {
        if(Number(mark) === Number(active)) {
          newMark = -1;
        } else {
          newMark = active;
        }
      }
      formData.append('lessonId','total');
      formData.append('userId', id);
      formData.append('score', newMark);
      let save = confirm(this._('Вы уверены, что хотите выставить оценку?'));
      if(save) {
        this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
        this.flagMark = true;
        this.$axios
          .post(this.urls.setScore, formData)
          .then(res=> {
            if(res.data) {
              this.$set(this.usersObjects[`_${res.data.userId}`].subjectsTotal, 'mark', Number(res.data.score));
            }
          })
          .catch(err=> console.log(err))
          .finally(() => {
            this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
            this.flagMark = false
          })
      }
    },
    /**
     *Метод по бинарному оцениванию
     */
    ActiveMarkBin(idUser, idLesson, mark) {
      const id = idUser.replace('_', '');
      this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
      this.flagMark = true;
      let formData = new FormData();
      let newMark = Number(mark) === 1 ? -1 : 1;
      formData.append('lessonId',idLesson);
      formData.append('userId', id);
      formData.append('score', newMark);
      this.$axios.post(this.urls.setScore, formData)
        .then(res=> {
          if(res.data) {
            this.$set(this.usersObjects[`_${res.data.userId}`].lessonsTotal[res.data.lessonId ], 'mark', Number(res.data.score));
            this.activeCell = '';
          }
        })
        .catch(err=> console.warn(err)).finally(() => {
          this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
          this.flagMark = false
        })
    },
    ActiveMarkTri(idUser, idLesson, mark, active) {
      const id = idUser.replace('_', '');
      this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
      this.flagMark = true;
      let formData = new FormData();
      let newMark = null;
      if(Number(mark) === Number(active)) {
        newMark = -1;
      } else {
        newMark = active;
      }
      formData.append('lessonId',idLesson);
      formData.append('userId', id);
      formData.append('score', newMark);
      this.$axios.post(this.urls.setScore, formData)
        .then(res=> {
          if(res.data) {
            this.$set(this.usersObjects[`_${res.data.userId}`].lessonsTotal[res.data.lessonId ], 'mark', Number(res.data.score));
            this.activeCell = '';
          }
        }).catch(err=> console.log(err)).finally(() => {
          this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
          this.flagMark = false;
        })
    },

    test(value) {
    },
    getUsersObjects(url) {
      this.$store.dispatch(globalActions.setLoadingOn, 'statementPerformanceTable', { root: true });
      fetch(url, {
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
      })
        .then(res => res.json())
        .then(data => {
          this.tableData = data;
          this.setUsersObjects();
          this.setLessons();
          this.setPages();
        })
        .catch(error => console.error(error))
        .finally(() => {
          this.$store.dispatch(globalActions.setLoadingOff, 'statementPerformanceTable', { root: true });
        })
    },
    setLessons() {
      this.tableData.lessons.forEach((item) => {
        Vue.set(this.sortedLessons, `_${item.id}`, item);
      });
    },
    setUsersObjects() {
      this.usersObjects = {};
      this.tableData.personsRows.forEach((item) => {
        Vue.set(this.usersObjects, `_${item.personId}`, item);
      });
    },
    setPages() {
      Vue.set(this, 'pages', this.tableData.pages);
    },
    /**
     * сохранение оценки
     * */
    saveAssesment() {
      let formData = new FormData();
      formData.append(`lessonId`, this.inputId.split('-')[0]);
      formData.append(`userId`, this.inputId.split('-')[1]);
      formData.append(`score`, this.inputData);
      // console.warn(`меняю у - ${this.inputId}, данные - ${this.inputData}`);
      this.$axios.post(this.urls.setScore, formData)
        .then(res=> {
          if(res.data) {
            // for(let i =0, arr = this.usersObjects; i < arr.length; i++) {
            //   if(arr[i].id == res.data.lessonId) {
            //     arr[i].lessonsTotal[res.data.userId].mark = Number(res.data.score);
            //     break;
            //   }
            // }
          }
        })
        .catch(err => console.log(err))
    },
    /**
     * функция
     */
    getUserCommentEvent(idUser, idLesson) {
      if(this.usersObjects) {
        const user = this.usersObjects[idUser]
        if(user) {
          let userlessons = user.lessonsTotal;
          if(userlessons) {
            if(userlessons[idLesson]) {
              return userlessons[idLesson]
            } else {
              return null
            }
          }
        }
      }
    },
    /**
     * функция выбора ячейки ( активная )
     */
    activeRowCell(idUser, idLesson, type) {
      if(this.activeCell === `${idUser}-${idLesson}`) {
        return;
        // this.activeCell = ``
      } else {
        if(!this.getUserCommentEvent(idUser, idLesson)) {
          this.activeCell = ``
        } else {
          this.activeCell = `${idUser}-${idLesson}`
        }
      }
      if(type.type === 1) {
        setTimeout(() => {
          let mark = type.mark >= 0 ? type.mark : '';
          document.getElementById(`inputFocus${this.activeCell}`).value = mark
        })
      }
    },
    /**
     * метод инициализации всех пользователей и действий по ним
     */
    getUsersEvents() {
      // TODO закомментировал, т. к. нигде не найти testUsers

      // this.testUsersTT.forEach((el) => {
      //   this.eventUsers.push({
      //     name: el.name,
      //     selectedUser: false,
      //     selectedUserCheckbox: false
      //   })
      // })
    },
    //////
    getByPath,
    // "20.03.2019" -> "2019-03-20"
    parseRuDate(ruDate) {
      if (!ruDate) return null;

      const [day, month, year] = ruDate.split(".");
      return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
    },
    toggleAll() {
      if (this.selectedUsers.length) this.selectedUsers = [];
      else this.selectedUsers = this.filteredUsers.slice();

    },

    /*
      random* - временный код для генерации данных
      TODO - удалить, принимать значения с бекэнда
    */
    randomCellType() {
      let cellTypes = ['continuous', 'binary', 'ternary'];
      let index = cellTypes.length - 1;
      return cellTypes[random(index)];
    },
    randomBinaryValue() {
      return !!random();
    },
    randomContiniousValue() {
      if (random(1, 3) !== 1) {
        return "";
      }

      return random(2, 5);
    },

    filterTableHeadersByDate(headers, dateRange) {
      let filterDateBegin = dateRange[0];
      let filterDateEnd = dateRange[1];

      // let schedulesIDs = [];
      return headers.filter(header => {
        if (header.id === undefined) {
          // не событие, служебный заголовок
          return header;
        }

        let headerDateBegin = header.beginTime.split(" ")[0];
        let headerDateEnd = header.endTime.split(" ")[0];

        if (
          (filterDateBegin && filterDateBegin > headerDateEnd) ||
          (filterDateEnd && filterDateEnd < headerDateBegin)
        ) {
          return;
        }

        return header;
      });
    },
    async actionWithSelectedUsers() {
      const id = this.selectedUsersIds.map(item => {
        return item.replace('_', '');
      });
      let dataSave = {
        user_ids: id,
        subject_id: this.subject.subid
      };

      this.usersSelectAll(false);

      // /* TODO посылать выбранных юзеров по нужным эндпоинтам */

      let resultGraduate, resultTotalScore;

      const _prefix = 'HmStatementPerformanceTable.actionWithSelectedUsers():';

      switch (this.chosenAction) {
        case ACTION_GRADUATE: {
          resultGraduate = await this.ajaxPostRequestWithProgress(
            this.urls.graduateStudent,
            dataSave
          );

          let graduatedUsersIds = resultGraduate.data.assigned_user_ids;
          if (graduatedUsersIds) {
            for (let userId of graduatedUsersIds) {
              this.userDeleteId(`_${userId}`)
            }
          }
          break;
        }
        case ACTION_TOTAL_SCORE: {
          resultTotalScore = await this.ajaxPostRequestWithProgress(
            this.urls.setTotalScore,
            dataSave
          );
          this.requestResultDataShowMessages(resultTotalScore.data);

          let newTotalMarksByUserId = resultTotalScore.data.marks;
          if (newTotalMarksByUserId) {
            for (let [userId, newTotalMark] of Object.entries(newTotalMarksByUserId)) {
              this.$set(this.usersObjects[`_${userId}`].subjectsTotal, 'mark', newTotalMark);
            }
          }
          break;
        }
      }
    },
    requestResultDataShowMessages(resultData) {
      let { message, error } = resultData;

      if (message) {
        this.addSuccessNotification(message);
      }
      if (error) {
        this.addErrorAlert(error);
      }
    },
    printTable() {
      let usersTableData = this.createUsersTableData();
      this.createPrintWindow(usersTableData, "table");
    },
    downloadExcelFile() {
      let usersTableData = this.createUsersTableData();
      let blob = this.createXlsx(usersTableData, "table");
      this.downloadXlsx(blob);
    },
    downloadWordFile() {
      let usersTableData = this.createUsersTableData();
      let doc = this.createDoc(usersTableData, "table");
      this.downloadDoc(doc);
    },
    createUsersTableData() {
      let usersTableData = {
        headers: [],
        data: []
      };
      usersTableData.headers = this.filteredHeaders.map(header => header.text);
      usersTableData.data = this.filteredUsers.map(user => {
        let userData = [];
        userData.push(user.name);
        for (let schedule of user.schedules) {
          userData.push(schedule.value ? "1" : "0");
        }
        userData.push(user.total.value === -1 ? "" : user.total.value);
        userData.push(
          user.certificate.value === -1 ? "" : user.certificate.value
        );
        return userData;
      });
      return usersTableData;
    },
    // createSubjectLink(lessonId) {
    //   let splittedPathname = window.location.pathname.split("/");
    //   let origin = window.location.origin;
    //   let subjectLink = "";
    //   subjectLink =
    //     origin +
    //     "/lesson/result/index/lesson_id/" +
    //     lessonId +
    //     "/" +
    //     splittedPathname[4] +
    //     "/" +
    //     splittedPathname[5];
    //   return subjectLink;
    // },
    cellsData(headers, gridItem) {
      return headers.map(
        (header) => {
          let valuePath = header.value;
          let value = getByPath(gridItem, valuePath);

          return {
            // gridItem,
            header,
            headerId: header.id,
            value,
            valuePath,
            displayType: header.cellDisplayType,
          }
        },
      );
    },
    trimTitle(title) {
      let max = 15;
      if (title.length > max) {
        title = title.substring(0, max) + '...';
      }
      return title;
    }
  }
};
</script>

<style lang="scss" scoped>
.statement-performance__no-users {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
}
.statement-performance__actions {
  //padding: 33px 26px 0 26px;
  padding: 40px 0 0 0;
  display: flex;
  position: relative;
  justify-content: space-between;

  .statement-performance__btn-action {
    //background: #e6e6e6;
    border-radius: 4px;
    height: 36px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 16px;
    cursor: pointer;
    &.row-act {
      margin-right: 150px;
    }
    > span {
      font-size: 12px;
      line-height: 18px;
      letter-spacing: 0.15px;
      color: #666666;
    }
  }
  .selects {
    display: flex;
    flex-wrap: nowrap;
    > .v-input {
      width: 391px;
      margin-right: 26px;
    }
  }
  .files-save {
    background: #5181b8;
    border-radius: 6px;
    display: flex;
    flex-wrap: nowrap;
    padding: 11.5px 16px;
    height: 44px;
    //position: absolute;
    //right: 26px;

    > div:not(:last-child),
    > svg:not(:last-child),
    > a:not(:last-child) {
      margin-right: 16px;
      cursor: pointer;
      text-decoration: none;
    }
  }
}

.statement-performance-selects {
  display: flex;
  align-items: center;
  &__select {
    margin-right: 20px;
    max-width: 300px;
    &-date {
      margin-top: 0 !important;
      top: -20px;

    }
  }
}

.statement-performance {
  /* height: 66vh; */
  // overflow: auto;
  overflow-y: hidden;
  overflow-x: auto;
  position: relative;
  z-index: 0;
  min-height: 115px;
  // max-height: 80vh;
  margin-bottom: 15px;
  &::-webkit-scrollbar {
        width: 4px;
        height: 4px;
  }
  &::-webkit-scrollbar-thumb {
    background: #706e6e;
    border-radius: 4px;
  }
  &::-webkit-scrollbar-thumb:hover {
    background: #70889E;
  }
  &::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    border-radius: 4px;
  }
  &__header {
    display: flex;
    position: sticky;
    top: 0;
    z-index: 100;
    min-width: 100%;
    width: min-content;
    &-fio {
      width: 100%;
      height: 110px;
      background: white;
      border-top: 1px solid rgba(0, 0, 0, 0.12);
      border-bottom: 1px solid rgba(0, 0, 0, 0.12);
      border-right: 1px solid rgba(0, 0, 0, 0.12);
      position: sticky;
      display: flex;
      top: 0;
      z-index: 50000;
      left: 0;
      min-width: 320px;
      > div {
        width: 100%;
        height: 100%;
        background: rgba(37, 116, 207, 0.08);
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        padding-left: 20px;
        padding-bottom: 20px;
        > div {
          height: 24px !important;
          padding: 0 !important;
          margin: 0 !important;
          margin-right: 10px !important;
        }
      }
    }
    &-content {
      height: 110px;
      width: auto;
      display: flex;
      flex-wrap: nowrap;
      position: sticky;
      top: 0;
      background: white;
      border-top: 1px solid rgba(0, 0, 0, 0.12);
      border-bottom: 1px solid rgba(0, 0, 0, 0.12);
      z-index: 10;
      &-item {
        width: 110px;
        height: 100%;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(0, 0, 0, 0.12);
        padding-top: 18px;
        align-items: center;
        background: rgba(37, 116, 207, 0.08);
        .stPer-icon-header {
          background-position: center;
          background-repeat: no-repeat;
          background-size: contain;
          width: 40px;
          height: 40px;
          margin-bottom: 9px;
          display: flex;
          justify-content: center;
          align-items: center;
        }
        .stPer-icon-text {
          width: 100%;
          display: flex;
          padding: 0 4px;
          justify-content: center;
          box-sizing: border-box;
          > a {
            line-height: 12px;
            text-decoration: none;
            text-align: center;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-width: 100%;
            box-sizing: border-box;
          }
          > a > span,
          > span {
            font-size: 11px;
            line-height: 12px;
            letter-spacing: 0.15px;
            color: #000000;
            text-align: center;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-width: 100%;
            box-sizing: border-box;
          }
        }
      }
    }
    &-result {
      width: 100%;
      height: 110px;
      background-color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 10;
      position: sticky;
      right: 0;
      top: 0;
      min-width: 103px;
      width: 103px;
      img {
        width: 40px;
        margin-bottom: 10px;
      }
      > div {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: rgba(153, 217, 189, 0.5);
        > svg {
          margin-bottom: 14px;
        }
        > span {
          padding: 0 16px;
          background: #05c985;
          border-radius: 4px;
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #ffffff;
        }
      }
    }
  }
  &__load-more {
    display: flex;
    padding-top: 10px;
    width: 100%;
    align-items: center;
    justify-content: center;
  }
  &.active-mark:after {
    content: '';
    width: 100%;
    height: 100%;
    position: fixed;
    background: transparent;
    left: 0;
    top: 0;
    z-index: 1000;
  }
  &__filters {
    display: flex;
    align-items: center;

    /* margin-left: 77px; */
    > span {
      font-weight: 500;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #000000;
    }
    > div {
      max-width: 150px;
      margin-left: 16px;
    }
  }
  &__container {
    height: auto;
    flex-direction: column;

    /* overflow: auto; */
    display: flex;
    flex-wrap: nowrap;
    position: relative;
    z-index: 1;
    min-width: 100%;
    width: min-content;
    &-fio {
      width: 100%;
      min-width: 320px;
      height: 100%;
      position: sticky;
      z-index: 100;
      left: 0;
      top: 0;
      background: white;
      &__groups {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        font-size: 12px;
        line-height: 18px;
        max-width: 220px;
        margin-bottom: 0;
      }
      &__content {
        display: flex;
        flex-direction: column;
        width: 100%;
        position: sticky;
        left: 0;
        top: 0;
        min-width: 320px;
        width: 100%;
        background: white;
        z-index: 5000;
        &-el {
          padding: 0 0 0 20px;
          width: 100%;
          height: 5.5vh;
          min-height: 45px;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          border-bottom: 1px solid rgba(0, 0, 0, 0.12);
          border-right: 1px solid rgba(0, 0, 0, 0.12);

          /* > div:not(.hm-card-link):not(.dataUser) {
            margin-right: 10px !important;
          } */
          .hm-card-link {
            > a {
              > div {
                > div {
                  height: 36px !important;
                  width: 30px !important;
                  min-width: 25px !important;
                  margin: 0;
                  margin-right: 8.5px !important;
                }
              }
            }
          }
          .dataUser {
            > span {
              font-size: 11px;
              line-height: 12px;
              letter-spacing: 0.02em;
              color: #2574CF;
            }
            > div {
              > span {
                font-size: 12px;
                line-height: 18px;
                letter-spacing: 0.15px;
                color: #424242;
              }
            }
          }
        }
      }
    }
    &-content {
      width: auto;

      /* min-width: 2800px; */
      display: flex;
      flex-direction: column;

      &__row {
        display: flex;
        flex-wrap: nowrap;
        height: 5.5vh;
        min-height: 45px;
        &-cell {
          width: 110px;
          height: 100%;
          display: flex;
          justify-content: center;
          align-items: center;
          border-bottom: 1px solid rgba(0, 0, 0, 0.12);
          border-right: 1px solid rgba(0, 0, 0, 0.12);
          position: relative;
          > div {
            background-color: #fff;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            .statement-performance__container-content__row-cell__scale {
              > input,
              > input:hover,
              >  input:active,
              > input:focus {
                width: 50%;
                height: 50%;
                border: none;
                outline: none;
                text-align: center;
                font-size: 26px;
                line-height: 36px;
                letter-spacing: 0.02em;
                color: #3E4E6C;
              }
              > span {
                font-size: 26px;
                line-height: 36px;
                letter-spacing: 0.02em;
                color: #3E4E6C;
              }
            }
            .statement-performance__container-content__row-cell__scale,
            .statement-performance__container-content__row-cell__bin,
            .statement-performance__container-content__row-cell__tryp {
              width: 100%;
              height: 100%;
              display: flex;
              align-items: center;
              justify-content: center;
              > svg {
                cursor: pointer;
              }
              > svg:nth-child(2) {
                margin-left: 8px;
              }
            }
            > span {
              font-size: 26px;
              line-height: 36px;
              letter-spacing: 0.02em;
              color: #3e4e6c;
            }
            .mark-in-user {
              width: 100%;
              height: 100%;
              display: flex;
              justify-content: center;
              align-items: center;
              > input {
                width: 50%;
                height: 50%;
              }
            }
          }
        }
      }
    }

    &-result {
      width: 103px;
      min-width: 103px;
      height: 100%;

      /* background-color: rgba(153, 217, 189, 0.15); */
      background: white;
      position: sticky;
      z-index: 100;
      right: 0;
      top: 0;
      flex-direction: column;
      &__content {
        z-index: 5000;
        background: white;
        position: sticky;
        right: 0;
        top: 0;
        min-width: 103px;
        width: 103px;
        display: flex;
        flex-direction: column;
        &__row {
          display: flex;
          justify-content: center;
          align-items: center;
          height: 5.5vh;
          min-height: 45px;
          width: 103px;
          background: rgba(153, 217, 189, 0.15);
          border-bottom: 1px solid rgba(0, 0, 0, 0.12);
          position: relative;
          &-scale {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            > input {
              width: 50%;
              height: 50%;
              border: none;
              outline: none;
              text-align: center;
              font-size: 26px;
              line-height: 36px;
              letter-spacing: 0.02em;
              color: #3e4e6c;
            }
            > span {
              font-size: 26px;
              line-height: 36px;
              letter-spacing: 0.02em;
              color: #3e4e6c;
            }
          }
        }
      }
    }
  }
  .emprt-lesson-in-user {
    background: linear-gradient(-45deg, rgba(0, 0, 0, 0) 49.9%, #c5c5c5 49.9%, #c5c5c5 60%, rgba(0, 0, 0, 0) 60%), linear-gradient(-45deg, #c5c5c5 10%, rgba(0, 0, 0, 0) 10%);
    background-size: 0.5em 0.5em;
    background-color: #dddddd;
  }
  .user-lesson-comments {
    position: absolute;
    top: 0;
    right: 0;
    border: 6px solid transparent;
    border-top: 6px solid rgba(5, 201, 133);
    border-right: 6px solid rgba(5, 201, 133);
  }
  .user-lesson-comments__no {
    position: absolute;
    top: 0;
    right: 0;
    border: 6px solid transparent;
    border-top: 6px solid rgba(175, 192, 163, 0.7);
    border-right: 6px solid rgba(175, 192, 163, 0.7);
  }
  .active-cell-row {
    background-color: #ffe99d;
    border: 1px solid #ffe99d;
  }
}
@media(max-width: 1100px) {
  .statement-performance {
    &__header {
      &-content-item {
        width: 90px;
      }
    }
    &__container {
      &-fio {
        &__content {
          &-el {
            & .dataUser span {
              display: inline-block;
              font-size: 11px;
              line-height: 12px;
            }
            & .statement-performance__container-fio__groups {
              font-size: 11px;
              line-height: 12px;
            }
          }
        }
        &__groups {
          font-size: 10px;
        }
      }
      &-content {
        &__row-cell {
          width: 90px;
          & > div .statement-performance__container-content__row-cell__scale > span {
          font-size: 20px;
        }
        }
      }
      &-result {
        &__content__row-scale > span {
          font-size: 20px;
        }
      }
    }
  }
}
@media(max-height: 1100px) {
  .statement-performance {
    &__header {
      &-fio,
      &-content,
      &-result {
        height: 90px;
      }
    }
    &__container {
      &-fio {
        &__content {
          &-el {
            & .dataUser span {
              display: inline-block;
              font-size: 11px;
              line-height: 12px;
            }
            & .statement-performance__container-fio__groups {
              font-size: 11px;
              line-height: 12px;
            }
          }
        }
        &__groups {
          font-size: 10px;
        }
      }
      &-content {
        &__row-cell {
          & > div .statement-performance__container-content__row-cell__scale > span {
          font-size: 20px;
        }
        }
      }
      &-result {
        &__content__row-scale > span {
          font-size: 20px;
        }
      }
    }
  }
}
</style>
