<template>
    <div class="full-height root" ref="menu_cell" @click="showEdit()">

        <textarea v-if="isEditing()"
                  v-model="localString"
                  @change="updateValue();hideEdit();"
                  ref="inline_input"
                  class="edit form-control"
        ></textarea>

        <div class="content" v-else v-html="showField" :style="{backgroundColor: (this.selected ? '#CFC' : 'transparent')}"></div>

    </div>
</template>

<script>
    import {eventBus} from './../../../app';

    export default {
        name: "RightMenuCell",
        data: function () {
            return {
                editing: false,
                selected: false,
                oldValue: null,
                localString: this.getLocalString()
            }
        },
        props: {
            canEdit: Boolean,
            note_type: String,
            table_id: Number,
        },
        computed: {
            showField() {
                return this.$root.strip_tags( this.$root.nl2br(this.localString) );
            }
        },
        watch: {
            table_id: function(val) {
                this.localString = this.getLocalString();
            }
        },
        methods: {
            getLocalString() {
                return this.note_type === 'notes' ?
                    this.$root.tableMeta.notes :
                    this.$root.tableMeta._user_notes ? this.$root.tableMeta._user_notes.notes : ''
            },
            isEditing() {
                return this.editing && this.canEdit;
            },
            showEdit() {
                if (!this.selected) {
                    this.selected = true;
                } else {
                    //edit cell
                    this.editing = true;
                    if (this.isEditing()) {
                        this.oldValue = this.localString;
                        this.$nextTick(function () {
                            this.$refs.inline_input.focus();
                        });
                    } else {
                        this.editing = false;
                    }
                }
            },
            hideEdit() {
                this.editing = false;
            },
            updateValue() {
                if (this.localString !== this.oldValue) {
                    (this.note_type === 'notes' ? this.ownerNoteChanged() : this.userNoteChanged());
                }
            },
            ownerNoteChanged() {
                this.$root.tableMeta.notes = this.localString;
                this.$nextTick(() => {
                    axios.put('/ajax/table', {
                        table_id: this.$root.tableMeta.id,
                        notes: this.localString
                    }).then(({ data }) => {
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                })
            },
            userNoteChanged() {
                axios.put('/ajax/table/user-note', {
                    table_id: this.$root.tableMeta.id,
                    notes: this.localString
                }).then(({ data }) => {
                }).catch(errors => {
                    Swal('', getErrors(errors));
                });
            },
            hideSelect(e) {
                let container = $(this.$refs.menu_cell);
                if (container.has(e.target).length === 0){
                    this.selected = false;
                }
            }
        },
        mounted() {
            eventBus.$on('global-click', this.hideSelect);
            eventBus.$on('global-keydown', this.hideSelect);
        },
        beforeDestroy() {
            eventBus.$off('global-click', this.hideSelect);
            eventBus.$off('global-keydown', this.hideSelect);
        }
    }
</script>

<style lang="scss" scoped>
    .root {
        overflow: hidden;

        .edit {
            height: 100%;
            resize: none;
            font-size: 1em;
        }
        .content {
            height: 100%;
            padding: 6px 12px;
            overflow: auto;
            align-items: flex-start;
        }
    }
</style>