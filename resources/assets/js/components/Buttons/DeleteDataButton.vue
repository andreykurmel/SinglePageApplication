<template>
    <span class="btn btn-primary btn-sm blue-gradient"
          @click="$emit('delete-clicked')"
          :style="$root.themeButtonStyle"
    >DD</span>
</template>

<script>
    export default {
        name: "DeleteDataButton",
        mixins: [
        ],
        data: function () {
            return {
            }
        },
        methods: {},
    }
</script>