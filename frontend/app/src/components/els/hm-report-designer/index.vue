<template>
  <div class="hm-report-designer" ref="container">
    <div
      v-if="statusModal === 'saving'"
      class="hm-report-designer__modal hm-report-designer__modal_loading"
    >
      <p>Сохранение</p>
    </div>
    <div
      v-if="statusModal === 'saved'"
      class="hm-report-designer__modal hm-report-designer__modal_saved"
    >
      <icons type="ok" />
      <p>Шаблон сохранен</p>
    </div>
    <div
      v-if="statusModal === 'error'"
      class="hm-report-designer__modal hm-report-designer__modal_error"
    >
      <p>При загрузке данных произошла ошибка</p>
    </div>
    <div class="hm-report-designer__wrapper">
      <div
        class="hm-report-designer__categories"
        v-for="(category, index) in headers"
        :key="index"
      >
        <h3 class="hm-report-designer__categories-title">{{category.title}}</h3>
        <category-items :category="category.fields"/>
      </div>
    </div>
    <div class="hm-report-designer__table-wrapper">
      <table class="hm-report-designer__table">
        <p
          v-if="headersInTable.length === 0"
          class="hm-report-designer__table-nodata"
        >Перетащите столбец в таблицу</p>
        <thead class="hm-report-designer__table-head">
          <draggable :group="{ name: 'people', put: true }" v-model="headersInTable" tag="tr">
            <th
              class="hm-report-designer__table-head-item"
              :class="{hiddenColumn: header.options.hiden == 1}"
              v-for="(header, index) in headersInTable"
              :key="index"
              scope="col"
            >
              <div class="hm-report-designer__table-buttons-wrapper">
                <v-menu left nudge-right="50" :close-on-content-click="false" v-model="header.modalStatus">
                  <template v-slot:activator="{ on, attrs }">
                    <button v-bind="attrs" v-on="on" @click="openModal(header)" class="hm-report-designer__table-button-open-modal">
                      <icons type="modal" />
                    </button>
                  </template>
                  <modal-filters :key="header.title" :data="header" @getDataTable="getTableData" />
                </v-menu>
                <button @click="activationInput(header, index)" class="hm-report-designer__table-button-open-modal">
                  <icons type="pencil" />
                </button>
                <button
                  class="hm-report-designer__table-button-close"
                  @click="deleteColumn(header)"
                ></button>
              </div>
              <span
                v-show="!header.input"
              >{{ header.options.title }}</span>
              <input
                v-show="header.input"
                class="hm-report-designer__table-input"
                type="text"
                :ref="'title-' + index"
                @keydown.enter="changeName($event, header)"
                @blur="changeName($event, header)"
                @change="changeName($event, header)"
                :value="header.options.title"
              />
              <!-- <textarea v-model="header.options.title" @change="changeName($event, header)"></textarea> -->
            </th>
          </draggable>
        </thead>
        <tbody class="hm-report-designer__table-body">
          <div v-if="pagination" class="hm-report-designer__info">
            <div class="hm-report-designer__info-container">
              <p class="hm-report-designer__info-container-title">Предварительный просмотр</p>
              <p v-if="pagination !== '0'" class="hm-report-designer__info-container-count">Всего страниц в отчете: {{Math.ceil(totalRecords / pagination)}}</p>
            </div>
          </div>
          <tr v-for="item in list" :key="item.title">
            <td :style="{height: list.length >= 10 ? '48px' : 'auto !important'}" v-for="(header, index) in headersInTable" :key="index">
              <span v-if="header.options.hiden == 0" v-html="formattingText(item[header.field.split('.')[2]])"></span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import CategoryItems from "./components/categoryItems";
