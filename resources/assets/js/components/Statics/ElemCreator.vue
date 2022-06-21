<template>
    <div>
        <button v-if="!editing" class="btn btn-success btn-sm" @click="editing = true">Add</button>
        <template v-else="">
            <input class="form-control control-width" v-model="str"/>
            <button class="btn btn-primary btn-sm blue-gradient" @click="$emit('save-elem', type, str);editing = false;">
                <i class="glyphicon glyphicon-ok"></i>
            </button>
            <button class="btn btn-default btn-sm" @click="editing = false">
                <i class="glyphicon glyphicon-remove"></i>
            </button>
        </template>
    </div>
</template>

<script>
    export default {
        name: 'ElemCreator',
        data() {
            return {
                editing: false,
                str: '',
            }
        },
        props: {
            type: String,
        },
    }
</script>

<style lang="scss" scoped>
    .control-width {
        width: calc(100% - 100px);
        display: inline-block;

        .btn-sm {
            padding: 3px 7px;
        }
    }
</style>