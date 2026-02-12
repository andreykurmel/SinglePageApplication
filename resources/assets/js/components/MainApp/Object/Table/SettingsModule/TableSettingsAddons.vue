<template>
    <div :style="textSysStyle" class="p5">
        <template v-if="isOwner">
            <div class="input-left-items form-group" :style="{width: max_set_len+'px'}">
                <div class="mb10"><b>Check an item to enable the add-on feature:</b></div>

                <div class="form-group--min m_l flex flex--center-v" v-if="tb_meta.add_map || presentAddress">
                    <div class="m_l" :class="[tb_meta.api_key_mode === 'table' ? 'form-group--min' : '']">
                        <label>Use</label>
                        <select class="form-control l-inl-control"
                                @change="propChanged('api_key_mode')"
                                v-model="tb_meta.api_key_mode"
                                :style="textSysStyle"
                                style="width: 130px;padding: 0;"
                        >
                            <option value="table">Table specific</option>
                            <option value="account">Account</option>
                        </select>
                        <label class="">
                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google API Key</a>
                        </label>
                        <template v-if="tb_meta.api_key_mode === 'account'">
                            <select class="form-control l-inl-control"
                                    @change="propChanged('account_api_key_id')"
                                    v-model="tb_meta.account_api_key_id"
                                    :style="textSysStyle"
                                    style="width: 100px;padding: 0;"
                            >
                                <option :value="null">No API Key</option>
                                <option v-for="(kkey,kk) in $root.user._google_api_keys" :value="kkey.id">{{ kkey.name || ('#'+(kk+1)) }}</option>
                            </select>
                            <span>.</span>
                        </template>
                        <span v-if="tb_meta.api_key_mode === 'table'">Enter below:</span>
                    </div>
                    <div v-if="tb_meta.api_key_mode === 'table'" class="flex flex--center">
                        <input class="form-control l-inl-control"
                               @click="hide_api = false"
                               @change="propChanged('google_api_key');setApidots();hide_api = true;"
                               v-model="hide_api ? api_dots : tb_meta.google_api_key"
                               :style="textSysStyle"/>
                        <button v-if="tb_meta.google_api_key" class="btn btn-danger btn-sm" @click="removeGlApi()">&times;</button>
                        <i class="fa fa-eye" :style="{color: hide_api ? '' : '#F00'}" @click="hide_api = !hide_api"></i>
                        <i class="fa fa-info-circle" ref="fahelp" @click="showHover"></i>
                        <hover-block v-if="help_tooltip"
                                     :html_str="$root.google_help"
                                     :p_left="help_left"
                                     :p_top="help_top"
                                     :c_offset="help_offset"
                                     @another-click="help_tooltip = false"
                        ></hover-block>
                    </div>
                </div>

                <div v-for="addon in $root.settingsMeta.all_addons" v-if="! addon.is_special" class="form-group--min m_l flex flex--center-v">
                    <span class="indeterm_check__wrap pub-check">
                        <span class="indeterm_check"
                              :class="{'disabled': !userHasAddon(addon.code)}"
                              @click="!userHasAddon(addon.code)
                                  ? null
                                  : propChanged(tbAddonKey(addon.code),tb_meta)"
                              :style="checkboxSys"
                        >
                            <i v-if="tb_meta[tbAddonKey(addon.code)]" class="glyphicon glyphicon-ok group__icon"></i>
                        </span>
                    </span>
                    &nbsp;
                    <div class="flex flex--center-v" style="width: 30%;">
                        <input v-if="usrCanEdit"
                               placeholder="Name"
                               class="form-control input-sm"
                               v-model="addon.name"
                               @change="updateAddon(addon)"/>
                        <span v-else>{{ addon.name }}</span>
                        <div v-if="addon.code === 'ai'" class="flex flex--center-v" style="width: 150%">
                            &nbsp;(Key:&nbsp;
                            <select class="form-control input-sm"
                                    @change="propChanged('openai_tb_key_id')"
                                    v-model="tb_meta.openai_tb_key_id"
                                    :style="textSysStyle">
                                <option :value="null"></option>
                                <option v-for="key in $root.user._ai_api_keys" :value="key.id">{{ key.name }}</option>
                            </select>
                            )
                        </div>
                    </div>
                    &nbsp;
                    <input v-if="usrCanEdit"
                           placeholder="Description"
                           class="form-control input-sm"
                           style="width: 70%;"
                           v-model="addon.description"
                           @change="updateAddon(addon)"/>
                    <span v-else style="font-size: 0.9em; color: #777;">{{ addon.description }}</span>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import {eventBus} from "../../../../../app";

