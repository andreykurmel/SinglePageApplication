<script>
    import MainRequest from './RequestData/MainRequest';

    export default {
        components: {
            MainRequest,
        },
        data() {
            return {
                object_id:  this.init_object_id,
                table_permission:  this.init_table_permission,
            }
        },
        props: {
            init_table_permission: Object,
            init_object_id: Number,
            settingsMeta: Object,
        },
        methods: {}
    }
</script>