<template>
    <span>
        <i v-if="icon === 'Record'" class="fa fa-info-circle"></i>

        <i v-else-if="icon === 'Table'" class="fa fa-link"></i>

        <i v-else-if="icon === 'Arrow'" class="glyphicon glyphicon-play"></i>

        <i v-else-if="icon === 'Delete'" class="fa fa-times"></i>

        <img v-else-if="icon === 'PDF'" src="/assets/img/icons/pdf_icon.png" width="17" height="17">

        <img v-else-if="icon === 'Doc'" src="/assets/img/icons/icon_document.png" width="17" height="17">

        <img v-else-if="icon === 'Web'" src="/assets/img/web_link.png" width="17" height="17">

        <img v-else-if="icon === 'GoogleMap'" src="/assets/img/gmaps_icon.png" width="17" height="17">

        <img v-else-if="icon === 'GoogleEarth'" src="/assets/img/gearth_icon.png" width="17" height="17">

        <i v-else-if="upperIcon.trim() === ''" class="space_content">{{ upperIcon }}</i>

        <i v-else="" class="link_content">{{ upperIcon }}</i>
    </span>
</template>

<script>
    export default {
        name: "LinkIcon",
        props:{
            icon: String,
        },
        computed: {
            upperIcon() {
                return this.icon ? this.icon.toUpperCase() : '';
            }
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
            border: 1px solid #3097D1;
            border-radius: 50%;
            padding: 0 3px;
            font-size: 0.70em;
            line-height: 1.4em;
            height: 1.3em;
            display: inline-block;
            position: relative;
            top: -1px;
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