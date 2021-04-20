<template>
    <div class="star-wrapper">
        <img v-for="i in star_count"
             :src="getStar(i)"
             :style="getStyle"
             class="star-icon"
             @click="setStar(i)"
             @mouseover="tmpSet(i)"
             @mouseout="tmpClear()"/>
    </div>
</template>

<script>
    export default {
        name: "StarsElems",
        data: function () {
            return {
                tmp_star: 0,
                star_count: 5,
            }
        },
        props:{
            cur_val: Number,
            can_edit: Boolean,
        },
        computed: {
            getStyle() {
                return {
                    cursor: (this.can_edit ? 'pointer' : 'initial'),
                    opacity: (this.tmp_star ? '0.7' : '1'),
                };
            }
        },
        methods: {
            getStar(val) {
                let star = this.tmp_star || this.cur_val;
                star = isNaN(star) ? 0 : star;
                if (star < val) {
                    return '/assets/img/aeon-star-empty.png';
                } else {
                    return '/assets/img/aeon-star.png';
                }
            },
            setStar(val) {
                if (this.can_edit) {
                    this.$emit('set-star', val);
                }
            },
            tmpSet(val) {
                if (this.can_edit) {
                    this.tmp_star = val;
                }
            },
            tmpClear() {
                this.tmp_star = 0;
            },
        },
    }
</script>

<style lang="scss" scoped>
    .star-wrapper {
        position: relative;
        overflow: initial;
        text-align: center;

        .star-icon {
            max-width: 25px;
            max-height: 25px;
            width: auto;
            height: 100%;
        }
    }
</style>