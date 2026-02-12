<script>
    import UserPlans from './NavbarPopups/UserPlans';
    import InviteModule from './NavbarPopups/InviteModule';
    import ResourcesPopup from './NavbarPopups/ResourcesPopup';
    import ThemeButton from './Buttons/ThemeButton';
    import FolderIconsPath from './MainApp/Object/Folder/FolderIconsPath';

    import {eventBus} from '../app';

    export default {
        name: 'NavbarData',
        mixins: [
        ],
        components: {
            UserPlans,
            ResourcesPopup,
            InviteModule,
            ThemeButton,
            FolderIconsPath
        },
        data() {
            return {
                show_user_popup: false,
                show_resource_popup: location.href.indexOf('opn=ucloud') > -1,
                show_invite: false,
                iconsArray: [],

                isRequestDemo: false,
                email: null,
                subject: null,
                company: null,
                pref_time: null,
                message: null,
                attach: null,
                errors: {},
            }
        },
        props: {
            user: Object,
        },
        computed: {
            envelopeSpecStyle() {
                let style = _.cloneDeep(this.$root.themeButtonStyle);
                style.height = '28px';
                style.width = '40px';
                style.fontSize = '28px';
                style.lineHeight = '28px';
                style.padding = '0';
                return style;
            },
            pricing_link() {
                return this.$root.settingsMeta.app_settings && this.$root.settingsMeta.app_settings['app_pricing_view'] ?
                    this.$root.settingsMeta.app_settings['app_pricing_view']['val']
                    : '';
            },
            benefits_link() {
                return this.$root.settingsMeta.app_settings && this.$root.settingsMeta.app_settings['app_our_benefits'] ?
                    this.$root.settingsMeta.app_settings['app_our_benefits']['val']
                    : '';
            },
        },
        watch: {
            show_user_popup(val) {
                this.emitPopup(val);
            },
            show_resource_popup(val) {
                this.emitPopup(val);
            },
            show_invite(val) {
                this.emitPopup(val);
            },
        },
        methods: {
            sendForm(){
                $.LoadingOverlay('show');
                let formData = new FormData();
                formData.append('email', this.email);
                formData.append('message', this.message);
                formData.append('attach', this.attach);
                formData.append('subject', this.subject);
                formData.append('company', this.company);
                formData.append('pref_time', this.pref_time);
                axios.post('/send-mail', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                ).then(({ data }) => {
                    this.isRequestDemo = false;
                    this.clearingForm();
                    Swal({
                        title: 'Info',
                        text: 'Thanks for your interest. We will get back to you shortly.',
                        timer: 3500
                    });
                }).catch(errors => {

                }).finally(() => $.LoadingOverlay('hide'));
            },
            clearingForm(){
                this.email = null;
                this.subject = null;
                this.company = null;
                this.pref_time = null;
                this.message = null;
                this.attach = null;
                this.errors = {};
                this.$refs.file.value = '';
            },
            requestDemoClick(){
                this.subject = 'Request a demo.';
                this.isRequestDemo = true;
            },
            handleFileUpload(){
                this.attach = this.$refs.file.files[0];
            },

            emitPopup(val) {
                eventBus.$emit('navbar-popup', val);
            },
            openUserPopup() {
                this.show_user_popup = true;

                axios.get('/ajax/settings/fees').then(({ data }) => {
                    this.$root.settingsMeta.all_plans = data.all_plans;
                    this.$root.settingsMeta.all_addons = data.all_addons;
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                }).finally(() => $.LoadingOverlay('hide'));
            },
            changeTableMetaHandler(meta) {},
        },
        mounted() {
            eventBus.$on('change-table-meta', (meta) => {
                this.iconsArray = meta.icons_array;
            });
            eventBus.$on('change-folder-meta', (meta) => {
                this.iconsArray = meta._root_folders;
            });
            eventBus.$on('open-resource-popup', () => {
                this.show_resource_popup = true;
            });

            //open by url param
            if (location.search.match(/^\?subscription/gi)) {
                this.show_user_popup = true;
            }
            if (location.search.match(/^\?settings/gi)) {
                this.show_resource_popup = true;
            }
            if (location.search.match(/^\?invites/gi)) {
                this.show_invite = true;
            }

            //move the 'follow buttons'
            $('#twitter-follow').append( $('.twitter-follow-button') );
            $('#linkedin-follow').append( $('.IN-widget') );
        }
    }
</script>

<style>
    .invite_btn {
        border: 1px solid #AAA;
        padding: 5px 10px;
        border-radius: 50%;
        cursor: pointer;
    }
</style>