<template>
    <div id="hmContactsNewCanal">
        <div class="new-canal__title">
          <svg-icon width="24" height="21" color="#1e1e1e" name="telegram"/>
          <span>Создать канал в Telegram</span>
        </div>
        <div class="new-canal__link">
          <span>Можете создать Telgram канал для выделенных пользователей - <a class="new-canal__link-create" href="">создать.</a></span>
        </div>
    </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  name: "hmContactsNewCanal",
    components: {SvgIcon},
}
</script>

<style lang="scss">
#hmContactsNewCanal {
  width: 100%;
  display: flex;
  flex-direction: column;
  margin-top: 34px;
  .new-canal__title {
    > svg {
      margin-right: 12px;
    }
    > span {
      font-weight: 500;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
    margin-bottom: 16px;
  }
  .new-canal__link {
    width: 100%;
    height: 42px;
    display: flex;
    align-items: center;
    > span {
      font-weight: normal;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #70889e;
      > a {
        text-decoration: none;
        cursor: pointer;
        color: #2574cf;
        font-style: normal;
      }
    }
  }
}

</style>
