<template>
    <div class="hm-report-modal" v-if="data.modalStatus">
        <button @click="closeModal" class="hm-report-modal__button-close"></button>
        <div v-if="data.function" class="hm-report-modal__filter">
            <div class="hm-report-modal__filter-wrapper">
                <p class="hm-report-modal__filter-title">Преобразования</p>
                <button class="hm-report-modal__filter-button-drop" @click="dropFunction">Сбросить</button>
            </div>
            <v-radio-group v-model="activeFunction" @change="changeFunction">
                <v-radio
                    v-for="(item, key) in data.function"
                    :key="key"
                    :label="item.title"
                    :value="key"
                ></v-radio>
            </v-radio-group>
        </div>
        <div v-if="data.aggregation" class="hm-report-modal__filter">
            <div class="hm-report-modal__filter-wrapper">
                <p class="hm-report-modal__filter-title">Вычисления</p>
                <button class="hm-report-modal__filter-button-drop" @click="dropAggregation">Сбросить</button>
            </div>
            <v-radio-group v-model="activeAggregation" @change="changeAggregation">
                <v-radio
                    v-for="(item, index) in data.aggregation"
                    :key="index"
                    :label="getLabelAggregation(item)"
                    :value="item"
                ></v-radio>
            </v-radio-group>
        </div>
        <div class="hm-report-modal__filter">
            <div class="hm-report-modal__filter-wrapper">
                <p class="hm-report-modal__filter-title">Фильтры</p>
            </div>
            <input
                v-if="data.type === 'integer'"
                class="hm-report-modal__filter-input"
                placeholder="Целое число"
                v-model="valueFilter"
                @change="changeFilter"
            />
            <input
                v-if="data.type === 'double'"
                class="hm-report-modal__filter-input"
                placeholder="вещественное число"
                v-model="valueFilter"
                @change="changeFilter"
            />
            <input
                v-if="data.type === 'string' || (data.type === 'date' && data.options.function)"
                class="hm-report-modal__filter-input"
                placeholder="маска строки"
                v-model="valueFilter"
                @change="changeFilter"
            />
            <input
                v-else
                class="hm-report-modal__filter-input"
                placeholder="тип неопределён"
                v-model="valueFilter"
                @change="changeFilter"
            />
        </div>
        <div class="hm-report-modal__filter">
            <div class="hm-report-modal__filter-wrapper">
                <p class="hm-report-modal__filter-title">Скрыть</p>
            </div>
            <button @click="changeVisible" class="hm-report-modal__filter-button">
                <span v-if="data.options.hiden == 0" class="hm-report-modal__filter-button-content">
                    <icons style="height: 16px;" type="visible"/>
                    <p>Поле является видимым</p>
                </span>
                <span v-else class="hm-report-modal__filter-button-content">
                    <icons style="height: 16px;" type="hidden"/>
                    <p>Поле является скрытым</p>
                </span>
            </button>
        </div>
        <div class="hm-report-modal__filter">
            <div class="hm-report-modal__filter-wrapper">
                <p class="hm-report-modal__filter-title">Входные параметры</p>
            </div>
            <button @click="changeInput" class="hm-report-modal__filter-button">
                <span v-if="data.options.input == 1" class="hm-report-modal__filter-button-content">
                    <icons type="input"/>
                    <p>Поле является входным параметром отчета</p>
                </span>
                <span v-else class="hm-report-modal__filter-button-content">
                    <icons style="height: 16px;" type="!input"/>
                    <p>Поле не является входным параметром отчета</p>
                </span>
            </button>
        </div>
    </div>
</template>
<script>
import Icons from "../icons";
export default {
    props: ["data"],
    components: {
        Icons
    },
    data() {
        return {
            statusFields: this.data.options,
            activeFunction: this.data.options.function,
            activeAggregation: this.data.options.aggregation,
            valueFilter: this.data.options.filter
        }
    },
    methods: {
        changeVisible() {
            if(this.data.options.hiden == 0) this.data.options.hiden = 1
            else this.data.options.hiden = 0
            this.$emit("getDataTable");
        },
        changeInput() {
            if(this.data.options.input == 0) this.data.options.input = 1
            else this.data.options.input = 0
            this.$emit("getDataTable");
        },
        changeFunction() {
            this.data.options["function"] = this.activeFunction;
            this.$emit("getDataTable");
        },
        dropFunction() {
            this.activeFunction = 0;
            this.changeFunction();
            this.$emit("getDataTable");
        },
        getLabelAggregation(value) {
            let label = "";
            if(value === 'max') label = "Максимум"
            else if(value === 'min') label = "Минимум"
            else if(value === 'avg') label = "Среднее"
            else if(value === 'sum') label = "Сумма"
            else if(value === 'count') label = "Количество"
            else if(value === 'group_concat') label = "Групповое объединение"
            else if(value === 'count_distinct') label = "Количество уникальных элементов"
            else if(value === 'group_concat_distinct') label = "Групповое объединение уникальных элементов"
            return label
            this.$emit("getDataTable");
        },
        changeAggregation() {
            this.data.options["aggregation"] = this.activeAggregation;
            this.$emit("getDataTable");
        },
        dropAggregation() {
            this.activeAggregation = '';
            this.changeAggregation();
        },
        changeFilter() {
            this.data.options["filter"] = this.valueFilter;
            this.$emit("getDataTable");
        },
        closeModal() {
            this.data.modalStatus = !this.data.modalStatus;
        }
    }
}
</script>
<style lang="scss">
.hm-report-designer__table-head-item:first-child {
    .hm-report-modal {
        left: 0;
        right: auto;
    }
}
.hm-report-modal {
    width: 100%;
    min-width: 260px;
    max-height: 427px;
    overflow: auto;
    background: #FFFFFF;
    box-shadow: 0px 6px 10px rgba(101, 101, 101, 0.25);
    border-radius: 4px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    cursor: auto;
    &__button-close {
        width: 15px;
        height: 15px;
        position: relative;
        margin-left: auto;
        margin-right: 0 !important;
        margin-bottom: 10px;
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
    &__filter {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 25px;
        &:last-child {
            margin-bottom: 0;
        }
        &-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 10px !important;
        }
        &-button-drop {
            font-size: 12px !important;
            margin: 0;
            &:hover {
                opacity: 0.5;
            }
        }
        &-title {
            font-weight: 500;
            font-size: 13px;
            line-height: 16px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
            margin-bottom: 0 !important;
        }
        &-input {
            border: 1px solid #D4E3FB !important;
            box-sizing: border-box !important;
            border-radius: 4px !important;
            width: 100% !important;
            padding: 12px 10px !important;
            font-style: normal !important;
            font-weight: 300 !important;
            font-size: 12px !important;
            line-height: 15px !important;
            letter-spacing: 0.02em !important;
            color: #1E1E1E !important;
            &::placeholder {
                color: #C4C4C4 !important;
            }
        }
        &-button-content {
            display: flex;
            align-items: center;
            & p {
                margin-bottom: 0 !important;
                margin-left: 13px;
                font-weight: 300;
                font-size: 12px;
                line-height: 15px;
                letter-spacing: 0.02em;
                color: #1E1E1E;
                text-align: start;
            }
        }
    }
}
</style>