import ModalFilters from "./components/modalFilters";
import Icons from "./components/icons"
export default {
  components: {
    draggable,
    CategoryItems,
    ModalFilters,
    Icons
  },
  props: ["reportFieldsInTable", "reportFields", "reportDomain", "reportId"],
  data() {
    return {
      allFields: null,
      headers: {},
      headersInTable: [],
      list: [],
      dragging: false,
      domain: this.reportDomain,
      statusModal: false,
      firstLoading: true,
      pagination: null,
      totalRecords: null
    };
  },
  mounted() {
    this.getAllFields();
    this.sortingFields();
  },
  watch: {
    headersInTable: function () {
      this.getTableData();
    }
  },
  methods: {
    formattingText(text) {
      if(text) {
        const tempEl = document.createElement("div");
        tempEl.innerHTML = text;
        return tempEl.textContent || "";
      }
    },
    getTableData() {
      if(this.headersInTable.length <= 0) return;
      const data = JSON.stringify(this.headersInTable);
      if(!this.firstLoading) this.statusModal = "saving";
      this.$refs.container.classList.add('hm-report-designer_loading');
      fetch(`/report/generator/grid/report_id/${this.reportId}`, {
        method: 'POST',
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: data
      }).then(res => res.json())
        .then(data => {
          this.list = data.data;
          this.pagination = data.tableSettings.pagination;
          this.totalRecords = data.tableSettings.totalRecords;
          if(!this.firstLoading) this.statusModal = "saved";
          else this.firstLoading = false;
          this.$refs.container.classList.remove('hm-report-designer_loading');
          setTimeout(() => this.statusModal = null, 2000)
        })
        .catch((error) => {
          this.statusModal = "error";
          this.pagination = "0";
          this.$refs.container.classList.remove('hm-report-designer_loading');
          setTimeout(() => this.statusModal = null, 2000)
        })

    },
    getAllFields() {
      const cloneReportFields = JSON.parse(JSON.stringify(this.reportFields));
      const newObj = {};
      for (let key in cloneReportFields) {
        const arrayFields = [];
        Object.entries(cloneReportFields[key].fields).map((field) => {
          field[1]["options"] = {
            hiden: 0,
            input: 0,
            title: field[1].title,
          };
          field[1]["field"] = `${this.reportDomain}.${key}.${field[0]}`;
          field[1]["modalStatus"] = false;
          field[1]["input"] = false;
          field[1]["category"] = key;
          arrayFields.push(field[1])
        });
        cloneReportFields[key].fields = arrayFields;
        newObj[key] = cloneReportFields[key];
      }
      this.allFields = newObj;
    },
    sortingFields() {
      const cloneReportFieldsInTable = JSON.parse(JSON.stringify(this.reportFieldsInTable));
      const arrayFieldsInTable = [];
      this.headers = JSON.parse(JSON.stringify(this.allFields));
      cloneReportFieldsInTable.forEach((item) => {
        const category = item.field.split('.')[1];
        this.headers[category].fields.forEach((field) => {
          const title = item.title.split(';')[0];
          if(field.title === title) {
            // Добавляем столбцы в таблицу
            field.options = item.options;
            arrayFieldsInTable.push(field);
            // Удаляем добавленные в таблицу столбцы из шапки
            const index = this.headers[category].fields.indexOf(field);
            this.headers[category].fields.splice(index, 1)
          }
        })
      });
      this.headersInTable = arrayFieldsInTable;
    },
    deleteColumn(item) {
      this.headers[item.category].fields.push(item);
      const indexItem = this.headersInTable.indexOf(item);
      this.headersInTable.splice(indexItem, 1)
    },
    changeName(e, item) {
      this.headersInTable.forEach(element => {
        if(item === element) {
          element.options.title = e.target.value;
          element.input = false;
        }
      });
      this.getTableData();
    },
    activationInput(item, index) {
      item.input = true;
      this.$nextTick(() => {
        this.$refs['title-' + index][0].focus();
      });
    },
    openModal(item) {
      this.headersInTable.forEach(element => {
        if(item === element) {
          element.modalStatus = !element.modalStatus;
        }
      });
    },
  }
};
</script>
<style lang="scss">
.hm-report-designer {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin-top: 30px;
  position: relative;
  &_loading {
    &::before {
      content: "";
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 10;
    }
  }
  &__modal {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    // width: 220px;
    position: fixed;
    top: 135px;
    left: 50%;
    z-index: 100;
    transform: translate(-50%, 0);
    padding: 14px;
    &_loading {
      background-color: #C4C4C4;
    }
    &_saved {
      background-color: #6FCF97;
      & p {
        margin-left: 12px;
      }
    }
    &_error {
      background-color: #E31F28;
    }
    & p {
      font-family: Roboto;
      font-style: normal;
      font-weight: normal;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #FFFFFF;
      margin-bottom: 0 !important;
    }
  }
  &__wrapper {
    display: flex;
    width: 100%;
    max-height: 271px;
    overflow: auto;
    padding-bottom: 20px;
    padding-right: 5px;
    box-sizing: border-box;
    &::-webkit-scrollbar {
      width: 4px;
      height: 4px;
    }
    &::-webkit-scrollbar-thumb:hover {
      background: #70889E;
    }
    &::-webkit-scrollbar-track {
      -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
      border-radius: 4px;
    }
    &::-webkit-scrollbar-thumb {
      background-color: #706e6e;
      border-radius: 4px;
    }
  }
  &__categories {
    display: flex;
    flex-direction: column;
    margin-right: 25px;
    min-width: 209px;
    &:last-child {
      margin-right: 0;
    }
    &-title {
      font-style: normal;
      font-weight: 500;
      font-size: 16px;
      line-height: 20px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
      min-height: 40px;
    }
  }
  &__info {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;

    &-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: calc(100% - 100px);
      max-width: 1200px;
      background: rgba(115, 124, 132, 0.07);
      border-radius: 12.8088px;
      text-align: center;
      &-title {
        font-family: Roboto;
        font-style: normal;
        font-weight: 600;
        font-size: 73px;
        line-height: 98px;
        letter-spacing: 0.02em;
        color: rgba(170, 177, 186, 0.7);
        margin-bottom: 15px;
      }
      &-count {
        font-family: Roboto;
        font-style: normal;
        font-weight: normal;
        font-size: 61px;
        line-height: 73px;
        letter-spacing: 0.02em;
        color: rgba(170, 177, 186, 0.7);
      }
    }
  }
  &__table {
    margin-top: 25px !important;
    border-collapse: collapse;
    min-height: 500px;
    position: relative;
    width: 100% !important;
    &-wrapper {
      overflow: auto;
      max-width: 100%;
      width: 100%;
    }
    &-body {
      position: relative;
      border: 1px solid #d4e3fb;
      min-height: 440px;
      height: 100%;
    }
    &-input {
      padding: 6px 4px;
      background: #FFFFFF !important;
      box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.06);
      font-weight: 300;
      font-size: 14px;
      line-height: 14px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
    &-nodata {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid #d4e3fb;
      border-bottom: none;
      box-sizing: border-box;
      width: 100%;
      height: 59px;
    }
    &-buttons-wrapper {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      position: absolute;
      top: 5px;
      right: 5px;
      width: calc(100% - 10px);
      & button {
        margin-right: 10px;
        &:last-child {
          margin-right: 0;
        }
      }
    }
    &-button {
      &-close {
        width: 15px;
        height: 15px;
        position: relative;
        &::before,
        &::after {
          content: "";
          position: absolute;
          top: 50%;
          left: 50%;
          width: 10px;
          height: 1px;
          border-radius: 2px;
          background-color: #70889E;
        }
        &::before {
          transform: translate(-50%, -50%) rotate(-45deg);
        }
        &::after {
          transform: translate(-50%, -50%) rotate(45deg);
        }
        &:hover {
          opacity: 0.5;
        }
      }
      &-open-modal {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 15px;
        height: 15px;
        &:hover {
          opacity: 0.5;
        }
      }
    }
    &-head {
      height: 59px;
    }
    &-head-item {
      position: relative;
      background: #EDF4FC;
      border: 1px solid #D4E3FB;
      box-sizing: border-box;
      font-weight: 500 !important;
      font-size: 14px !important;
      line-height: 18px;
      text-align: center !important;
      letter-spacing: 0.02em;
      color: #424242;
      padding: 20px !important;
      min-height: 86px;

      cursor: grab;
      &:active {
        cursor: grabbing;
      }

      &.hiddenColumn {
        background: #AAB1BA;
        border-color: #AAB1BA;
      }
    }
    & tr {
      background-color: #ffffff;
      border: none !important;
    }
    & tr:nth-child(2n) {
      background-color: #F5F5F5;
    }
    & td {
      border-right: 1px solid #D4E3FB;
      padding: 16px;
      &:last-child {

border-right: 0;
      }
    }
  }
}
</style>
