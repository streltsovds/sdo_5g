<template>
  <div>
    <v-checkbox
      v-if="gradeScaleType === GradeType.binary"
      color="green"
      on-icon="check_circle"
      off-icon="check_circle"
      :input-value="inputValue"
      @change="onChange($event)"
    ></v-checkbox>

    <v-layout
      v-else-if="gradeScaleType === GradeType.ternary"
      justify-center
    >
      <v-flex xs6>
        <v-checkbox
          :input-value="inputValue"
          color="green"
          on-icon="check_circle"
          off-icon="check_circle"
          @change="ternaryOnChange($event, 1)"
        ></v-checkbox>
      </v-flex>
      <v-flex xs6>
        <v-checkbox
          color="red"
          on-icon="cancel"
          off-icon="cancel"
          :input-value="inputValue === undefined ? inputValue : !inputValue"
          @change="ternaryOnChange($event, 2)"
        ></v-checkbox>
      </v-flex>
    </v-layout>

    <v-text-field
      v-else
      :value="inputValue"
      solo
      text
      @input="onChange($event)"
    ></v-text-field>
  </div>
</template>

<script>
import GradeType from "@/components/els/marksheet/lib/GradeType";

export default {
  name: "HmGradeInput",
  props: {
    inputValue: {
      type: [String, Boolean, Number],
      default: "",
    },
    gradeScaleType: {
      type: String,
      default: GradeType.continious,
    },
  },
  created() {
    Object.assign(this, {
      GradeType
    });
  },
  methods: {
    onChange(newValue) {
      this.$emit("update:inputValue", newValue);
    },
    ternaryOnChange(newValue, type) {
      if (type === 1) {
        if (this.$props.inputValue) this.$emit("update:inputValue", undefined);
        else this.$emit("update:inputValue", true);
      } else {
        if (
          this.$props.inputValue === undefined ||
          this.$props.inputValue === true
        )
          this.$emit("update:inputValue", false);
        else this.$emit("update:inputValue", undefined);
      }
    }
  }
};
</script>

<style scoped></style>
