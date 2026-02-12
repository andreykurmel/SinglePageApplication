<template>
    <select ref="select2" v-model="init_val" :multiple="is_multiple">
        <option v-if="empty_val"></option>
        <option disabled class="folder-option">// PRIVATE</option>
        <template v-for="node in builded_tree">
            <option v-if="node.is_folder" disabled class="folder-option">
                <!--<span v-for="i in (node.lvl*4)">&nbsp;</span>-->
                {{ node.name + '/' }}
            </option>
            <option v-else="" :value="node.id">
                <!--<span v-for="i in (node.lvl*4)">&nbsp;</span>-->
                {{ node.name + (node._referenced ? ' (' + node._referenced.trim() + ')' : '') }}
            </option>
        </template>
        <option disabled class="folder-option">// PUBLIC</option>
        <template v-for="node in builded_public">
            <option v-if="node.is_folder" disabled class="folder-option">
                <!--<span v-for="i in (node.lvl*4)">&nbsp;</span>-->
                {{ node.name + '/' }}
            </option>
            <option v-else="" :value="node.id">
                <!--<span v-for="i in (node.lvl*4)">&nbsp;</span>-->
                {{ node.name + (node._referenced ? ' (' + node._referenced.trim() + ')' : '') }}
            </option>
        </template>
        <option disabled class="folder-option">// ACCOUNT</option>
        <template v-for="node in builded_account">
            <option v-if="node.is_folder" disabled class="folder-option">
                <!--<span v-for="i in (node.lvl*4)">&nbsp;</span>-->
                {{ node.name + '/' }}
            </option>
            <option v-else="" :value="node.id">
                <!--<span v-for="i in (node.lvl*4)">&nbsp;</span>-->
                {{ node.name + (node._referenced ? ' (' + node._referenced.trim() + ')' : '') }}
            </option>
        </template>
    </select>
</template>

<script>
    import {eventBus} from '../../../app';

    export default {
        name: "SelectWithFolderStructure",
        data: function () {
            return {
                selectParam: {
                    minimumResultsForSearch: 0,
                    width: '100%',
                    dropdownAutoWidth: true,
                    multiple: this.is_multiple,
                },
                init_val: this.cur_val,
                builded_tree: [],
                builded_public: [],
                builded_account: [],
                already_included: [],
            }
        },
        props: {
            for_single_select: Boolean,
            empty_val: Boolean,
            cur_val: String|Number|Array,
            available_tables: Array,
            user: Object,
            is_obj_attr: String,
            only_item: String,
            is_multiple: Boolean,
        },
        methods: {
            putAvailableTablesToPrivate() {
                _.each(_.orderBy(this.available_tables, '__url'), (ava) => {
                    if (! String(ava.__url).match('public.')) {
                        let folder_path = String(ava.__url).replace(location.origin+'/data', '');
                        let a_ref = (ava && ava._referenced ? '@' + ava._referenced : '');
                        this.builded_tree.push({
                            id: ava.id,
                            lvl: 0,
                            is_folder: false,
                            name: a_ref + folder_path + ' / ' + ava.name,
                            _referenced: ava ? ava._referenced : '',
                        });
                    }
                });
            },
            buildFoldersForTables(nodes, lvl, tree_name, folder_path) {
                _.each(nodes, (elem) => {
                    if (elem.li_attr['data-type'] === 'folder') {
                        let f_path = (folder_path ? folder_path + ' / ' : '')
                            + elem.li_attr['data-object'].name;
                        //add Folder and Sub-Folders/Sub-Tables
                        /*tree_name.push({
                            id: '',
                            lvl: lvl,
                            is_folder: true,
                            name: f_path,
                            _referenced: '',
                        });*/
                        if (elem.children && elem.children.length) {
                            this.buildFoldersForTables(elem.children, lvl+1, tree_name, f_path);
                        }
                    } else {
                        //add Table
                        let ref = _.find(this.available_tables, {id: Number(elem.li_attr['data-id'])});
                        if (this.user.id === elem.li_attr['data-user_id'] || (!this.only_item && ref && ref._referenced)) {
                            let id = (this.is_obj_attr
                                ? elem.li_attr['data-object'][this.is_obj_attr]
                                : elem.li_attr['data-id']);

                            this.already_included.push(id);
                            let a_ref = (ref && ref._referenced && String(folder_path).indexOf('@') === -1 ? '@'+ref._referenced+' / ' : '');

                            tree_name.push({
                                id: id,
                                lvl: lvl,
                                is_folder: false,
                                name: a_ref + folder_path + ' / ' + elem.li_attr['data-object'].name,
                                _referenced: ref ? ref._referenced : '',
                            });
                        }
                    }
                });
            },
            pushNotincludedToPublic(tree_name) {
                _.each(_.orderBy(this.available_tables, '__url'), (ava) => {
                    if (!in_array(ava.id, this.already_included)) {
                        let folder_path = String(ava.__url).replace(location.origin+'/data', '');
                        let a_ref = (ava && ava._referenced && String(folder_path).indexOf('@') === -1 ? '@'+ava._referenced : '');
                        tree_name.push({
                            id: ava.id,
                            lvl: 0,
                            is_folder: false,
                            name: a_ref + folder_path + ' / ' + ava.name,
                            _referenced: ava ? ava._referenced : '',
                        });
                    }
                });
            },
        },
        mounted() {
            this.builded_tree = [];
            this.builded_account = [];
            this.builded_public = [];
            if (!this.only_item || this.only_item == 'user') {
                if (this.$root.private_menu_tree) {
                    this.buildFoldersForTables(this.$root.private_menu_tree, 0, this.builded_tree, '');
                } else {
                    this.putAvailableTablesToPrivate();
                }
            }
            if (!this.only_item || this.only_item == 'account') {
                this.buildFoldersForTables(this.$root.account_menu_tree, 0, this.builded_account, '');
            }
            if (!this.only_item || this.only_item == 'public') {
                this.pushNotincludedToPublic(this.builded_public, '');
            }

            this.$nextTick(function () {
                $(this.$refs.select2).select2(this.selectParam)
                    .on('change', (e) => {
                        let val = (this.is_obj_attr ? $(this.$refs.select2).val() : Number($(this.$refs.select2).val()));
                        this.$emit( 'sel-changed', val );
                    })
                    .on('select2:close', (e) => {
                        if (this.$refs.select2 && this.for_single_select) {
                            $(this.$refs.select2).select2('destroy');
                        }
                        this.$emit('sel-closed');
                    });
                $(this.$refs.select2).next().css('height','30px');

                if (this.for_single_select) {
                    $(this.$refs.select2).select2('open');
                }
            });
        },
        beforeDestroy() {
            if (this.$refs.select2 && $(this.$refs.select2).hasClass('select2-hidden-accessible')) {
                $(this.$refs.select2).select2('destroy');
            }
        }
    }
</script>

<style scoped>
    .folder-option {
        color: #000000;
        font-weight: bold;
        background-color: #EEEEEE;
    }
</style>