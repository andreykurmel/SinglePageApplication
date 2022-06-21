<template>
    <div class="c2m2-button">
        <label>
            <input :checked="allChecked" @click="toggleAll()" type="checkbox"/> C2M2
        </label>
    </div>
</template>

<script>
    export default {
        name: "C2M2Button",
        data: function () {
            return {
                menu_opened: false,
                cm_fields: ['created_by', 'created_on', 'modified_by', 'modified_on']
            }
        },
        props:{
            tableMeta: Object,
            user: Object
        },
        computed: {
            allChecked() {
                let res = true;
                for (let i in this.tableMeta._fields) {
                    if (this.inArray(this.tableMeta._fields[i].field, this.cm_fields)) {
                        res = res && this.tableMeta._fields[i].is_showed;
                    }
                }
                return res;
            }
        },
        methods: {
            inArray(item, array) {
                return $.inArray(item, array) > -1;
            },
            toggleAll() {
                let set_status = this.allChecked ? 0 : 1;
                let ids = [];
                for (let i in this.tableMeta._fields) {
                    if (this.inArray(this.tableMeta._fields[i].field, this.cm_fields)) {
                        this.tableMeta._fields[i].is_showed = set_status;
                        ids.push(this.tableMeta._fields[i].id);
                    }
                }

                if (this.user.id && ids.length) {
                    axios.put('/ajax/settings/show-columns-toggle', {
                        table_field_ids: ids,
                        is_showed: set_status ? 1 : 0
                    }).then(({ data }) => {
                    }).catch(errors => {
                        Swal('', getErrors(errors));
                    });
                }
            }
        }
    }
</script>

<style scoped>
    .c2m2-button {
        height: 30px;
        display: flex;
        align-items: center;
        font-size: 1.2em;
    }
    .c2m2-button > label {
        cursor: pointer;
        margin: 0;
        padding: 0;
        text-align: center;
    }
    .c2m2-button > label > input {
        position: relative;
        top: 1px;
    }
</style>