import {SpecialFuncs} from "../../../../../classes/SpecialFuncs";

import CellStyleMixin from "./../../../../_Mixins/CellStyleMixin.vue";

import HoverBlock from "./../../../../CommonBlocks/HoverBlock.vue";
import SelectWithFolderStructure from "../../../../CustomCell/InCell/SelectWithFolderStructure";

export default {
    name: 'TableSettingsAddons',
    mixins: [
        CellStyleMixin,
    ],
    components: {
        HoverBlock,
        SelectWithFolderStructure,
    },
    data() {
        return {
            hide_api: true,
            api_dots: '',
            help_tooltip: false,
            help_left: 0,
            help_top: 0,
            help_offset: 0,
            edit_conv_table: false,
        }
    },
    computed: {
        presentAddress() {
            return _.find(this.tb_meta._fields, (hdr) => { return hdr.f_type === 'Address' });
        },
        isOwner() {
            return this.tb_meta._is_owner && !this.type;
        },
        usrCanEdit() {
            return this.$root.user.is_admin || this.$root.user.role_id == 3;
        },
    },
    props: {
        tableMeta: Object,//style mixin
        tb_meta: Object,
        type: String,
        max_set_len: Number,
    },
    methods: {
        updateAddon(addon) {
            if (! addon.name) {
                Swal({ title: 'Info', html: '"Name" is required for addon!' });
            } else {
                this.$root.sm_msg_type = 1;
                axios.post('/ajax/addon/rename', {
                    id: addon.id,
                    name: addon.name,
                    description: addon.description,
                }).then(({ data }) => {
                    //
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => {
                    this.$root.sm_msg_type = 0;
                });
            }
        },
        tbAddonKey(code) {
            return 'add_' + code;
        },
        setApidots() {
            this.api_dots = String(this.tb_meta.google_api_key || '').replace(/./gi, '*');
        },
        showHover(e) {
            let bounds = this.$refs.fahelp ? this.$refs.fahelp.getBoundingClientRect() : {};
            let px = (bounds.left + bounds.right) / 2;
            let py = (bounds.top + bounds.bottom) / 2;
            this.help_tooltip = true;
            this.help_left = px || e.clientX;
            this.help_top = py || e.clientY;
            this.help_offset = Math.abs(bounds.top - bounds.bottom) || 0;
        },
        userHasAddon(code) {
            return _.findIndex(this.$root.user._subscription._addons, {code: code}) > -1;
        },
        propChanged(prop_name, obj, sync_obj) {
            $.LoadingOverlay('show');
            window.setTimeout(() => {
                if (obj) {
                    obj[prop_name] = !obj[prop_name];
                    (sync_obj ? sync_obj[prop_name] = obj[prop_name] : null);
                }

                if (prop_name === 'name') {
                    this.tb_meta.name = SpecialFuncs.safeTableName(this.tb_meta.name);
                }
                this.$emit('prop-changed', prop_name);

                if (prop_name === 'initial_view_id') {
                    eventBus.$emit('save-table-status');
                }
                if (prop_name === 'add_report' || prop_name === 'add_bi') {
                    eventBus.$emit('run-after-load-changes');
                }
                $.LoadingOverlay('hide');
            }, 50);
        },
        removeGlApi() {
            this.tb_meta.google_api_key = '';
            this.propChanged('google_api_key');
            this.setApidots();
        },
    },
    mounted() {
        this.setApidots();
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
@import './../../../../CommonBlocks/TabldaLike';

.m_l {
    margin-left: 20px;
}

.form-group, .form-group--min {
    white-space: nowrap;
    break-inside: avoid;
}

.form-group--min {
    margin-bottom: 5px;
}

.pub-check {
    /*margin: 0 12px 0 0;*/
    position: relative;
}

.input-left-items {
    text-align: left;

    label {
        font-weight: normal;
    }
}

label {
    margin: 0;
}

.tablda-like {
    th {
        padding: 1px 3px;
    }
}
.center {
    text-align: center;
}

.fa-info-circle {
    cursor: pointer;
    padding-left: 5px;
}

.btn-danger {
    line-height: 18px;
    font-size: 2em;
    padding: 5px;
}

select {
    option {
        white-space: nowrap;
        display: block;
        margin: 0;
        font-size: 1em;
        line-height: 1.1em;
    }
}
</style>
<style lang="scss">
.form-group--min {
    .select2-container {
        width: 100%;
        max-width: 100%;
    }
}
</style>