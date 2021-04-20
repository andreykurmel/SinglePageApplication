<template>
    <div class="popup-wrapper" @click.self="hide()">
        <div class="popup" :style="getPopupStyle()">
            <div class="flex flex--col">
                <div class="popup-header">
                    <div class="flex">
                        <div class="flex__elem-remain" style="text-align: left">Change File Name</div>
                        <div class="" style="padding-bottom: 4px;">
                            <span class="glyphicon glyphicon-remove pull-right header-btn" @click="hide()"></span>
                        </div>
                    </div>
                </div>

                <div class="popup-content popup-main">
                    <div>Enter new name (without extension)</div>
                    <div>
                        <input class="form-control" v-model="file_name"/>
                    </div>
                    <div class="renamer-btns">
                        <button class="pull-right btn btn-default btn-sm" @click="hide()">Cancel</button>
                        <button class="pull-right btn btn-success btn-sm" @click="$emit('f_rename', file_name)">Save</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import PopupAnimationMixin from './../_Mixins/PopupAnimationMixin';

    export default {
        name: "FileRenamerBlock",
        mixins: [
            PopupAnimationMixin,
        ],
        data: function () {
            return {
                file_name: this.no_extension(this.init_name),
                //PopupAnimationMixin
                getPopupWidth: 500,
                idx: 0,
            };
        },
        props:{
            init_name: String
        },
        methods: {
            hide() {
                this.$emit('hide');
            },
            no_extension(str) {
                return String(this.init_name).replace( '.'+_.last(String(this.init_name).split('.')), '' )
            },
        },
        mounted() {
            this.runAnimation();
            //$(this.$refs.full_block).appendTo(this.$root.$el);
        }
    }
</script>

<style lang="scss" scoped>
    @import "./../CustomPopup/CustomEditPopUp";

    .popup-wrapper {
        z-index: 2500;

        .popup {
            height: auto;
        }

        .renamer-btns {
            margin-top: 10px;

            button {
                margin-left: 5px;
            }
        }
    }
</style>