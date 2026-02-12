<template>
    <div class="full-height setup_wrapper" ref="ckeditor_wrap">
        <div v-if="type === 'email'"
             class="flex flex--center-v flex--space add_link"
             :style="textSysStyleSmart"
             ref="topbuttons"
        >
            <div class="flex flex--center-v">
                <label>Add Field Variable:&nbsp;</label>
                <select style="width: 200px;"
                        class="form-control"
                        @change="addFieldLink()"
                        v-model="field_link_to_add"
                        :disabled="is_disabled"
                        :style="textSysStyle">
                    <option v-for="fld in tableMeta._fields" :value="fld">{{ fld.name }}</option>
                </select>
            </div>

            <div class="flex flex--center-v">
                <label>&nbsp;&nbsp;&nbsp;Insert Linked Data:&nbsp;</label>
                <select style="width: 200px;"
                        class="form-control"
                        @change="linkedDataVariable()"
                        v-model="selected_link_id"
                        :disabled="is_disabled"
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
                        >{{ link.name + ' (' + link.link_type + ')' }}</option>
                    </optgroup>
                </select>

                <template v-if="type === 'email'">
                    <label>&nbsp;&nbsp;&nbsp;View:&nbsp;</label>
                    <select style="width: 200px;"
                            class="form-control"
                            @change="emitUpd()"
                            v-model="targetRow.email_link_viewtype"
                            :disabled="is_disabled"
                            :style="textSysStyle">
                        <option value="table">Grid</option>
                        <option value="vertical">Board</option>
                        <option value="list">List</option>
                    </select>
                </template>

                <ckeditor-settings-button
                    :target-row="targetRow"
                    :is_disabled="is_disabled"
                    style="position: relative; top: 3px; margin-left: 5px;"
                    @updated-ckeditor="emitUpd()"
                ></ckeditor-settings-button>
            </div>
        </div>

        <!--<html-edit-panel :init-text="targetRow[fieldName]"-->
        <!--:table-meta="tableMeta"-->
        <!--style="height: 150px;"-->
        <!--@txt-update="(val) => { targetRow[fieldName] = val; emitUpd() }"-->
        <!--&gt;</html-edit-panel>-->

        <vue-ckeditor
                v-if="draw_ck"
                :config="ck_conf"
                :read-only-mode="is_disabled"
                v-model="targetRow[fieldName]"
                style="color: #222;"
                @blur="emitUpd()"
        ></vue-ckeditor>
    </div>
</template>

<script>
    import CellStyleMixin from "../_Mixins/CellStyleMixin.vue";

    import HtmlEditPanel from "../CustomTable/Specials/HtmlEditPanel";
    import VueCkeditor from "../../../../../node_modules/vue-ckeditor2/src/VueCkeditor";
    import InfoSignLink from "../CustomTable/Specials/InfoSignLink";
    import CkeditorSettingsButton from "../Buttons/CkeditorSettingsButton.vue";
    import TourSettingsButton from "../Buttons/TourSettingsButton.vue";

    export default {
        name: "TabCkeditor",
        mixins: [
            CellStyleMixin,
        ],
        components: {
            TourSettingsButton,
            CkeditorSettingsButton,
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
                    height: 500,
                    toolbarGroups: [
                        { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
                        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
                        '/',
                        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
                        '/',
                        { name: 'links' },
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
        props: {
            tableMeta: Object,
            targetRow: Object,
            fieldName: String,
            type: String,
            is_disabled: Boolean,
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
                return String(name).replace(newRegexp('[^\\p{L}\\d]'),'');
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
                    let link = null;
                    _.each(this.tableMeta._fields, (fld) => {
                        _.each(fld._links, (lnk) => {
                            if (lnk.id == this.selected_link_id) {
                                link = lnk;
                            }
                        });
                    });
                    if (link) {
                        let view = null;
                        switch (this.targetRow.email_link_viewtype) {
                            case 'vertical': view = 'Board'; break;
                            case 'list': view = 'List'; break;
                            default: view = 'Grid'; break;
                        }
                        this.targetRow[this.fieldName] += ' [Link:'
                            + this.prepName(link.name)
                            + '/'
                            + this.prepName(view)
                            + ']';
                        this.emitUpd();
                    }
                }
            },
        },
        mounted() {
            this.$nextTick(() => {
                this.ck_conf.height = $(this.$refs.ckeditor_wrap).outerHeight() - 140
                    - (this.$refs.topbuttons ? $(this.$refs.topbuttons).outerHeight() : 0);
                this.draw_ck = true;
            });
        },
        beforeDestroy() {
        }
    }
</script>

<style lang="scss" scoped>
    .add_link {
        flex-wrap: wrap;
        white-space: nowrap;

        div {
            margin-bottom: 5px;
        }
        label {
            margin: 0;
        }
        select {
            height: 30px;
            padding: 3px 6px;
        }
        input {
            max-width: 65px;
        }
    }
</style>