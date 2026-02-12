<script>
    /**
     *  should be present:
     *
     *  this.idx: Number
     *  ---for resize:
     *  this.getPopupWidth: Number,
     *  this.getPopupHeight: Number,
     *  this.storeSizes(): Function,
     *  */
    export default {
        data: function () {
            return {
                resizerX: 0,
                resizerY: 0,
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
        computed: {
            popupHeightPx() {
                let pophe = (to_float(this.getPopupHeight) || to_float('80%')) / 100 * window.innerHeight;
                if (String(this.getPopupHeight).match(/px/)) {
                    pophe = to_float(this.getPopupHeight);
                }
                return pophe;
            },
        },
        methods: {
            //resize
            dragResizeStart(e) {
                if (this.storeSizes) {
                    e = e || window.event;
                    this.resizerX = e.clientX - this.getPopupWidth;
                    this.resizerY = e.clientY - this.popupHeightPx;
                } else {
                    Swal('Info','Resize is not available!');
                }
            },
            dragResizeDo(e) {
                if (this.storeSizes) {
                    e = e || window.event;
                    if (e.clientX && e.clientY) {
                        this.storeSizes(e.clientX - this.resizerX, e.clientY - this.resizerY);
                    }
                }
            },
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
                this.topPos = this.$root.lastMouseClick.clientY;
                this.leftPos = this.$root.lastMouseClick.clientX;
                this.anim_opac = 0;
                this.transition_ms = 800;
                setTimeout(() => {
                    let positions = this._endPositions();
                    this.topPos = positions.top;
                    this.leftPos = positions.left;
                    this._pamTestPos();
                    this.anim_opac = 1;
                    setTimeout(() => {
                        this.transition_ms = 0;
                        this._pamClear(clear_spec);
                    }, this.transition_ms);
                }, 1);
            },
            noAnimation(clear_spec) {
                let positions = this._endPositions();
                this.topPos = positions.top;
                this.leftPos = positions.left;
                this._pamTestPos();
                this.anim_opac = 1;
                this._pamClear(clear_spec);
            },
            _endPositions() {
                let positions = {
                    top: ((window.innerHeight - this.popupHeightPx) / 2 + (this.idx * 40)),
                    left: ((window.innerWidth - this.getPopupWidth) / 2 + this.idx * 30),
                };

                if (this.shiftObject) {
                    positions.top = this.shiftObject.top_px + (this.idx * 40);
                    positions.left = ((window.innerWidth*this.shiftObject.left - this.getPopupWidth) / 2 + this.idx*30);
                }
                return positions;
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