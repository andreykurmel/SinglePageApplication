<template>
    <div class="progress-wrapper" @click="showEdit()">
        <div v-if="!editing" class="full-height">
            <div class="pr-line" :style="valStyle"></div>
            <span class="pr-val" :style="textStyle">{{ showedPercent }}%</span>
        </div>
        <input v-else="" ref="inline_input" :value="curVal" @blur="setVal()"/>
    </div>
</template>

<script>
    import {SpecialFuncs} from '../../../classes/SpecialFuncs';

    import CellStyleMixin from './../../_Mixins/CellStyleMixin.vue';

    export default {
        name: "ProgressBar",
        mixins: [
            CellStyleMixin,
        ],
        data: function () {
            return {
                editing: false,
            }
        },
        props:{
            pr_val: Number,
            can_edit: Boolean,
            table_header: Object,
            ignore_format: Boolean,
        },
        computed: {
            curVal() {
                return this.getValInRange(this.pr_val);
            },
            valStyle() {
                return {
                    width: (this.curVal*100)+'%',
                    cursor: (this.can_edit ? 'pointer' : 'initial'),
                };
            },
            showedPercent() {
                return this.ignore_format
                    ? Number(this.curVal*100).toFixed(0)
                    : SpecialFuncs.format( this.table_header, this.curVal*100 );
            },
        },
        methods: {
            setVal() {
                let vv = this.can_edit ? this.getValInRange( $(this.$refs.inline_input).val() ) : this.pr_val;
                this.editing = false;
                this.$emit( 'set-val', vv );
            },
            showEdit() {
                if (this.can_edit) {
                    this.editing = true;
                    this.$nextTick(function () {
                        if (this.$refs.inline_input) {
                            this.$refs.inline_input.focus();
                        }
                    });
                }
            },
            getValInRange(val) {
                let res = to_float(val);
                if (res > 1) {
                    res = res / 100;
                }
                res = Math.min(res, 1);
                res = Math.max(res, 0);
                return res;
            }
        },
    }
</script>

<style lang="scss" scoped>
    .progress-wrapper {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        border: 1px solid #4c8a31;
        position: relative;
        overflow: hidden;

        .pr-line {
            background: linear-gradient(rgb(162, 225, 136), rgb(81, 148, 59) 50%, rgb(76, 138, 49) 50%, rgb(107, 169, 80));
            border-radius: 12px;
            position: relative;
            height: 100%;
        }

        .pr-val {
            position: absolute;
            right: 5px;
            top: 0;
            height: 100%;
            font-weight: bold;
            color: black;
            display: block;
            //background-color: rgba(0,0,0,0.35);
            padding: 0 3px;
            border-radius: 6px;
        }

        input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 0 5px;
            height: 100%;
            overflow: hidden;
        }
    }
</style>