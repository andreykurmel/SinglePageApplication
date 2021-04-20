<template>
    <div class="full-height setup_wrapper flex flex--col">
        <div class="flex__elem-remain" ref="ckeditor_wrap">
            <div class="flex flex--center-v add_link">
                <label>Add Field Variable:&nbsp;</label>
                <select class="form-control"
                        @change="addFieldLink()"
                        v-model="field_link_to_add"
                        :style="textStyle">
                    <option v-for="fld in tableMeta._fields" :value="fld">{{ fld.name }}</option>
                </select>
            </div>

            <!--<html-edit-panel :init-text="emailSettings.email_body"-->
            <!--:table-meta="tableMeta"-->
            <!--style="height: 150px;"-->
            <!--@txt-update="(val) => { emailSettings.email_body = val; emitUpd() }"-->
            <!--&gt;</html-edit-panel>-->

            <vue-ckeditor
                    v-if="draw_ck"
                    :config="ck_conf"
                    v-model="emailSettings.email_body"
                    @blur="emitUpd()"
            ></vue-ckeditor>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";

    import HtmlEditPanel from "../../../../CustomTable/Specials/HtmlEditPanel";
    import VueCkeditor from "../../../../../../../../node_modules/vue-ckeditor2/src/VueCkeditor";

    export default {
        name: "EmailBody",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            VueCkeditor,
            HtmlEditPanel,
        },
        data: function () {
            return {
                field_link_to_add: null,
                draw_ck: false,
                ck_conf: {
                    allowedContent: true,
                    height: 222,
                    toolbarGroups: [
                        { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
                        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
                        '/',
                        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
                        '/',
                        { name: 'insert', groups: [ 'insert' ] },
                        { name: 'styles' },
                        { name: 'colors' },
                        { name: 'tools' },
                        { name: 'others' },
                        { name: 'about' },
                    ],
                    removeButtons: 'Flash,Iframe,PageBreak',
                },
            }
        },
        props:{
            tableMeta: Object,
            emailSettings: Object,
        },
        computed: {
        },
        methods: {
            emitUpd() {
                this.formula_email_subject = false;
                this.$emit('save-backend');
            },
            recreateFrm(param) {
                this[param] = false;
                this.$nextTick(() => {
                    this[param] = true;
                });
            },
            prepName(name) {
                return String(name).replace(/[^\w\d]/gi,'');
            },
            addFieldLink() {
                if (this.field_link_to_add) {
                    let name = this.field_link_to_add.formula_symbol || this.field_link_to_add.name;
                    this.emailSettings.email_body += ' {' + this.prepName(name) + '}';
                    this.emitUpd();
                }
            },
        },
        mounted() {
            this.ck_conf.height = $(this.$refs.ckeditor_wrap).outerHeight() - 180;
            this.draw_ck = true;
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .setup_wrapper {
        .form-group {
            white-space: nowrap;
            font-size: 1.1em;

            label {
                margin: 0;
            }
        }
    }
    .add_link {
        white-space: nowrap;
        margin-bottom: 10px;

        label {
            margin: 0;
        }
        select {
            max-width: 250px;
            height: 30px;
            padding: 3px 6px;
        }
    }
</style>