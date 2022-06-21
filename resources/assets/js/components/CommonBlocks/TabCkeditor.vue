<template>
    <div class="full-height setup_wrapper flex flex--col">
        <div class="flex__elem-remain" ref="ckeditor_wrap">
            <div class="flex flex--center-v add_link" :style="textSysStyle">
                <label>Add Field Variable:&nbsp;</label>
                <select class="form-control"
                        @change="addFieldLink()"
                        v-model="field_link_to_add"
                        :style="textSysStyle">
                    <option v-for="fld in tableMeta._fields" :value="fld">{{ fld.name }}</option>
                </select>

                <label>&nbsp;&nbsp;&nbsp;Insert Linked Data:&nbsp;</label>
                <select class="form-control"
                        @change="linkedDataVariable()"
                        v-model="selected_link_id"
                        :style="textSysStyle">
                    <optgroup
                        v-for="fld in tableMeta._fields"
                        v-if="fldHasLinks(fld)"
                        :label="fld.name + ':'"
                    >
                        <option
                            v-for="link in fld._links"
                            v-if="link.link_type === 'Record'"
                            :value="link.id"
                        >{{ link.icon + ' (' + link.link_type + ')' }}</option>
                    </optgroup>
                </select>

                <info-sign-link
                    :use_hover="true"
                    :app_sett_key="'addon_email_body_info'"
                    :hgt="24"
                    style="margin-left: 10px;"
                ></info-sign-link>

                <template v-if="type === 'email'">
                    <label>&nbsp;&nbsp;&nbsp;Table Width:&nbsp;</label>
                    <select class="form-control"
                            @change="emitUpd()"
                            v-model="targetRow.email_link_width_type"
                            :style="textSysStyle">
                        <option value="full">Full Body</option>
                        <option value="content">Fit Content</option>
                        <option value="column_size">Fixed (ea. col.)</option>
                        <option value="total_size">Fixed (Total)</option>
                    </select>

                    <template v-if="targetRow.email_link_width_type === 'column_size' || targetRow.email_link_width_type === 'total_size'">
                        <input class="form-control input-sm"
                               type="number"
                               @change="emitUpd()"
                               v-model="targetRow.email_link_width_size"
                               :style="textSysStyle">
                        <label>px</label>
                    </template>

                    <label>&nbsp;&nbsp;&nbsp;Align:&nbsp;</label>
                    <select class="form-control"
                            @change="emitUpd()"
                            v-model="targetRow.email_link_align"
                            :style="textSysStyle">
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                </template>
            </div>

            <!--<html-edit-panel :init-text="targetRow[fieldName]"-->
            <!--:table-meta="tableMeta"-->
            <!--style="height: 150px;"-->
            <!--@txt-update="(val) => { targetRow[fieldName] = val; emitUpd() }"-->
            <!--&gt;</html-edit-panel>-->

            <vue-ckeditor
                    v-if="draw_ck"
                    :config="ck_conf"
                    v-model="targetRow[fieldName]"
                    @blur="emitUpd()"
            ></vue-ckeditor>
        </div>
    </div>
</template>

<script>
    import CellStyleMixin from "../_Mixins/CellStyleMixin.vue";

    import HtmlEditPanel from "../CustomTable/Specials/HtmlEditPanel";
    import VueCkeditor from "../../../../../node_modules/vue-ckeditor2/src/VueCkeditor";
    import InfoSignLink from "../CustomTable/Specials/InfoSignLink";

    export default {
        name: "TabCkeditor",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            InfoSignLink,
            VueCkeditor,
            HtmlEditPanel,
        },
        data: function () {
            return {
                selected_link_id: null,
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
            targetRow: Object,
            fieldName: String,
            type: String,
        },
        computed: {
        },
        methods: {
            fldHasLinks(fld) {
                return fld._links
                    && fld._links.length
                    && _.find(fld._links, {link_type: 'Record'});
            },
            emitUpd() {
                this.$emit('save-row', this.targetRow);
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
                    this.targetRow[this.fieldName] += ' {' + this.prepName(name) + '}';
                    this.emitUpd();
                }
            },
            linkedDataVariable() {
                if (this.selected_link_id) {
                    let field = null;
                    let link = null;
                    let idxs = { fi:null, li:null };
                    _.each(this.tableMeta._fields, (fld, fi) => {
                        _.each(fld._links, (lnk, li) => {
                            if (lnk.id == this.selected_link_id) {
                                field = fld;
                                link = lnk;
                                idxs.fi = fi+1;
                                idxs.li = li+1;
                            }
                        });
                    });
                    if (field && link) {
                        this.targetRow[this.fieldName] += ' [Link:#' + idxs.li
                            + '/' + this.prepName(link.icon)
                            + '@Col:#' + idxs.fi
                            + '/' + this.prepName(field.name)
                            + ']';
                        this.emitUpd();
                    }
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
            max-width: 150px;
            height: 30px;
            padding: 3px 6px;
        }
        input {
            max-width: 65px;
        }
    }
</style>