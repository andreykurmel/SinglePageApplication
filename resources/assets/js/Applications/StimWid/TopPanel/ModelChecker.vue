<template>
    <div class="fields_checker">
        <div>
            <label>
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="toggleAll()">
                        <i v-if="allChecked" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>All Columns</span>
            </label>
        </div>
        <div v-for="col in columns">
            <label :style="{backgroundColor: (col.checked ? '#CCC;' : '')}">
                <span class="indeterm_check__wrap">
                    <span class="indeterm_check" @click="col.checked = !col.checked">
                        <i v-if="col.checked" class="glyphicon glyphicon-ok group__icon"></i>
                    </span>
                </span>
                <span>{{ col.name }}</span>
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ModelChecker",
        mixins: [],
        data: function () {
            return {}
        },
        computed: {
            allChecked() {
                let all = true;
                _.each(this.columns, (el) => {
                    if (!el.checked) {
                        all = false;
                    }
                });
                return all;
            }
        },
        props:{
            columns: Array, // [ { checked: bool, name: string },... ]
        },
        methods: {
            toggleAll() {
                let new_val = !this.allChecked;
                _.each(this.columns, (el) => {
                    el.checked = new_val;
                });
            }
        },
    }
</script>

<style scoped>
    .fields_checker > div {
        border-bottom: 1px dashed #CCC;
        display: flex;
    }
    .fields_checker > div > label {
        white-space: nowrap;
        font-size: 0.7em;
        margin: 0;
    }
</style>