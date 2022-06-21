export class WidSettings {

    /**
     *
     * @param obj
     * @param user_id
     */
    constructor(obj, user_id) {
        this.applyProps(obj, user_id);
    }

    /**
     *
     * @param obj
     * @param user_id
     * @param skip_pos
     */
    applyProps(obj, user_id, skip_pos) {
        obj = obj || {};

        if (this._id && !obj._id) {
            return;
        }

        this._id = obj._id;
        this._app_tb = String(obj._app_tb || '');
        this._user_id = user_id;

        this.usergroup = String(obj.usergroup || user_id || ' ');
        this.model = String(obj.model || ' ');
        this.curtab = String(obj.curtab || ' ');

        this.planeXY = obj.planeXY !== undefined ? !!obj.planeXY : false;
        this.planeYZ = obj.planeYZ !== undefined ? !!obj.planeYZ : false;
        this.planeZX = obj.planeZX !== undefined ? !!obj.planeZX : true;
        this.nodes = obj.nodes !== undefined ? !!obj.nodes : true;
        this.nodesName = obj.nodesName !== undefined ? !!obj.nodesName : false;
        this.wireframe = obj.wireframe !== undefined ? !!obj.wireframe : true;
        this.wireframeName = obj.wireframeName !== undefined ? !!obj.wireframeName : false;
        this.members = obj.members !== undefined ? !!obj.members : true;
        this.objects = obj.objects !== undefined ? !!obj.objects : true;
        this.rl_view = obj.rl_view !== undefined ? !!obj.rl_view : true;
        this.rl_size = obj.rl_size !== undefined ? obj.rl_size : 1;
        this.edges_members = obj.edges_members !== undefined ? !!obj.edges_members : false;
        this.edges_eqpts = obj.edges_eqpts !== undefined ? !!obj.edges_eqpts : false;
        this.edges_color = obj.edges_color || '#ffffff';
        this.defaultAngle = obj.defaultAngle !== undefined ? !!obj.defaultAngle : false;
        this.showLabelsEqpt = obj.showLabelsEqpt !== undefined ? !!obj.showLabelsEqpt : false;
        this.fontSize = Number(obj.fontSize) || 20;
        this.frameScale = Number(obj.frameScale) || 3;
        this.skybox = obj.skybox || '';
        this.skyboxColor = obj.skyboxColor || '#ffffff';
        this.terrain = obj.terrain || '';
        this.terrainColor = obj.terrainColor || '#ffffff';
        this.gridSize = obj.gridSize || '1ft';
        this.gridShrink = Number(obj.gridShrink) || 0;
        this.defEqptColor = obj.defEqptColor || '#aaaaaa';
        this.defMemberColor = obj.defMemberColor || '#aaaaaa';
        this.defRLColor = obj.defRLColor || '#aaaaaa';

        if (!skip_pos) {
            this.camera_pos_x = Number(obj.camera_pos_x) || -15;
            this.camera_pos_y = Number(obj.camera_pos_y) || 20;
            this.camera_pos_z = Number(obj.camera_pos_z) || 15;

            this.camera_add1_x = Number(obj.camera_add1_x) || -15;
            this.camera_add1_y = Number(obj.camera_add1_y) || 20;
            this.camera_add1_z = Number(obj.camera_add1_z) || 15;

            this.camera_add2_x = Number(obj.camera_add2_x) || -15;
            this.camera_add2_y = Number(obj.camera_add2_y) || 20;
            this.camera_add2_z = Number(obj.camera_add2_z) || 15;

            this.active_camera = Number(obj.active_camera) || 1;
        }
    }

    /**
     *
     * @param x
     * @param y
     * @param z
     */
    setCameraP(x, y, z) {
        let changed = false;
        let ac = String(this.active_camera) || '1';
        switch (ac) {
            case '1':
                changed = this.camera_pos_x != Number(x).toFixed(2)
                    || this.camera_pos_y != Number(y).toFixed(2)
                    || this.camera_pos_z != Number(z).toFixed(2);
                this.camera_pos_x = Number(x).toFixed(2);
                this.camera_pos_y = Number(y).toFixed(2);
                this.camera_pos_z = Number(z).toFixed(2);
                break;
            case '2':
                changed = this.camera_add1_x != Number(x).toFixed(2)
                    || this.camera_add1_y != Number(y).toFixed(2)
                    || this.camera_add1_z != Number(z).toFixed(2);
                this.camera_add1_x = Number(x).toFixed(2);
                this.camera_add1_y = Number(y).toFixed(2);
                this.camera_add1_z = Number(z).toFixed(2);
                break;
            case '3':
                changed = this.camera_add2_x != Number(x).toFixed(2)
                    || this.camera_add2_y != Number(y).toFixed(2)
                    || this.camera_add2_z != Number(z).toFixed(2);
                this.camera_add2_x = Number(x).toFixed(2);
                this.camera_add2_y = Number(y).toFixed(2);
                this.camera_add2_z = Number(z).toFixed(2);
                break;
        }
        return changed;
    }

    /**
     *
     * @returns {*}
     */
    cameraPosGet() {
        let ac = String(this.active_camera) || '1';
        switch (ac) {
            case '1':
                return {
                    x: this.camera_pos_x,
                    y: this.camera_pos_y,
                    z: this.camera_pos_z,
                };
            case '2':
                return {
                    x: this.camera_add1_x,
                    y: this.camera_add1_y,
                    z: this.camera_add1_z,
                };
            case '3':
                return {
                    x: this.camera_add2_x,
                    y: this.camera_add2_y,
                    z: this.camera_add2_z,
                };
        }
        return null;
    }

    /**
     *
     */
    saveSettings(clone) {
        if (this._user_id && this._app_tb && this.usergroup && this.curtab) {
            this.usergroup = this.usergroup || ' ';
            this.model = this.model || ' ';
            this.curtab = this.curtab || ' ';
            axios.post('?method=save_model', {
                app_table: this._app_tb,
                model: this,
                master_params: clone ? {id: ''} : '',
            }).catch(errors => {
                Swal('', getErrors(errors));
            });
        }
    }

}