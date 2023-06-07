<template>
  <div id="hmContactsCardMini">
    <div class="card-user-mini">
      <div class="card-user-mini-photo">
        <img v-if="dataCard.photo && dataCard.photo === ''" :src="dataCard.photo" alt="фотография">
        <svg-icon width="36" height="36" color="#4A90E2" v-else  name="user-round"/>
      </div>
      <div class="card-user-mini-info">
        <span class="card-user-mini-info__name">{{ dataCard.name }}</span>
        <span class="card-user-mini-info__desc" v-if="dataCard.description !== '' && dataCard.description">{{ dataCard.description }}</span>
      </div>
    </div>
    <div
      v-if="iconRemove"
      class="card-user-remove"
      @click="clearuser"><svg-icon  width="14" height="14" color="inherit" name="close" title="удалить" /> </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  name: "hmContactsCardMini",
    components: {SvgIcon},
    props: {
      dataCard: {
        type:Object,
        default:()=> {} },
      iconRemove: {
          type: Boolean,
          default: true
      },
  },
  methods: {
    // метод точечного удаления пользователя
    clearuser() {
      this.$store.commit('dataContacts/SET__selectedUser',{user: this.dataCard, flag: false} )
    }
  }
}
</script>

<style lang="scss">
#hmContactsCardMini {
  width: 100%;
  height: 52px;
  max-width: 294px;
  background: #FFFFFF;
  box-shadow: 5px 5px 25px rgba(179, 179, 179, 0.25);
  border-radius: 40px;
  display: flex;
  margin-bottom: 12px;
  position: relative;
  &:last-child {
    margin-bottom: 0;
  }
  .card-user-mini {
    display: flex;
    align-items: center;
    &-photo {
      width: 36px;
      height: 36px;
      margin: 8px 12px 8px 8px;
      border-radius: 50%;
    }
    &-info {
      display: flex;
      flex-direction: column;
      padding: 8px 0;
      &__name {
        font-weight: normal;
        font-size: 14px;
        line-height: 21px;
        letter-spacing: 0.02em;
        color: #5181B8;
      }
      &__desc {
        font-weight: normal;
        font-size: 12px;
        line-height: 18px;
        letter-spacing: 0.15px;
        color: #70889E;
      }
    }
  }
  .card-user-remove {
    position: absolute;
    right: 16px;
    top: 12px;
    width: 26px;
    height: 26px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    transition: .1s linear;
    > svg {
      fill: #C4C4C4;
    }
    &:hover {
      background:  #E6E6E6;
      > svg {
        fill :  #666666;
      }
    }
    &:active {
      > svg {
        fill : #1E1E1E;
      }
      cursor: pointer;
    }
  }

}
</style>
