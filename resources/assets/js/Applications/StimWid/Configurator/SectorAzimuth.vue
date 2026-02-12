<template>
    <canvas ref="sector_azm" :width="size" :height="size"></canvas>
</template>

<script>

export default {
    name: 'SectorAzimuth',
    mixins: [],
    components: {},
    data() {
        return {}
    },
    computed: {},
    props: {
        azimuth: {
            type: String | Number,
            required: true,
        },
        size: {
            type: Number,
            default: 20,
        },
        color: {
            type: String,
            default: '#000000',
        }
    },
    watch: {
        azimuth(val) {
            this.init();
        },
        color(val) {
            this.init();
        },
    },
    methods: {
        init() {
            if (!this.$refs.sector_azm) {
                return;
            }
            let ctx = this.$refs.sector_azm.getContext('2d');

            let ctr_x = this.size / 2;
            let ctr_y = this.size / 2;

            let radius = (this.size - 2) / 2;

            let azimuth = Number(this.azimuth) - 90;//set 0 as arrow to the top
            azimuth *= Math.PI / 180;//to radians

            let fromx = ctr_x - radius * Math.cos(azimuth);
            let fromy = ctr_y - radius * Math.sin(azimuth);

            let tox = ctr_x + radius * Math.cos(azimuth);
            let toy = ctr_y + radius * Math.sin(azimuth);

            this.drawArrow(ctx, fromx, fromy, tox, toy, 1, this.color);
            ctx.strokeStyle = this.color;
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(ctr_x, ctr_y, radius, 0, 2 * Math.PI);
            ctx.stroke();
        },
        drawArrow(ctx, fromx, fromy, tox, toy, arrowWidth, color) {
            //letiables to be used when creating the arrow
            let headlen = this.size / 4;
            let angle = Math.atan2(toy - fromy, tox - fromx);

            ctx.save();
            ctx.strokeStyle = color;
            ctx.fillStyle = color;

            //starting path of the arrow from the start square to the end square
            //and drawing the stroke
            ctx.beginPath();
            ctx.moveTo(fromx, fromy);
            ctx.lineTo(tox, toy);
            ctx.lineWidth = arrowWidth;
            ctx.stroke();

            //starting a new path from the head of the arrow to one of the sides of
            //the point
            ctx.beginPath();
            ctx.moveTo(tox, toy);
            ctx.lineTo(tox - headlen * Math.cos(angle - Math.PI / 7),
                toy - headlen * Math.sin(angle - Math.PI / 7));

            //path from the side point of the arrow, to the other side point
            ctx.lineTo(tox - headlen * Math.cos(angle + Math.PI / 7),
                toy - headlen * Math.sin(angle + Math.PI / 7));

            //path from the side point back to the tip of the arrow, and then
            //again to the opposite side point
            ctx.lineTo(tox, toy);
            ctx.lineTo(tox - headlen * Math.cos(angle - Math.PI / 7),
                toy - headlen * Math.sin(angle - Math.PI / 7));

            ctx.closePath();
            ctx.fill();

            //draws the paths created above
            ctx.stroke();
            ctx.restore();
        },
    },
    mounted() {
        this.init();
    },
    beforeDestroy() {
    }
}
</script>

<style lang="scss" scoped>
</style>