<template>
    <select-block
            v-if="timezones.length"
            :options="timezones"
            :sel_value="tz"
            :fixed_pos="true"
            :can_search="true"
            :hidden_name="name"
            :is_disabled="is_disabled"
            @option-select="tzChanged"
    ></select-block>
</template>

<script>
    import {MomentTzHelper} from "../classes/helpers/MomentTzHelper";

    import SelectBlock from "./CommonBlocks/SelectBlock";

    export default {
        components: {
            SelectBlock,
        },
        name: "MomentTimezones",
        data: function () {
            return {
                timezones: [],
                tz: this.cur_tz ? this.cur_tz : moment.tz.guess(),
            }
        },
        props:{
            name: String,
            cur_tz: String,
            is_disabled: Boolean,
        },
        methods: {
            tzChanged(opt) {
                this.tz = opt.val;
                this.$emit('changed-tz', opt.val);
            },
        },
        mounted() {
            this.timezones = MomentTzHelper.timezones();
        }
    }
</script>