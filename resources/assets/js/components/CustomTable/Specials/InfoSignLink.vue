<template>
    <div class="sign_wrapper flex flex--center" ref="sign_wrapper" :style="{height: (hgt || 40)+'px'}">
        <div v-if="use_hover"
             class="btn btn-primary btn-sm blue-gradient flex flex--center"
             :style="btnStyle"
             @click="showHover"
        >
            <i class="fas fa-info" ref="hover_ref"></i>
            <hover-block v-if="h_tooltip"
                         :html_str="help_link"
                         :p_left="h_left"
                         :p_top="h_top"
                         :c_offset="h_offset"
                         @another-click="h_tooltip = false"
            ></hover-block>
        </div>
        <a v-else
           target="_blank"
           class="btn btn-primary btn-sm blue-gradient flex flex--center"
           :href="help_link || 'javascript:void(0)'"
           :style="btnStyle"
           @contextmenu.prevent="rightClick()"
        >
            <i class="fas fa-info"></i>
        </a>

        <div v-if="show_edit" class="modal-wrapper">
            <div class="modal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">Info/Support Link Settings</div>
                        <div class="modal-body">
                            <label>Get Started Link:</label>
                            <textarea v-model="help_link" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="tableNoteChanged()">Save</button>
                            <button type="button" class="btn btn-default" @click="show_edit = false">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "InfoSignLink",
        mixins: [
        ],
        data: function () {
            return {
                show_edit: false,
                help_link: null,

                h_tooltip: false,
                h_left: 0,
                h_top: 0,
                h_offset: 0,
            }
        },
        props:{
            app_sett_key: String,
            hgt: Number,
            use_hover: Boolean,
        },
        computed: {
            btnStyle() {
                let style = _.cloneDeep(this.$root.themeButtonStyle);
                style.height = '25px';
                style.width = '25px';
                style.borderRadius = '50%';
                style.marginTop = '1px';
                return style;
            },
        },
        methods: {
            showHover(e) {
                let bounds = this.$refs.hover_ref ? this.$refs.hover_ref.getBoundingClientRect() : {};
                let px = (bounds.left + bounds.right) / 2;
                let py = (bounds.top + bounds.bottom) / 2;
                this.h_tooltip = true;
                this.h_left = px || e.clientX;
                this.h_top = py || e.clientY;
                this.h_offset = Math.abs(bounds.top - bounds.bottom) || 0;
            },
            rightClick() {
                if (this.$root.user.is_admin || this.$root.user.role_id == 3) {
                    this.show_edit = true;
                }
            },
            //save changes
            tableNoteChanged() {
                $.LoadingOverlay('show');
                axios.put('/ajax/app/settings', {
                    app_key: this.app_sett_key,
                    app_val: this.help_link
                }).then(({ data }) => {
                    if (this.$root.settingsMeta.app_settings[this.app_sett_key]) {
                        this.$root.settingsMeta.app_settings[this.app_sett_key].val = this.help_link;
                    }
                    this.show_edit = false;
                }).catch(errors => {
                    Swal('', getErrors(errors));
                }).finally(() => {
                    $.LoadingOverlay('hide');
                });
            },
        },
        mounted() {
            if (this.$root.settingsMeta.app_settings[this.app_sett_key]) {
                this.help_link = this.$root.settingsMeta.app_settings[this.app_sett_key].val;
            }
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .sign_wrapper {
        a {
            font-size: 18px;
            color: #777;
        }

        .modal-header {
            color: #FFF;
            background: #444;
            font-size: 18px;
            font-weight: bold;
        }
    }

    .modal-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        z-index: 1200;
        background: rgba(0, 0, 0, 0.45);

        .modal {
            display: block;
            top: 50%;
            transform: translateY(-30%);
            margin: 0 auto;

            .btn-success {
                background-color: #2ab27b !important;
            }

            .close-modal {
                font-size: 2.5em;
                line-height: 0.8em;
                cursor: pointer;
            }

        }
    }
</style>