<template>
    <span class="hm-text-link-edit">
        <template v-if="setLink">
            <a :href="hrefUrl"><span><slot /></span></a>
        </template>
            <span v-else><slot /></span>
        <span @mouseover="setPointer" @click="changeText">
            <svg-icon
                class="icon-link"
                name="EditSidebars"
                title="Быстрое редактирование"
            />
        </span>
    </span>
</template>

<script>
    /**
     * Компонент для организации редактирования любого текста или гиперссылки "на месте".
     * Постановка задачи в #33178
     *
     * Компонент настраивается следующими атрибутами:
     *    :lessonId - ID занятия
     *    :setLink - по умолчанию false, если выставлен в true, текст работает как ссылка, иначе простой текст;
     *    :hrefUrl - если :setLink="true", сюда передается url перехода если компонент работает в режиме ссылки;
     *    :receiverUrl - url ajax-приемника для внесения изменений в текст;
     *    :fieldType - тип html-элемента, в котором происходит редактирование текста. Доступные значения: 'input', 'textarea'.
     *    По умолчанию - 'input'. Настроить вид этих элементов можно в методе changeText()
     */

    import axios from "axios";
    import SvgIcon from "@/components/icons/svgIcon";

    export default {
        name: "hmTextLinkEdit",
        components: {SvgIcon},
        props: {
            lessonId: {
                type: Number,
                default: 0
            },
            fieldType: {
                type: String,
                default: 'input'
            },
            hrefUrl: {
                type: String,
                default: ''
            },
            receiverUrl: {
                type: String,
                default: ''
            },
            setLink: {
                type: Boolean,
                default: false
            },
            type: {
                type: String,
                default: ''
            }
        },
        methods: {
            changeText: function (event) {
                var text = this.$slots.default[0].text.trim();
                let currentTarget = event.currentTarget;
                let previousSibling = currentTarget.previousSibling;
                let input = null;
                switch (this.fieldType) {
                    case 'input':
                        input = document.createElement('input');
                        input.type = 'text';
                        input.value = text;
                        input.style.width = '450px';
                        input.style.border = 'solid #0b6aff 1px';
                        break;
                    case 'textarea':
                        input = document.createElement('textarea');
                        input.value = text;
                        input.rows = 5;
                        input.cols = 90;
                        break;
                }

                previousSibling.replaceWith(input);
                input.addEventListener("keyup", this.sendChanges);
            },
            setPointer: function () {
                let icons = document.getElementsByClassName('icon-link');
                for (var i = 0, max = icons.length; i < max; i++) {
                    icons[i].style.cursor = "pointer";
                }
            },
            sendChanges: function(event) {
                event.preventDefault();
                if (event.keyCode === 13) {

                    let formData = new FormData();
                    const name = this.type === 'section' ? 'section_id' : 'lesson_id';
                    formData.append('text', event.target.value.trim());
                    formData.append(name, this.lessonId);

                    this.$axios.post(this.receiverUrl, formData)
                        .then((res) => {
                            if (this.setLink) {
                                let span = document.createElement('span');
                                span.textContent = this.text = this.$slots.default[0].text = res.data.text;
                                let anchor = document.createElement('a');
                                anchor.href = this.hrefUrl;
                                anchor.setAttribute("style", "text-decoration: none !important;");
                                anchor.appendChild(span);
                                event.target.replaceWith(anchor);
                            } else {
                                event.target.replaceWith(res.data.text);
                            }
                        })
                        .catch(err => console.log(err));
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .hm-text-link-edit {
        a {
            text-decoration: none;
        }
    }
    .icon-link {
        margin-left: 10px;
        width: 12px !important;
        position: relative;
        top: -4px;
    }
</style>
