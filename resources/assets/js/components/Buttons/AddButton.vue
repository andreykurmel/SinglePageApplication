<template>
    <div class="blue-gradient add_button" :style="$root.themeButtonStyle">
        <div class="flex flex--center full-height">
            <button
                    class="btn btn-primary btn-sm blue-gradient"
                    :disabled="!available || in_query"
                    :style="btnStylenb"
                    @click="addClicked()"
            >Add</button>
            <span v-show="check" class="indeterm_check__wrap" style="padding-right: 7px">
                <span class="indeterm_check"
                      :class="{'disabled': !available || in_query}"
                      @click="!available || in_query ? null : addingRow.active = !addingRow.active">
                    <i v-if="addingRow.active" class="glyphicon glyphicon-ok group__icon"></i>
                </span>
            </span>
        </div>
    </div>
</template>

<script>
    export default {
        name: "AddButton",
        mixins: [
        ],
        data: function () {
            return {
                in_query: false,
            }
        },
        props:{
            addingRow: Object,
            available: Boolean|Number,
            check: {
                type: Boolean,
                default: function () {
                    return true;
                }
            },
        },
        computed: {
            btnStylenb() {
                let style = _.cloneDeep(this.$root.themeButtonStyle);
                style.border = 'none';
                return style;
            }
        },
        methods: {
            addClicked() {
                if (this.available && !this.in_query) {
                    this.in_query = true;
                    this.$emit('add-clicked');
                    setTimeout(() => {
                        this.in_query = false;
                    }, 1500);
                }
            },
        },
    }
</script>

<style scoped lang="scss">
    .add_button {
        display: inline-block;
        height: 30px;
        border: none;/*1px solid rgb(10, 93, 184);*/
        border-radius: 4px;

        .flex {
            button {
                position: relative;
                height: 28px;
                border: none;
            }
            input {
                margin: 2px 5px 0 0;
                width: 20px;
                height: 20px;
            }
        }
    }
</style>