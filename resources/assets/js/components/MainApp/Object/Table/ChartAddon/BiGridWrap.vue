<template>
    <div :id="id_val"
         :hash="gs_hash"
         :gs-id="id_val"
         :gs-x="gs_x || 0"
         :gs-y="gs_y || 0"
         :gs-w="gs_wi || 1"
         :gs-h="gs_he || 1"
         class="grid-stack-item"
    >
        <div class="grid-stack-item-content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "BiGridWrap",
        components: {
        },
        data: function () {
            return {}
        },
        props:{
            gs_hash: String,
            gs_x: Number|String,
            gs_y: Number|String,
            gs_wi: Number|String,
            gs_he: Number|String,
        },
        computed: {
            id_val() {
                return this.$options.name + this.gs_hash;
            },
        },
        methods: {
        },
        created() {
        }
    }
</script>

<style lang="scss" scoped>
    .grid-stack-item {
        .grid-stack-item-content {
            overflow: visible;
        }
    }
</style>