<template>
  <div id="searchSubject">
    <input
      :value="currentText"
      @input="testings"
      type="text"
      :placeholder="placeholder" />
    <div class="icon-search">
      <svg-icon name="Search" stroke-width="3px" style="width: 16px; height: 16px" color="#DADADA" />
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  name: "SearchSubject",
  components: {SvgIcon},
  props: {
    placeholder: {
      type: String,
      default: "поиск",
    },
  },
  data() {
    return {
      text: null,
    };
  },
    computed: {
      defaultText() {
         return this.$store.getters['subject/GET_SEARCH_QUERY']
      },
      currentText() {
        if(this.text === null) {
            return this.defaultText ? this.defaultText : ''
        } return this.text
      }
    },
    methods: {
      testings(value) {
          this.text = value.target.value
          this.$emit('testing', this.text)
      }
    }
};
</script>

<style lang="scss">
#searchSubject {
  width: 100%;
  height: 48px;
  margin: 17px 0 15px 0;
  position: relative;
  > input {
    width: 100%;
    height: 100%;
    background: #FFFFFF;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    padding: 16px 46px 13px 16px;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #70889E;
    &:active {
      border: none;
      outline: none;
    }
    &:focus {
      border: none;
      outline: none;
    }
    &::placeholder {
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #70889E;
    }

  }
  .icon-search {
    position: absolute;
    right: 16px;
    top: 12px;
  }
}
</style>
