<template>
    <div class="star-wrapper flex flex--center">

        <template v-if="getIcon(0) && enough_width">
            <img v-for="i in star_count"
                 :src="getIcon(i)"
                 :style="getStyle(i)"
                 class="star-icon"
                 @click="setStar(i)"
                 @mouseover="tmpSet(i)"
                 @mouseout="tmpClear()"/>
        </template>

        <template v-else-if="getIcon(0) && !enough_width">
            <select class="form-control select-star" v-model="cur_val" @change="setStar(cur_val)" @mousedown.stop="" @mouseup.stop="">
                <option :value="0">0</option>
                <option v-for="i in star_count" :value="i">{{ i }}</option>
            </select>
            <img :src="getIcon(0)" :style="getStyle(0)" class="star-icon"/>
        </template>

        <template v-else=""></template>
    </div>
</template>

<script>
    export default {
        name: "StarsElems",
        data: function () {
            return {
                tmp_star: 0,
            }
        },
        props:{
            cur_val: {
                type: Number,
                default: 0,
            },
            can_edit: Boolean|Number,
            table_header: Object,
        },
        computed: {
            enough_width() {
                return this.star_count * 26 < this.table_header.width;
            },
            star_count() {
                return this.table_header.f_format
                    ? Number( String(this.table_header.f_format).replace(/[^\d]/gi, '') ) || 5
                    : 5;
            },
        },
        watch: {
        },
        methods: {
            getStyle(val) {
                return {
                    filter: (this.getStar() < val ? 'grayscale(1)' : ''),
                    cursor: (this.can_edit ? 'pointer' : 'initial'),
                    opacity: (this.tmp_star ? '0.7' : '1'),
                };
            },
            getStar() {
                let star = this.tmp_star || this.cur_val || 0;
                return isNaN(star) ? 0 : star;
            },
            getIcon(val) {
                let type = String(this.table_header.f_format).replace(/[-\d]/gi, '');
                switch (type) {
                    case 'Custom':
                        return this.$root.fileUrl({url:this.table_header.rating_icon});

                    case 'Flag':
                        return '/assets/img/flag-icon.png';

                    case 'Star':
                    default:
                        if (this.getStar() < val) {
                            return '/assets/img/aeon-star-empty.png';
                        } else {
                            return '/assets/img/aeon-star.png';
                        }
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

        .btn-deletable {
            position: absolute;
            top: 10%;
            right: 3px;
            bottom: 10%;
            padding: 0 3px;

            span {
                font-size: 1.4em;
                line-height: 0.7em;
                display: inline-block;
            }
        }

        .select-star {
            width: 45px;
            max-height: 100%;
            max-width: 75%;
            display: inline-block;
            padding: 0 6px;
        }
    }
</style>