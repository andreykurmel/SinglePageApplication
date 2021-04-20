<template>
    <input
            class="form-control"
            type="file"
            ref="file_input"
            @change="setFiles()"
    />
</template>

<script>
    export default {
        name: "FilesAttacher",
        data: function () {
            return {
            };
        },
        props:{
            field: String,
        },
        methods: {
            //working with files
            setFiles() {
                this.$emit('set-files', this.field, this.$refs.file_input.files[0]);
                $(this.$refs.file_input).val(null);
            },
        },
    }
</script>