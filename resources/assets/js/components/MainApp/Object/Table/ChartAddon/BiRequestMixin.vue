
<script>
    import {SpecialFuncs} from '../../../../../classes/SpecialFuncs';

    export default {
        methods: {
            //requests
            getRequestParams(chartSettings, tableMeta, defaultParams) {
                let request_opt;
                switch (chartSettings.dataset.type)
                {
                    case 'all_table':
                        request_opt = {
                            table_id: tableMeta.id,
                            page: 1,
                            rows_per_page: 0,
                        };
                        break;

                    default:
                        request_opt = _.cloneDeep(defaultParams);
                        request_opt.page = 1;
                        request_opt.rows_per_page = tableMeta.rows_per_page || 0;
                        break;
                }
                request_opt.special_params = SpecialFuncs.specialParams();
                return request_opt;
            },
        },
    }
</script>