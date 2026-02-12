<script>
    export default {
        methods: {
            canSRV(tableMeta) {
                return tableMeta.single_view_active
                    && tableMeta.single_view_permission_id
                    && (!this.$root.user.see_view || this.$root.user.view_all.can_show_srv);
            },
        }
    }
</script>