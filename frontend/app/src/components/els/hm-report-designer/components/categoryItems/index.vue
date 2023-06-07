<template>
    <draggable class="hm-report-designer__list-group" :list="category" :group="{ name: 'people', put: false }">
        <div
            class="hm-report-designer__header-item"
            v-for="(header, index) in category"
            :key="index"
        >
            <icons type="item-button" />
            <span>{{ header.title }}</span>
        </div>
    </draggable>
</template>
<script>
import draggable from "vuedraggable";
import Icons from "../icons";
export default {
    props: ["category"],
    components: {
        Icons,
        draggable
    }
}
</script>
<style lang="scss">
    .hm-report-designer__list-group {
      margin-top: 10px;
    }
    .hm-report-designer__header-item {
        display: flex;
        align-items: center;
        width: 200px;
        background: rgba(237, 244, 252, 0.5);
        border: 1px solid #D4E3FB;
        box-sizing: border-box;
        border-radius: 4px;
        margin-top: 12px;
        padding: 5px;
        cursor: grab;
        &:active {
            cursor: grabbing;
        }

        &:first-child {
          margin-top: 0;
        }

        & span {
            font-style: normal;
            font-weight: 300;
            font-size: 14px;
            line-height: 16px;
            letter-spacing: 0.02em;
            color: #000000;
            margin-left: 7px;
        }
    }
</style>
