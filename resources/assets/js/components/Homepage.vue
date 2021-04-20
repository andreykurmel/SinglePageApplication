
<script>
    export default {
        name: "Home",
        components: {
        },
        props: {
            init_link: String
        },
        data() {
            return{
                isContact: false,
                email: null,
                subject: null,
                message: null,
                attach: null,
                errors: {},
                hash: null,
                data_link: null,
            }
        },
        methods:{
            sendForm(){
                $.LoadingOverlay('show');
                let formData = new FormData();
                formData.append('email', this.email);
                formData.append('message', this.message);
                formData.append('attach', this.attach);
                formData.append('subject', this.subject);
                axios.post('/send-mail', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
                ).then(({ data }) => {
                    this.isContact = false;
                    this.clearingForm();
                    Swal({
                        title: 'Thanks for your message. We will get back to your shortly.',
                        text: '',
                        timer: 3500
                    });
                }).catch(errors => {

                }).finally(() => $.LoadingOverlay('hide'));
            },
            clearingForm(){
                this.email = null;
                this.subject = null;
                this.message = null;
                this.attach = null;
                this.errors = {};
                this.$refs.file.value = '';
            },
            contactClick(){
                this.isContact = !this.isContact;
            },
            handleFileUpload(){
                this.attach = this.$refs.file.files[0];
            },
        },
        mounted(){
            $('head title').html(this.$root.app_name+': Table + Data + Apps');

            $('.mainbag').viewScroller({
                loopMainViews: true,
                animSpeedMainView: 1500,
                afterChange: () => {
                    this.hash = window.location.hash;
                },
            });

            if (window.screen.width >= 768) {
                this.data_link = this.init_link;
            }
        }
    }
</script>