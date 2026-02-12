<script>
/**
 * Required:
 * - mapElem: MapTable/MapRefCond
 * - canvas_x: Number
 * - canvas_y: Number
 */
export default {
    data() {
        return {
            initX: 0,
            initY: 0,
            dragOffsetX: 0,
            dragOffsetY: 0,
        }
    },
    watch: {
        'mapElem.position'(newVal, oldVal) {
            this.setSizes();
        },
    },
    methods: {
        percentOffsetFromMiddle() {
            return Math.abs(this.canvas_y/2 - this.initY) / this.canvas_y;
        },
        setSizes() {
            this.initX = this.canvas_x * this.mapElem.position.pos_x / 100;
            this.initY = this.canvas_y * this.mapElem.position.pos_y / 100;
        },
        getPosition() {
            return {
                left: this.initX + 'px',
                top: this.initY + 'px',
            };
        },
        dragMapStart(e) {
            e = e || window.event;
            this.dragInitX = e.clientX - this.initX;
            this.dragInitY = e.clientY - this.initY;
        },
        dragMapDo(e) {
            e = e || window.event;
            if (e.clientX && e.clientY) {
                this.initX = e.clientX - this.dragInitX;
                this.initY = e.clientY - this.dragInitY;
            }
        },
        dragMapEnd() {
            this.mapElem.position.pos_x = this.initX / this.canvas_x * 100;
            this.mapElem.position.pos_y = this.initY / this.canvas_y * 100;
            this.mapElem.position.changed = 1;
            this.mapElem.positionToBackend();
            this.$emit('position-was-updated');
        },
    },
}
</script>