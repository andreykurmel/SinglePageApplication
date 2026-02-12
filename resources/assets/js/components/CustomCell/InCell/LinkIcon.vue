<template>
    <span>
        <i v-if="icon === 'Record'" class="fa fa-info-circle"></i>

        <i v-else-if="icon === 'Table'" class="fa fa-link"></i>

        <i v-else-if="icon === 'Arrow'" class="glyphicon glyphicon-play"></i>

        <i v-else-if="icon === 'Delete'" class="fa fa-times"></i>

        <i v-else-if="icon === 'History'" class="fa fa-history"></i>

        <img v-else-if="icon === 'PDF'" src="/assets/img/icons/pdf_icon.png" width="17" height="17">

        <img v-else-if="icon === 'Doc'" src="/assets/img/icons/icon_document.png" width="17" height="17">

        <img v-else-if="icon === 'Web'" src="/assets/img/web_link.png" width="17" height="17">

        <img v-else-if="icon === 'GoogleMap'" src="/assets/img/gmaps_icon.png" width="17" height="17">

        <img v-else-if="icon === 'GoogleEarth'" src="/assets/img/gearth_icon.png" width="17" height="17">

        <button v-else-if="icon === 'Button'"
                class="btn btn-default blue-gradient"
                style="padding: 0px 5px; margin: 0; font-size: 0.8em;"
                :style="$root.themeButtonStyle"
        >{{ tableHeader.name }}</button>

        <i v-else-if="iconText === ''" class="space_content">{{ iconText }}</i>

        <button v-else-if="iconParam.toUpperCase() === '[B]'"
                class="btn btn-default blue-gradient"
                style="padding: 0px 5px; margin: 0; font-size: 0.8em;"
                :style="$root.themeButtonStyle"
        >{{ iconText }}</button>

        <i v-else-if="iconParam.toUpperCase() === '[U]'" class="link_content l_underlined">{{ iconText }}</i>

        <i v-else="" class="link_content l_circle">{{ iconText }}</i>
    </span>
</template>

<script>
    export default {
        name: "LinkIcon",
        props:{
            icon: String,
            tableHeader: Object,
        },
        computed: {
            trimIcon() {
                return String(this.icon || '').trim();
            },
            iconParam() {
                let match = this.trimIcon.match(/\[[^\]]\]/gi);
                return match && match[0] ? String(match[0] || '') : '';
            },
            iconText() {
                return this.trimIcon.replace(this.iconParam, '');
            },
        }
    }
</script>

<style lang="scss" scoped>
    span {
        img {
            position: relative;
            top: -1px;
        }
        .link_content {
            padding: 0 3px;
            font-size: 0.70em;
            line-height: 1.4em;
            height: 1.3em;
            display: inline-block;
            position: relative;
            top: -1px;
        }
        .l_circle {
            border: 1px solid #3097D1;
            border-radius: 50%;
        }
        .l_underlined {
            text-decoration: underline;
        }
        .space_content {
            text-decoration: underline;
            cursor: pointer;
        }
        .fa-times {
            color: #F00;
        }
    }
</style>