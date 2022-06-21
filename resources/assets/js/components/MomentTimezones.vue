<template>
    <select-block
            v-if="timezones.length"
            :options="timezones"
            :sel_value="tz"
            :fixed_pos="true"
            :can_search="true"
            :hidden_name="name"
            @option-select="tzChanged"
    ></select-block>
</template>

<script>
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
        },
        methods: {
            tzChanged(opt) {
                this.tz = opt.val;
                this.$emit('changed-tz', opt.val);
            },
        },
        mounted() {
            this.timezones = _.map(moment.tz.names(), (tz) => {
                return {
                    val: tz,
                    show: tz,
                };
            });
        }
    }
</script>