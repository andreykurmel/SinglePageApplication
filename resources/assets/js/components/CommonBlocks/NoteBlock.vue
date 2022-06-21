<template>
    <div class="note_wrap flex flex--center-v" @click="openEdit">
        <label>{{ note_label }}</label>
        <span v-if="!can_edit">{{ note_content }}</span>
        <textarea v-else="" class="form-control" rows="1" ref="txtarea" v-model="edit_data" @blur="updateNote"></textarea>
    </div>
</template>

<script>
    export default {
        name: "NoteBlock",
        data: function () {
            return {
                can_edit: false,
                edit_data: this.note_content,
            };
        },
        props:{
            note_label: String,
            note_content: String,
        },
        methods: {
            openEdit() {
                this.can_edit = true;
                this.$nextTick(() => {
                    if (this.$refs.txtarea) {
                        this.$refs.txtarea.focus();
                    }
                });
            },
            updateNote() {
                this.$emit('updated-data', this.edit_data);
                this.can_edit = false;
            },
        },
        created() {
        },
    }
</script>

<style lang="scss" scoped>
    .note_wrap {
        background-color: transparent;
        cursor: pointer;

        label {
            margin: 0;
        }

        div {
            height: 36px;
        }
    }
</style>