<template>
  <div class="MODULE module-chart">
    <component
      :is="dynamicChart"
      v-if="dynamicChart && !hasError"
      :data="formattedData"
      :options="options"
    />
    <v-alert :value="hasError" type="error" outlined>
      Произошла ошибка при загрузке данных
    </v-alert>
    <v-progress-circular
      class="module-chart_loader"
      v-if="isLoading"
      indeterminate
      color="primary"
    />
  </div>
</template>
<script>
export default {
  components: {
    ChartArea: () => import("./charts/area"),
    ChartBar: () => import("./charts/bar"),
    ChartApexbar: () => import("./charts/apexbar"),
    ChartLine: () => import("./charts/line"),
    ChartPie: () => import("./charts/pie"),
    ChartRadar: () => import("./charts/radar")
  },
  props: {
    /**
     * url
     * нужен для ajax запроса.
     * если не указан url, используются данные из свойства data
     */
    url: {
      type: String,
      default: null
    },
    /**
     * formFields
     * данные, которые необходимо добавить в post запрос
     * формат данных массив объектов со свойствами key и value
     * пример: [{ key: "key", value: "type" }, { key: "value", value: v }];
     */
    formFields: {
      type: Array,
      default: () => []
    },
    /**
     * formatter
     * функция для форматирования данных, если не указана будет использоваться
     * функция форматирования по умолчанию defaultFormatter
     */
    formatter: {
      type: Function,
      default: null
    },
    /**
     * data
     * данные для построения графика
     * формат: массив объектов со свойствами profile(значение) и title(подпись) по умолчанию.
     * если названия свойств отличаются от значений по умолчанию,
     * то их можно указать в dataValue и dataLabel соответственно
     * data перед построением графика форматируется функцией defaultFormatter или formatter
     */
    data: {
      type: [Array, Object],
      default: () => []
    },
    /**
     * type
     * типы: bar, pie, line, area
     */
    type: {
      type: String,
      default: "bar"
    },
    /**
     * options
     *
     * опции для построения графиков.
     * Опции по умолчанию: /charts/mixins/Options.js getDefaultOptions
     */
    options: {
      type: Object,
      default: () => {}
    },
    // id: {
    //   type: String,
    //   required: true
    // },
    /**
     * dataValue
     * значение указывается, если в данных для постороения название свойства содержащего
     * значение отличается от profile
     */
    dataValue: {
      type: String,
      default: null
    },
    /**
     * dataLabel
     * значение указывается, если в данных для постороения название свойства содержащего
     * подпись отличается от title
     */
    dataLabel: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      legends: [],
      componentsTypeConformity: {
        bar: "ChartBar",
        pie: "ChartPie",
        line: "ChartLine",
        area: "ChartArea",
        radar: "ChartRadar",
        apexbar: "ChartApexbar"
      },
      isLoading: false,
      formattedData: [],
      hasError: false
    };
  },
  computed: {
    dynamicChart() {
      if (!this.componentsTypeConformity.hasOwnProperty(this.options.type || this.type)) return null;
      return this.componentsTypeConformity[(this.options.type || this.type)];
    }
  },
  watch: {
    formFields() {
      this.getData();
    },
    data() {
      this.init();
    }
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      if (this.url && this.url.length > 0) return this.getData();

      this.formattedData = this.getFormattedData(this.data);
    },
    getData() {
      if (this.isLoading) return;

      this.isLoading = true;
      this.hasError = false;
      let formData = new FormData();

      this.formFields.forEach(field => {
        formData.append(field.key, field.value);
      });

      this.$axios
        .post(this.url, formData)
        .then(r => {
          if (r.status === 200 && r.data && r.data.data.length > 0) {
            let data = r.data.data;
            this.$emit("loadData", r.data);

            this.formattedData = this.getFormattedData(data);
          }
        })
        .catch(e => {
          console.error(e);
          this.hasError = true;
        })
        .finally(() => (this.isLoading = false));
    },
    getFormattedData(data) {
      if(this.type.includes('apex')) return data;
      
      return this.isFunction(this.formatter)
        ? this.formatter(data)
        : this.defaultFormatter(data);
    },
    defaultFormatter(data) {
      if ((this.options.type || this.type) === "radar") return data;

      let formatterData = [];
      let dataValue = this.dataValue ? this.dataValue : "profile";
      let dataLabel = this.dataLabel ? this.dataLabel : "title";

      data.forEach(set => {
        
        if ((!set.hasOwnProperty(dataValue) && dataValue !== '...arr') || !set.hasOwnProperty(dataLabel))
          return;

        // let valuesByDataValue = [...Object.entries(set)].filter(([index, itm]) => console.log(index));

        formatterData.push({
          ...set,
          value: set[dataValue],
          label: set[dataLabel],
        });
      });

      return formatterData;
    },
    isFunction(functionToCheck) {
      return (
        functionToCheck &&
        {}.toString.call(functionToCheck) === "[object Function]"
      );
    }
  }
};
</script>
<style lang="scss">
.module-chart {
  position: relative;
  min-height: 100px;
  //axis X
  .module-chart_x-axis {
    .tick {
      .text-vertical {
        transform: translateX(20px) rotate(70deg) translateY(10px);
        text-anchor: start;
      }
    }
  }
  .module-chart_loader {
    display: block;
    margin: auto;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translateY(-50%);
  }
}
</style>
