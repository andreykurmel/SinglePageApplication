<script>
    /**
     *  Must be present:
     *
     *  this.user: Object
     *  this.tableMeta: Object
     *  */
    export default {
        data: function () {
            return {
            }
        },
        computed: {
            themeTopBgStyle() {
                let bg_color = this.getThemeProp('navbar_bg_color');
                return {backgroundColor: bg_color || '#FFF'};
            },
            themeTableHeaderBgStyle() {
                let bg_color = this.getThemeProp('table_hdr_bg_color') || '#CCC';
                return {
                    background: this.buildCssGradient(bg_color),
                    //border: '1px solid '+bg_color
                }
            },
            themeMainBgStyle() {
                let bg_color = this.getThemeProp('main_bg_color');
                return {backgroundColor: bg_color || '#005fa4'};
            },
            themeRibbonStyle() {
                let bg_color = this.getThemeProp('ribbon_bg_color');
                return {backgroundColor: bg_color};
            },
            themeButtonStyle() {
                return this.btnThStyle();
            },
            themeLightBtnStyle() {
                return this.btnThStyle(0.15, 0.1, 0.05);
            },
            themeButtonBgColor() {
                return this.getThemeProp('button_bg_color');
            },

            themeTextFontColor() {
                return this.getThemeProp('app_font_color') || null;
            },
            themeTextFontSize() {
                return Number(this.getThemeProp('app_font_size') || 12);
            },
            checkBoxStyle() {
                return {
                    width: Math.min(this.themeTextFontSize, 20)+'px',
                    height: Math.min(this.themeTextFontSize, 20)+'px',
                    color: this.themeTextFontColor,
                };
            },
        },
        methods: {
            //inits
            tsmInitOwnerOrEnforced(tableMeta) {
                let tm = tableMeta;
                return tm &&
                    (
                        tm._is_owner
                        ||
                        (tm._current_right && tm._current_right.enforced_theme)
                        ||
                        this.$root.user.see_view
                    );
            },
            tsmInitTableTheme(tableMeta) {
                let tm = tableMeta;
                let theme = {};
                if (this.tsmInitOwnerOrEnforced(tableMeta) && tm._theme) {
                    theme = tm._theme;
                }
                return theme;
            },
            tsmInitUserTheme(tableMeta) {
                let u = this.user;
                let tm = tableMeta;
                let theme = {};
                if (this.tsmInitOwnerOrEnforced(tableMeta) && tm._owner_theme) {
                    theme = tm._owner_theme;
                }
                else
                if (u && u._selected_theme) {
                    theme = u._selected_theme;
                }
                return theme;
            },
            //getters
            themeTableBgColor(tableMeta) {
                return this.getThemeProp('table_hdr_bg_color', tableMeta);
            },
            getThemeProp(prop, tableMeta) {
                let usr_theme = this.tsmInitUserTheme(tableMeta || this.tableMeta);
                let table_theme = this.tsmInitTableTheme(tableMeta || this.tableMeta);
                let selected_prop = (usr_theme[prop] || null);

                return table_theme[prop] // prop from table settings OR
                    || selected_prop; // prop from theme settings (or default)
            },
            buildCssGradient(clr) {
                if (clr) {
                    return 'linear-gradient(to bottom, '
                        + this.clrAdd(clr, 0.32, 0.32, 0.23)
                        + ', '
                        + this.clrAdd(clr, 0.02, 0.02, 0.02)
                        + ' 50%, '
                        + this.clrAdd(clr, -0.02, -0.02, -0.02)
                        + ' 50%, '
                        + this.clrAdd(clr, 0.1, 0.1, 0.15)
                        + ')';
                } else {
                    return null;
                }
            },
            clrAdd(clr, red, green, blue) {
                let c_obj = Color(clr);

                c_obj.red += red;
                c_obj.red = Math.min(c_obj.red, 1);
                c_obj.red = Math.max(c_obj.red, 0);

                c_obj.green += red;
                c_obj.green = Math.min(c_obj.green, 1);
                c_obj.green = Math.max(c_obj.green, 0);

                c_obj.blue += red;
                c_obj.blue = Math.min(c_obj.blue, 1);
                c_obj.blue = Math.max(c_obj.blue, 0);

                return c_obj.toString();
            },
            btnThStyle(add_r, add_g, add_b) {
                let style = {
                    color: '#FFF'
                };
                let bg_color = this.getThemeProp('button_bg_color') || '#004aa2';

                if (bg_color && bg_color.charAt(0) == '#') { //#005fa4
                    if (add_r && add_g && add_b) {
                        bg_color = this.clrAdd(bg_color, add_r, add_g, add_b);
                    }
                    style.background = this.buildCssGradient(bg_color);
                    //style.border = '1px solid '+this.clrAdd(bg_color, -0.3, -0.3, -0.3);
                }
                return style;
            },
        },
    }
</script>