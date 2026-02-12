<script>
/**
 * this.selAi - Object should be present
 */
export default {
    methods: {
        aiModuleStyle() {
            if (! this.selAi) {
                return {};
            }

            let stl = {
                color: this.selAi.txt_color,
                background: this.selAi.bg_color,
                fontFamily: this.selAi.font_family,
                fontWeight: 'normal',
                fontSize: (Number(this.selAi.font_size) || 14) + 'px',
                fontStyle: null,
                textDecoration: null,
            };

            _.each(this.selAi.font_style, (f) => {
                (f === 'Italic' ? stl.fontStyle = 'italic' : stl.fontStyle = stl.fontStyle || null);
                (f === 'Bold' ? stl.fontWeight = 'bold' : stl.fontWeight = stl.fontWeight || null);
                (f === 'Strikethrough' ? stl.textDecoration = 'line-through' : stl.textDecoration = stl.textDecoration || null);
                (f === 'Overline' ? stl.textDecoration = 'overline' : stl.textDecoration = stl.textDecoration || null);
                (f === 'Underline' ? stl.textDecoration = 'underline' : stl.textDecoration = stl.textDecoration || null);
            });

            return stl;
        },
        removeMessage(idx, msgId) {
            if (idx > -1) {
                this.selAi._ai_messages.splice(idx, 1);
            } else {
                this.selAi._ai_messages = [];
            }

            axios.delete('/ajax/addon-ai/messages', {
                params: {
                    model_id: this.selAi.id,
                    direct_id: msgId || null,
                }
            }).catch(errors => {
                Swal('Info', getErrors(errors));
            });
        },
    },
}
</script>