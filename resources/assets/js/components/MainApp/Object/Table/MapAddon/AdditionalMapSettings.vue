<template>
    <div class="map_additionals" :style="textSysStyle">
        <div class="form-group flex flex--center-v no-wrap">
            <div class="flex flex--center">
                <label>Info Panel:</label>
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Header color:&nbsp;</label>
                <div class="color_wrap w-sm">
                    <tablda-colopicker
                        :init_color="tableMeta.map_popup_header_color"
                        :avail_null="true"
                        @set-color="(clr) => {tableMeta.map_popup_header_color = clr; updateRow('map_popup_header_color')}"
                    ></tablda-colopicker>
                </div>
            </div>

            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Width:&nbsp;</label>
            <input class="form-control w-sm" v-model="tableMeta.map_popup_width" @change="updateRow('map_popup_width')"/>
            <label>px</label>
            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
            <input class="form-control w-sm" v-model="tableMeta.map_popup_height" @change="updateRow('map_popup_height')"/>
            <label>px</label>
        </div>

        <div class="form-group flex flex--center-v no-wrap">
            <label>Image display style:&nbsp;</label>
            <select class="form-control w-md" v-model="tableMeta.map_picture_style" @change="updateRow('map_picture_style')">
                <option value="scroll">Scroll</option>
                <option value="slide">Slide</option>
            </select>

            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Image on Right:&nbsp;</label>
            <select class="form-control w-md" v-model="tableMeta.map_picture_field" @change="updateRow('map_picture_field')">
                <option></option>
                <option
                    v-for="header in tableMeta._fields"
                    v-if="header.f_type === 'Attachment'"
                    :value="header.id"
                >{{ header.name }}</option>
            </select>

            <template v-if="tableMeta.map_picture_width">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Image width:&nbsp;</label>
                <input class="form-control w-sm" v-model="tableMeta.map_picture_width" @change="updateRow('map_picture_width')"/>
                <label>%</label>
            </template>
        </div>
    </div>
</template>

<script>
    import TabldaColopicker from "../../../../CustomCell/InCell/TabldaColopicker";

    import CellStyleMixin from "../../../../_Mixins/CellStyleMixin";

    export default {
        name: "AdditionalMapSettings",
        components: {
            TabldaColopicker,
        },
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
            }
        },
        computed: {
        },
        props:{
            tableMeta: Object,
        },
        methods: {
            updateRow(changed) {
                this.$emit('upd-table', changed);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .map_additionals {
        padding: 15px;
        background: white;
        height: calc(100% - 30px);

        label {
            margin: 0;
        }
        .color_wrap {
            width: 150px;
            height: 36px;
            position: relative;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .w-sm {
            width: 60px;
        }
        .w-md {
            width: 90px;
        }
    }
</style>