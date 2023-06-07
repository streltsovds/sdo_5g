<template>
    <div id="hmSearch">
        <input type="text" v-model="dataSearch" :placeholder="placeholder" @keydown.enter="startSearch">
        <div class="search-icon" @click="startSearch" v-if="dataSearch === ''">
        <svg-icon  stroke-width="2"  width="17" height="17" color="#979797" name="search" title="Найти" />
      </div>
      <div v-else
           class="search-icon search-clear__clear-button"
           @click="returnResult">
        <svg-icon width="14" height="14" color="inherit" name="close" title="Очистить" />
      </div>
    </div>
</template>

<script>
    import SvgIcon from "@/components/icons/svgIcon";
    export default {
        name: "hm-search",
        components: {SvgIcon},
        props: {
            placeholder:{type: String, default:'Поиск'},
            subjectId: {type: [String, Number], default: null}
        },
        data() {
          return {
              dataSearch: '', // строка для связи
          }
        },
        watch: {
            dataSearch(data) {
                if(data === '') {
                    this.$store.dispatch('dataContacts/SearchUsers', {id:this.subjectId, search:'' } )
                }
            }
        },
        methods: {
          startSearch() {
            if(this.dataSearch !== '') {
                this.$store.dispatch('dataContacts/SearchUsers', {id:this.subjectId, search:this.dataSearch } )
            }
          },
          returnResult() {
              this.$store.dispatch('dataContacts/SearchUsers', {id:this.subjectId, search:'' } )
              this.dataSearch = '';
          }
        }
    }
</script>

<style lang="scss">
#hmSearch {
  width: 100%;
  position: relative;
  .search-clear {
    width: 100%;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    margin-top: 10px;
    &__clear-button {
      width: 26px;
      height: 26px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      transition: .1s linear;
      &:hover {
        background:  #E6E6E6;
        > svg {
          fill :  #666666;
        }
      }
      > svg {
        fill: #C4C4C4;
      }
      &:active {
        > svg {
          fill : #1E1E1E;
        }
        cursor: pointer;
      }
    }
    > span {
      font-style: normal;
      font-weight: normal;
      font-size: 12px;
      line-height: 18px;
      letter-spacing: 0.15px;
      color: #979797;
    }
  }
  > input {
    width: 100%;
    height: 100%;
    padding: 12px 50px 12px 16px;
    background: #FFFFFF;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    color:  #70889E;
    outline: none;
    &::placeholder {
        color: #70889E;
        font-size: .9em;
    }
    &:focus {
        outline: 1px solid #2c98f0;
    }
  }
  .search-icon {
    position: absolute;
    top: 9.5px;
    right: 17px;
    cursor: pointer;
  }
}
</style>
