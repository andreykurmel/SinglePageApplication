<script>
    import MainRequest from './RequestData/MainRequest';

    export default {
        components: {
            MainRequest,
        },
        data() {
            return {
                object_id:  this.init_object_id,
                data_request:  this.init_data_request,
            }
        },
        props: {
            init_data_request: Object,
            init_object_id: Number,
            settingsMeta: Object,
        },
        methods: {}
    }
</script>