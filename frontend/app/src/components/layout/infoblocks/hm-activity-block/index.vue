<template>
  <div class="INFOBLOCK infoblock-activity-block">
    <v-card-text>
      <div class="infoblock-activity-block_filters">
        <v-layout row wrap>
          <v-flex sm12 md3>
            <v-select
              v-model="selectedCourse"
              :items="courses"
              label="Курс"
              class="hm-form-element"
            >
              <template slot="selection" slot-scope="{ item }">
                <span>{{ item.text }}</span>
              </template>
              <template slot="item" slot-scope="{ item }">
                <span>{{ item.text }}</span>
              </template>
            </v-select>
          </v-flex>
          <v-flex sm12 md3>
            <v-select
              v-model="selectedUser"
              :items="users"
              label="Пользователь"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md3>
            <v-select
              v-model="selectedType"
              :items="types"
              label="Вид"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md3>
            <v-select
              v-model="selectedPeriod"
              :items="periods"
              label="Период"
              class="hm-form-element"
            />
          </v-flex>
          <v-flex sm12 md12 class="infoblock-activity-block_chart-container">
            <hm-chart
              id="infoblock-activity-block_chart"
              :url="url"
              :form-fields="formFields"
              :type="chart.type"
              :options="chart.options"
            />
          </v-flex>
        </v-layout>
      </div>
    </v-card-text>
  </div>
</template>
<script>
import HmChart from "@/components/media/hm-chart/index";
export default {
  components: { HmChart },
  props: {
    url: {
      type: String,
      default: null
    },
    courses: {
      type: Array,
      default: () => []
    },
    users: {
      type: Array,
      default: () => []
    },
    types: {
      type: Array,
      default: () => []
    },
    periods: {
      type: Array,
      default: () => []
    },
    type: {
      type: String,
      default: null
    },
    period: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedCourse: {},
      selectedUser: {},
      selectedType: {},
      selectedPeriod: {},
      chart: {
        type: "bar",
        options: {
          height: 312,
          margin: {
            left: 30,
            bottom: 40
          },
          tooltip: {
            show: true,
            content: "[[ value ]]"
          },
          axisX: {
            text: {
              rotate: true
            }
          },
          axisY: {
            ticksCount: 5
          }
        }
      },
      formFields: []
    };
  },
  watch: {
    selectedType(v) {
      this.setFormField("type", v);
    },
    selectedPeriod(v) {
      this.setFormField("period", v);
    },
    selectedUser(v) {
      this.setFormField("user", v);
    },
    selectedCourse(v) {
      this.setFormField("group", v);
    }
  },
  created() {
    const defaultType = this.types[0].value;
    const selectedType = this.types.find(
      item =>
        item.value !== undefined &&
        item.value.toString() === this.type.toString()
    );
    this.selectedType = selectedType || defaultType;

    const defaultPeriod = this.periods[0].value;
    const selectedPeriod = this.periods.find(
      item =>
        item.value !== undefined &&
        item.value.toString() === this.period.toString()
    );
    this.selectedPeriod = selectedPeriod || defaultPeriod;

    this.selectedCourse = this.courses[0].value;
    this.selectedUser = this.users[0].value;
  },
  methods: {
    setFormField(name, value) {
      if (!value) return;

      this.formFields = [{ key: "key", value: name }, { key: "value", value }];
    }
  }
};
</script>
<style lang="scss">
  .infoblock-activity-block_filters {
    .hm-form-element{
      margin: 0 10px 0 0;
    }
  }
</style>
