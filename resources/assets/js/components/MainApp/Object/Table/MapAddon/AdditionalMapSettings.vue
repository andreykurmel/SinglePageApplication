<template>
    <div class="map_additionals" :style="textSysStyle">
        <div class="form-group flex flex--center-v no-wrap">
            <div class="flex flex--center">
                <label>Info Panel:</label>
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Header color:&nbsp;</label>
                <div class="color_wrap w-sm">
                    <tablda-colopicker
                        :init_color="selectedMap.map_popup_header_color"
                        :avail_null="true"
                        :can_edit="canEdit"
                        @set-color="(clr) => {selectedMap.map_popup_header_color = clr; updateRow('map_popup_header_color')}"
                    ></tablda-colopicker>
                </div>
            </div>

            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Width:&nbsp;</label>
            <input class="form-control w-sm" v-model="selectedMap.map_popup_width" :disabled="!canEdit" @change="updateRow('map_popup_width')"/>
            <label>px</label>
            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height:&nbsp;</label>
            <input class="form-control w-sm" v-model="selectedMap.map_popup_height" :disabled="!canEdit" @change="updateRow('map_popup_height')"/>
            <label>px</label>
        </div>

        <div class="form-group flex flex--center-v no-wrap">
            <label>Images:&nbsp;</label>
            <select class="form-control w-md" v-model="selectedMap.map_picture_field" :disabled="!canEdit" @change="updateRow('map_picture_field')">
                <option></option>
                <option
                    v-for="header in selectedMap._fields"
                    v-if="header.f_type === 'Attachment'"
                    :value="header.id"
                >{{ header.name }}</option>
            </select>

            <template v-if="selectedMap.map_picture_field">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Position:&nbsp;</label>
                <select class="form-control w-md" v-model="selectedMap.map_picture_position" :disabled="!canEdit" @change="updateRow('map_picture_position')">
                    <option value="top">Top</option>
                    <option value="bottom">Bottom</option>
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                </select>
            </template>

            <template v-if="selectedMap.map_picture_field">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Display style:&nbsp;</label>
                <select class="form-control w-md" v-model="selectedMap.map_picture_style" :disabled="!canEdit" @change="updateRow('map_picture_style')">
                    <option value="scroll">Scroll</option>
                    <option value="slide">Slide</option>
                </select>
            </template>

            <template v-if="selectedMap.map_picture_field">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $root.inArray(selectedMap.map_picture_position, ['left', 'right']) ? 'Width' : 'Height' }}:&nbsp;</label>
                <input class="form-control w-sm" v-model="selectedMap.map_picture_width" :disabled="!canEdit" @change="updateRow('map_picture_width')"/>
            </template>

            <template v-if="selectedMap.map_picture_field">
                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Minimum:&nbsp;</label>
                <input class="form-control w-sm" v-model="selectedMap.map_picture_min_width" :disabled="!canEdit" @change="updateRow('map_picture_min_width')"/>
                <label>px</label>
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
            selectedMap: Object,
            canEdit: Boolean,
        },
        methods: {
            updateRow(changed) {
                if (!this.canEdit) {
                    return;
                }
                if (this.$root.inArray(changed, ['map_picture_width', 'map_picture_min_width'])) {
                    if (this.selectedMap[changed] <= 1) {
                        this.selectedMap[changed] = Math.max(this.selectedMap[changed], 0);
                        this.selectedMap[changed] = Math.min(this.selectedMap[changed], 1);
                    } else {
                        this.selectedMap[changed] = Math.max(this.selectedMap[changed], 10);
                        this.selectedMap[changed] = Math.min(this.selectedMap[changed], 1000);
                    }
                }
                this.$emit('upd-map', this.selectedMap, changed);
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
        height: 100%;

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
            width: 100px;
        }
    }
</style>