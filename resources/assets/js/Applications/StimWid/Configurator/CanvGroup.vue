<template>
    <div class="group-wrap" v-if="data_eqpt && settings" @click.self="$emit('empty-clicked')">
        <!--Equipments-->
        <template v-for="d_eqpt in eqptGroup">
            <!--<template v-for="i in d_eqpt.qty">-->
                <canv-group-eqpt
                        v-if="!d_eqpt._hidden"
                        :eqpt="d_eqpt"
                        :settings="settings"
                        :px_in_ft="px_in_ft"
                        :eqpt_params="eqptParams"
                        :style="settings.full_mirr({left: 0})"
                        class="eqpt--z"
                        @select-port="portSel"
                        @right-click="eRightClick"
                        @save-model="eSaveModel"
                        @modelid-changed="modelidChaged"
                ></canv-group-eqpt>
            <!--</template>-->
            <div class="tooltip__bg" :title="getTitle" @click.self="$emit('empty-clicked')"></div>
        </template>
        <!--Equipments-->
    </div>
</template>

<script>
    import {Settings} from "./Settings";
    import {Sector} from "./Sector";
    import {Pos} from "./Pos";

    import CanvGroupEqpt from "./CanvGroupEqpt";

    export default {
        name: 'CanvGroup',
        mixins: [
        ],
        components: {
            CanvGroupEqpt
        },
        data() {
            return {
            }
        },
        computed: {
            eqptGroup() {
                let sec = [ (this.sector ? this.sector.sector : '') ];
                sec = sec.concat(this.shared_sec_pos || []);
                let pos = [ (this.pos ? this.pos.name : '') ];

                return _.filter(this.data_eqpt, (el) => {
                    return Boolean(this.top_lvl) === Boolean(el.is_top())
                        && in_array(el.sector, sec)
                        && (this.shared_sec_pos || in_array(el.pos, pos));
                });
            },
            getTitle() {
                return this.pos
                    ? 'Pos:' + this.pos.name
                    : (this.sector ? 'Sector:' + this.sector.sector : '');
            },
            eqptParams() {
                return {
                    group_he: this.group_he,
                    top_lvl: this.top_lvl,
                };
            },
        },
        props: {
            shared_sec_pos: Array,
            data_eqpt: Array,
            sector: Sector,
            pos: Pos,
            px_in_ft: Number,
            group_he: Number,
            top_lvl: Number,
            settings: Settings,
        },
        watch: {
        },
        methods: {
            //proxy
            eRightClick(row_id) {
                this.$emit('right-click', row_id);
            },
            eSaveModel(eqpt, sel_exclude) {
                this.$emit('save-model', eqpt, sel_exclude);
            },
            portSel(eqpt_id, pos, idx) {
                this.$emit('select-port', eqpt_id, pos, idx);
            },
            modelidChaged(eqpt, from_model_id) {
                this.$emit('modelid-changed', eqpt, from_model_id);
            },
        },
        mounted() {
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .group-wrap {
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        display: flex;
        justify-content: space-evenly;

        .eqpt--z {
            position: absolute;
        }

        .tooltip__bg {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
        }
    }
</style>