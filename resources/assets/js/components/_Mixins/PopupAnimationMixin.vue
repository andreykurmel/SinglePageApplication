<script>
    /**
     *  should be present:
     *
     *  this.getPopupWidth: Number
     *  this.idx: Number
     *  */
    export default {
        data: function () {
            return {
                topPos: this.$root.lastMouseClick.clientY,
                leftPos: this.$root.lastMouseClick.clientX,
                anim_opac: 0,
                transition_ms: 800,
                anim_transform: 'none',
                topOffset: 0,
                leftOffset: 0,
                zIdx: 1400,
                is_vis: false,
            }
        },
        methods: {
            //additionals
            hideMenu(e) {
                if (this.is_vis && e.keyCode === 27 && this.$root.tablesZidx <= this.zIdx && !this.$root.e__used) {
                    this.hide();
                    this.$root.set_e__used(this);
                }
            },
            //Popup Animation
            getPopupStyle() {
                return {
                    height: this.getPopupHeight || null,
                    width: this.getPopupWidth + (!isNaN(this.getPopupWidth) ? 'px' : ''),
                    top: this.topPos ? this.topPos+'px' : '0px',
                    left: this.leftPos ? this.leftPos+'px' : '0px',
                    opacity: this.anim_opac,
                    transform: this.anim_opac ? this.anim_transform : 'scale(0.1)',
                    transition: this.transition_ms ? 'all '+this.transition_ms+'ms' : 'none',
                    zIndex: this.zIdx || null,
                    margin: 0,
                };
            },
            dragPopSt() {
                this.topOffset = window.event.clientY - this.topPos;
                this.leftOffset = window.event.clientX - this.leftPos;
            },
            dragPopup() {
                if (window.event.clientY && window.event.clientX) {
                    this.topPos = (window.event.clientY - this.topOffset);
                    this.leftPos = (window.event.clientX - this.leftOffset);
                }
            },
            runAnimation(clear_spec) {
                this.is_vis = true;
                let end_top_pos = ((window.innerHeight*0.1) + (this.idx*40));
                let end_left_pos = ((window.innerWidth - this.getPopupWidth) / 2 + this.idx*30);

                if (this.shiftObject) {
                    end_top_pos = this.shiftObject.top_px;
                    end_left_pos = ((window.innerWidth*this.shiftObject.left - this.getPopupWidth) / 2 + this.idx*30);
                }

                this.topPos = this.$root.lastMouseClick.clientY;
                this.leftPos = this.$root.lastMouseClick.clientX;
                this.anim_opac = 0;
                this.transition_ms = 800;
                setTimeout(() => {
                    this.topPos = end_top_pos;
                    this.leftPos = end_left_pos;
                    this._pamTestPos();
                    this.anim_opac = 1;
                    setTimeout(() => {
                        this.transition_ms = 0;
                        this._pamClear(clear_spec);
                    }, this.transition_ms);
                }, 1);
            },
            noAnimation(clear_spec) {
                this.topPos = (window.innerHeight*0.1) + (this.idx*40);
                this.leftPos = (window.innerWidth - this.getPopupWidth) / 2 + this.idx*30;
                this._pamTestPos();
                this.anim_opac = 1;
                this._pamClear(clear_spec);
            },
            _pamTestPos() {
                if (this.topPos < 1) {
                    this.topPos = '0';
                }
                if (this.leftPos < 1) {
                    this.leftPos = '0';
                }
            },
            _pamClear(clear_spec) {
                this.anim_transform = 'none';
                if (clear_spec && typeof clear_spec === 'object') {
                    _.each(clear_spec, (val, prop) => {
                        this[prop] = val;
                    });
                }
            },
        },
    }
</script>