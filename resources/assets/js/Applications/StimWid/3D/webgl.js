(function (exports) {
    var dom, element;
    var scene, camera, renderer, controls;
    var light,light1,light2,light3, raycaster;

    var DOMaxesHelper, rendererAxesHelper, axesHelperScene, axesHelperContainer, axesHelperCamera, axesHelper;

    var ais;
    var gridXY, gridYZ, gridZX;
    var Axises;
    var nodes = [];

    var viewSettings = {};


    var LAST;
    var INTERSECT_ARR = [];
    var mouse = new THREE.Vector3();

    var onDistCalc;
    var onSelected;
    var onRigthClickSelect;
    var onCameraUpdate;

    var cameraHUD, sceneHUD;
    var hudCanvas, hudBitmap;

    var lastCameraType = '';
    var lastZoom = 0;

    var shape = 'shape'; // To be changed according to web selection.

    var terrain;

    var present_mesh = {
        node: [],
        eqpt: [],
        member: [],
    };
    var rl_meshes = [];

    /**
     *
     * @param obj
     * @param type
     * @returns {boolean}
     */
    function isPresent(obj, type) {
        let tt = 'eqpt';
        if (String(type).indexOf('node') > -1) {
            tt = 'node';
        }
        if (String(type).indexOf('member') > -1 || String(type).indexOf('line') > -1 || String(type).indexOf('memberLabel') > -1) {
            tt = 'member';
        }
        let arr = present_mesh[tt] || [];

        if (obj._row_hash) {
            return arr.indexOf(obj._row_hash) > -1;
        } else {
            return false;
        }
    }

    /**
     *
     * @param row_hash
     * @param type
     * @returns {*}
     */
    function setCached(row_hash, type) {
        present_mesh[type] = present_mesh[type] || [];
        if ( present_mesh[type].indexOf(row_hash) === -1 ) {
            present_mesh[type].push(row_hash);
        }
        return row_hash;
    }

    /**
     *
     * @param new_cached
     */
    function clearCached(new_cached) {
        present_mesh.node = _.filter(present_mesh.node || [], (node_hash) => {
            return (new_cached.node || []).indexOf(node_hash) > -1;
        });
        present_mesh.eqpt = _.filter(present_mesh.eqpt || [], (node_hash) => {
            return (new_cached.eqpt || []).indexOf(node_hash) > -1;
        });
        present_mesh.member = _.filter(present_mesh.member || [], (node_hash) => {
            return (new_cached.member || []).indexOf(node_hash) > -1;
        });
    }

    /**
     *
     * @param figure
     * @param camera
     */
    function adaptFigureScale(figure, camera){
        let distance = camera.position.distanceTo(figure.position);
        if(figure.type === 'nodeLabel' || figure.type === 'memberLabel' || figure.type === 'equipmentLabel'){

            if(distance > 15) {
                distance = 15;
            } else if(distance < 3) {
                distance = 3;
            }

            let size = (distance/15) * (viewSettings.fontSize/20);
            figure.scale.set(size * 2, size, size);

        } else if (figure.type === 'node') {
            if (distance < 20) {
                if (distance < 1) distance = 1;
                let size = distance / 20;
                figure.scale.set(size, size, size);
            } else {
                figure.scale.set(1, 1, 1);
            }
        }
    }

    function AdjustFrameScale(frame, scale) {
        if(frame) frame.scale.set(scale, scale, scale);
    }

    function EnableAxesMinimap(selector) {
        DOMaxesHelper = document.querySelector(selector);
        rendererAxesHelper = new THREE.WebGLRenderer({antialias: true, alpha: true});
        rendererAxesHelper.setSize(DOMaxesHelper.offsetWidth, DOMaxesHelper.offsetHeight);

        axesHelperScene = new THREE.Scene();

        axesHelperCamera = new THREE.PerspectiveCamera( 30, DOMaxesHelper.offsetWidth / DOMaxesHelper.offsetHeight, 0.1, 1000 );
        axesHelperCamera.position.set(0, 0, 0.6);
        axesHelperScene.add(axesHelperCamera);

        var light = new THREE.AmbientLight( 0xCCCCCC ); // soft white light
        axesHelperScene.add( light );


        axesHelperContainer = new THREE.Object3D();
        axesHelperScene.add(axesHelperContainer);

        drawNorth();

        DOMaxesHelper.appendChild(rendererAxesHelper.domElement);
    }

    function changeCamera(orto_param) {
        controls.reset();
        if (orto_param) {
            camera.toOrthographic();
            camera.setZoom(1000);
            switch (orto_param) {
                //Important NOTE: camera.rotation will be overwritten by controls.update(), so we had to use 'position' to change 'rotation' via this Vector.
                //In 'top' case we have camera.position at [0,20,0] with looking at [0,0,0] and camera.up = [0,1,0]. So we cannot get x/y axis direction (all = 0)
                //And moving position.x to negative we will get positive X vector for camera.up detection.
                //Referenced code: camera.lookAt( camera.position, controls.target, camera.up ) //resources/assets/js/Applications/StimWid/3D/lib/threejs/three.js
                case 'top': camera.position.set(-0.0001,20,0); break;
                case 'bot': camera.position.set(0.0001,-20,0); break;
                case 'front': camera.position.set(0,0,20); break;
                case 'back': camera.position.set(0,0,-20); break;
                case 'left': camera.position.set(20,0,0); break;
                case 'right': camera.position.set(-20,0,0); break;
            }
        } else {
            camera.toPerspective();
            camera.setZoom(1);
        }
    }

    function Init(mode) {
        raycaster = new THREE.Raycaster();

        renderer = new THREE.WebGLRenderer({antialias: true, alpha: false, preserveDrawingBuffer: true});

        renderer.setSize(dom.width, dom.height);
        renderer.setClearColor("#ffffff");
        renderer.autoClear = false;

        window.scene = scene = new THREE.Scene();

        camera = new THREE.CombinedCamera(dom.width / 2, dom.height / 2, 45, 0.1, 50000, -500, 50000);
        //camera = new THREE.OrthographicCamera( dom.offsetWidth / - 2, dom.offsetWidth / 2, dom.offsetHeight / 2, dom.offsetHeight / - 2, 1, 1000 );
        // THREE.CombinedCamera = function ( width, height, fov, near, far, orthoNear, orthoFar)

        // camera.setZoom(400);
        // camera.toOrthographic();

        if(mode === 'wid') EnableAxesMinimap('#DOMaxesHelper');

        camera.position.set(6, 6, 6); // (2ft,2ft,2ft), zoomscale: pixles/ft
        //camera.rotation.y = Math.PI/2;
        scene.add(camera);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableRotate = false;
        controls.enablePan = false;
        controls.enableKeys = false;

        controls.addEventListener('change', function (e) {
            scene.traverse(function (child) {
                if (child instanceof THREE.Sprite || THREE.Sphere) {
                    adaptFigureScale(child, camera);
                }
            });

            onCameraUpdate && onCameraUpdate(camera.position);

        });

        // light = new THREE.DirectionalLight("#ffffff", 0.25);
        // light.position.set(1.0, 1.0, 1.0).normalize(); // may need lights at other corners
        // camera.add(light);

        light2 = new THREE.HemisphereLight(0xcccccc, 0xcccccc, 0.2);
        light2.name = "Hemisphere";
        light2.visible = true;
        light2.position.set(0, 1, 0);
        scene.add(light2);

        // light3 = new THREE.DirectionalLight("#ffffff", 0.5);
        // light3.position.set(1.0, 1.0, 1.0).normalize(); // may need lights at other corners
        // scene.add(light);

        light3 = new THREE.AmbientLight( 0x999999 ); // soft white light
        scene.add( light3 );

        ais = new THREE.Mesh();
        scene.add(ais);

        gridZX = new THREE.GridHelper(12, 1); // grid line spacing is fixed to be 1/12 ft = 1 inch
        gridZX.name = 'gridZX';
        gridZX.visible = true;
        scene.add(gridZX);

        gridXY = new THREE.GridHelper(12, 1);
        gridXY.name = 'gridXY';
        gridXY.rotation.x = 90 * Math.PI / 180;
        gridXY.visible = false;
        scene.add(gridXY);

        gridYZ = new THREE.GridHelper(12, 1);
        gridYZ.name = 'gridYZ';
        gridYZ.rotation.z = 90 * Math.PI / 180;
        gridYZ.visible = false;
        scene.add(gridYZ);

        if(skyBox) {
            skyBox.visible = false;

            scene.add( skyBox );
        }

        if(Terrain) {
            terrain = new Terrain("../../../assets/img/textures/grass/" + 'grass', false);
            scene.add( terrain.TerrainPlatter );
        }

        // Axes();
        drawAllAxes();
        Event(mode);

        dom.appendChild(renderer.domElement);
        webgl._initialized = true;
    }

    function ChangeGridSettings(settings) {
        if (!settings || !scene) {
            return;
        }

        var size = viewSettings.gridSize;
        var grid_size = 12;
        var step = 1;

        switch (size) {
            case '1in':
                step = 1/12;
                break;
            case '3in':
                step = 1/4;
                break;
            case '6in':
                step = 1/2;
                break;
            case '1ft':
                step = 1;
                break;
            default:
                break;
        }

        scene.remove(scene.getObjectByName('gridZX'));
        scene.remove(scene.getObjectByName('gridYZ'));
        scene.remove(scene.getObjectByName('gridXY'));

        gridZX = new THREE.GridHelper(grid_size, step);
        gridZX.name = 'gridZX';
        gridZX.visible = viewSettings.planeZX;
        scene.add(gridZX);

        gridXY = new THREE.GridHelper(grid_size, step);
        gridXY.name = 'gridXY';
        gridXY.rotation.x = 90 * Math.PI / 180;
        gridXY.visible = viewSettings.planeXY;
        scene.add(gridXY);

        gridYZ = new THREE.GridHelper(grid_size, step);
        gridYZ.name = 'gridYZ';
        gridYZ.rotation.z = 90 * Math.PI / 180;
        gridYZ.visible = viewSettings.planeYZ;
        scene.add(gridYZ);

        // var max = Math.abs(Math.max(box.size().x, box.size().y, box.size().z));

        // if (max < Infinity) {
        //     if (camera.inPerspectiveMode) {
        //         camera.position.y = max*2;
        //         controls.target.set(0, box.size().y / 2, 0);
        //
        //         gridZX.scale.set(max, max, max);
        //         gridXY.scale.set(max, max, max);
        //         gridYZ.scale.set(max, max, max);
        //
        //         Axises.scale.set(max, max, max);
        //     } else if (camera.inOrthographicMode) {
        //         camera.setZoom(camera.zoom / max);
        //         controls.target.set(0, 0, 0);
        //     }
        // }
    }

    function ChangeCameraPosition(x, y, z){
        camera.position.set(x, y, z);
    }

    function GetCurentScreenshotURL() {
        return renderer.domElement.toDataURL("image/png");
    }

    function ChangeViewSettingsWLSC(settings) {
        viewSettings = settings;
        controls.enableRotate = settings.rotation || false;
        controls.enablePan = settings.pan || false;
    }

    function ChangeViewSettingsWID(settings) {
        if (!settings || !scene) {
            return;
        }

        viewSettings = settings;

        viewSettings.fontSize = viewSettings.fontSize || 20;

        scene.traverse(function (child) {
            if (child instanceof THREE.Sprite) {
                adaptFigureScale(child, camera);
            }
        });

        if(viewSettings.defaultAngle){
            camera.position.set(-5,8,6);
        }

        if (viewSettings.frameScale){
            AdjustFrameScale(Axises, viewSettings.frameScale);

            scene.traverse(function (child) {
                if (child.type === 'axises_help') {
                    AdjustFrameScale(child, viewSettings.frameScale);
                }
            });
        }

        if(viewSettings.skybox === 'skyboxColorPicker'){
            let materialArray = [];
            for (let i = 0; i < 6; i++) {
                materialArray.push(new THREE.MeshBasicMaterial({
                    side: THREE.BackSide,
                    color: Number.parseInt(viewSettings.skyboxColor.replace("#", "0x"), 16)
                }));
            }

            skyBox.material = new THREE.MeshFaceMaterial(materialArray);
            skyBox.visible = true;
        }

        if(viewSettings.skybox && skyBox && viewSettings.skybox !== 'skyboxColorPicker') {
            var materialArray = [];
            var imagePrefix = "../../../assets/img/textures/" + viewSettings.skybox + "/sky_";
            var directions = ["posX", "negX", "posY", "negY", "posZ", "negZ"];
            var imageSuffix = ".jpg";

            for (var i = 0; i < 6; i++) {
                materialArray.push(new THREE.MeshBasicMaterial({
                    map: THREE.ImageUtils.loadTexture(imagePrefix + directions[i] + imageSuffix),
                    side: THREE.BackSide
                }));
            }

            skyBox.material = new THREE.MeshFaceMaterial(materialArray);
            skyBox.visible = true;
        } else {
            if(!viewSettings.skybox && skyBox) {
                skyBox.visible = false;
            }
        }

        if(viewSettings.terrain === 'terrainColorPicker'){
            terrain.TerrainPlatter.material = new THREE.MeshPhongMaterial({
                color: Number.parseInt(viewSettings.terrainColor.replace("#", "0x"), 16),
                shading: THREE.SmoothShading
            });
            terrain.TerrainPlatter.visible = true;
        }

        if(viewSettings.terrain && viewSettings.terrain !== 'terrainColorPicker') {
            var gt = THREE.ImageUtils.loadTexture("../../../assets/img/textures/" + viewSettings.terrain);

            terrain.TerrainPlatter.material = new THREE.MeshPhongMaterial({map: gt, shading: THREE.SmoothShading});
            terrain.TerrainPlatter.material.map.repeat.set(64, 64);
            terrain.TerrainPlatter.material.map.wrapS = terrain.TerrainPlatter.material.map.wrapT = THREE.RepeatWrapping;
            terrain.TerrainPlatter.visible = true;
        } else {
            if(!viewSettings.terrain){
                terrain.TerrainPlatter.visible = false;
            }
        }

        scene.traverse(function (child) {
            if (child instanceof THREE.EdgesHelper) {
                child.visible = (child._sett_type === 'eqpts' && viewSettings.edges_eqpts && viewSettings.objects)
                    || (child._sett_type === 'member' && viewSettings.edges_members && viewSettings.members);
            }
        });

        controls.enableRotate = true;
        controls.enablePan = true;
        // controls.reset();

        scene.getObjectByName('gridZX').visible = viewSettings.planeZX;
        scene.getObjectByName('gridYZ').visible = viewSettings.planeYZ;
        scene.getObjectByName('gridXY').visible = viewSettings.planeXY;

        scene.traverse(function (child) {
            if (child instanceof THREE.Sprite) {
                if (child.type == 'nodeLabel') {
                    child.visible = viewSettings.nodesName;
                }

                if (child.type == 'memberLabel') {
                    child.visible = viewSettings.wireframeName;
                }

                if(child.type === 'equipmentLabel'){
                    child.visible = viewSettings.showLabelsEqpt;
                }
            }

            if (child instanceof THREE.Mesh) {
                if (child.type == 'node') {
                    child.visible = viewSettings.nodes;
                }

                if (child.type == 'member') {
                    child.visible = viewSettings.members;
                }

                if (child.type == 'equipment') {
                    child.visible = viewSettings.objects;
                }

                if (child.single_type == 'rl_bracket') {
                    child.visible = !!viewSettings.rl_view;
                    var $clr = Number( String(child.color_lc).replace('#', '0x'), 16);
                    child.material.color.setHex( $clr );
                }
            }

            if (child instanceof THREE.Line) {
                if (child.type == 'line') {
                    child.visible = viewSettings.wireframe;
                }
            }
        });
    }

    function ChangeViewSettingsDPOSS(settings) {
        viewSettings = settings;

        viewSettings.planeZX = viewSettings.grid.zx;
        viewSettings.planeXY = viewSettings.grid.xy;
        viewSettings.planeYZ = viewSettings.grid.yz;

        ais.traverse(function (child) {
            if (child instanceof THREE.EdgesHelper) {
                child.visible = settings.wireframe;
            }
        });

        // if (lastCameraType == settings.camera.type) {
        //     return false;
        // }

        lastCameraType = settings.camera.type;

        if (settings.camera.type == '2D') {
            document.getElementById("2d").style.display = "block";

            ais.visible = false;

            controls.enableRotate = false;
            controls.enablePan = false;
            controls.reset();

            // camera.setZoom(400);
            camera.toOrthographic();

            scene.getObjectByName('gridZX').visible = false;
            scene.getObjectByName('gridYZ').visible = false;
            scene.getObjectByName('gridXY').visible = false;

            //camera.position.set(2*zoomscale, 0*zoomscale, 0*zoomscale);

        } else if (settings.camera.type == '3D') {
            document.getElementById("2d").style.display = "none";

            ais.visible = true;

            controls.enableRotate = true;
            controls.enablePan = true;
            // controls.reset();

            // camera.setZoom(1);
            camera.toPerspective();

            scene.getObjectByName('gridZX').visible = viewSettings.grid.zx;
            scene.getObjectByName('gridYZ').visible = viewSettings.grid.yz;
            scene.getObjectByName('gridXY').visible = viewSettings.grid.xy;

            //camera.position.set(3, 3, 3);
            //camera.position.set(2*zoomscale, 0*zoomscale, 0*zoomscale);
        }

        AspectDPoSS();
    }

    function Axes(params) {
        params = params || {};

        var axisRadius = params.radius !== undefined ? params.radius : 0.005; // 0.02
        var axisLength = params.length !== undefined ? params.length : 2; // 2
        var axisTess = params.tess !== undefined ? params.tess : 12;

        var axisXMaterial = new THREE.MeshBasicMaterial({color: 0xFF0000});
        var axisYMaterial = new THREE.MeshBasicMaterial({color: 0x0000FF});
        var axisZMaterial = new THREE.MeshBasicMaterial({color: 0x00FF00});

        axisXMaterial.side = THREE.DoubleSide;
        axisYMaterial.side = THREE.DoubleSide;
        axisZMaterial.side = THREE.DoubleSide;

        var axisX = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisXMaterial
        );
        var axisY = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisYMaterial
        );
        var axisZ = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisZMaterial
        );

        axisX.rotation.z = -Math.PI / 2;
        axisX.position.x = axisLength / 2 - 1;

        axisY.position.y = axisLength / 2 - 1;

        axisZ.rotation.y = -Math.PI / 2;
        axisZ.rotation.z = -Math.PI / 2;
        axisZ.position.z = axisLength / 2 - 1;

        Axises = new THREE.Object3D();

        Axises.add(axisX);
        Axises.add(axisY);
        Axises.add(axisZ);

        var arrowX = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 4 * axisRadius, 4 * axisRadius, axisTess, 1, true),
            axisXMaterial
        );

        var arrowY = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 4 * axisRadius, 4 * axisRadius, axisTess, 1, true),
            axisYMaterial
        );

        var arrowZ = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 4 * axisRadius, 4 * axisRadius, axisTess, 1, true),
            axisZMaterial
        );

        arrowX.rotation.z = -Math.PI / 2;

        arrowY.position.y = axisLength - 1 + axisRadius * 4 / 2;

        arrowZ.rotation.z = -Math.PI / 2;
        arrowZ.rotation.y = -Math.PI / 2;
        arrowZ.position.z = axisLength - 1 + axisRadius * 4 / 2;

        scene.add(Axises);
        Axises.rotation.x = Math.PI/2;

        // scene.add(arrowX);
        // scene.add(arrowY);
        // scene.add(arrowZ);
    }

    function drawAllAxes(params) {
        params = params || {};
        var axisRadius = params.axisRadius !== undefined ? params.axisRadius : 0.02;
        var axisLength = params.axisLength !== undefined ? params.axisLength : 2;
        var axisTess = params.axisTess !== undefined ? params.axisTess : 24;

        var axisXMaterial = new THREE.MeshLambertMaterial({ color: 0xFF0000 });
        var axisYMaterial = new THREE.MeshLambertMaterial({ color: 0x00FF00 });
        var axisZMaterial = new THREE.MeshLambertMaterial({ color: 0x0000FF });
        axisXMaterial.side = THREE.DoubleSide;
        axisYMaterial.side = THREE.DoubleSide;
        axisZMaterial.side = THREE.DoubleSide;
        var axisX = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisXMaterial
        );
        var axisY = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisYMaterial
        );
        var axisZ = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisZMaterial
        );
        axisX.rotation.z = - Math.PI / 2;
        axisX.position.x = axisLength/2-1;

        axisY.position.y = axisLength/2-1;

        axisZ.rotation.y = - Math.PI / 2;
        axisZ.rotation.z = - Math.PI / 2;
        axisZ.position.z = axisLength/2-1;

        // scene.add( axisX );
        // scene.add( axisY );
        // scene.add( axisZ );

        var arrowX = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 2*axisRadius, 8*axisRadius, axisTess, 1, true),
            axisXMaterial
        );
        var arrowY = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 2*axisRadius, 8*axisRadius, axisTess, 1, true),
            axisYMaterial
        );
        var arrowZ = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 2*axisRadius, 8*axisRadius, axisTess, 1, true),
            axisZMaterial
        );
        arrowX.rotation.z = - Math.PI / 2;
        arrowX.position.x = axisLength - 1 + axisRadius*4/2;

        arrowY.position.y = axisLength - 1 + axisRadius*4/2;

        arrowZ.rotation.z = - Math.PI / 2;
        arrowZ.rotation.y = - Math.PI / 2;
        arrowZ.position.z = axisLength - 1 + axisRadius*4/2;

        // scene.add( arrowX );
        // scene.add( arrowY );
        // scene.add( arrowZ );

        Axises = new THREE.Object3D();

        Axises.add(axisX);
        Axises.add(axisY);
        Axises.add(axisZ);

        Axises.add(arrowX);
        Axises.add(arrowY);
        Axises.add(arrowZ);

        scene.add(Axises);
    }

    function drawNorth() {

        //North Arrow on ZX (Global scene)
        var planeGeometry = new THREE.PlaneBufferGeometry(4, 4, 1, 1);
        //var texture = new THREE.TextureLoader().load('../../../assets/img/textures/arrow-north.png');
        var planeMaterial = new THREE.MeshLambertMaterial({ map: THREE.ImageUtils.loadTexture('../../../assets/img/textures/arrow-north.png') });
        planeMaterial.side = THREE.DoubleSide;
        var plane = new THREE.Mesh(planeGeometry, planeMaterial);
        plane.receiveShadow = true;
        // rotate and position the plane
        plane.rotation.x = -0.5 * Math.PI;
        plane.rotation.z = -0.5 * Math.PI;
        plane.position.set(10,0,10);
        // add the plane to the scene
        scene.add(plane);



        //Dark Part
        var darkMaterial = new THREE.MeshLambertMaterial({ color: 0x222222 });
        darkMaterial.side = THREE.DoubleSide;

        var darkArrow = new THREE.Geometry();
        darkArrow.vertices.push( new THREE.Vector3(0,0,0) );
        darkArrow.vertices.push( new THREE.Vector3(0.75,0,0) );
        darkArrow.vertices.push( new THREE.Vector3(-0.5,0,0.5) );
        darkArrow.faces.push(new THREE.Face3(0, 2, 1));

        var darkTriang = new THREE.Mesh(darkArrow, darkMaterial);

        //Light Part
        var lightMaterial = new THREE.MeshLambertMaterial({ color: 0xDDDDDD });
        lightMaterial.side = THREE.DoubleSide;

        var lightArrow = new THREE.Geometry();
        lightArrow.vertices.push( new THREE.Vector3(0,0,0) );
        lightArrow.vertices.push( new THREE.Vector3(0.75,0,0) );
        lightArrow.vertices.push( new THREE.Vector3(-0.5,0,-0.5) );
        lightArrow.faces.push(new THREE.Face3(0, 2, 1));

        var lightTriang = new THREE.Mesh(lightArrow, lightMaterial);

        //Show on the AxisHelper
        var Hlper = new THREE.Object3D();

        Hlper.add(darkTriang);
        Hlper.add(lightTriang);

        const nLabel = addLabel('N', {fontSize: 18, position: {'x': -0.2, 'y': 0, 'z': 0}});
        Hlper.add(nLabel);

        axesHelperContainer.add(Hlper);
        axesHelper = Hlper;
        axesHelper.scale.set(0.3, 0.3, 0.3);
    }

    function Render() {
        Update();
        Resize();

        controls.update();

        renderer.clear();
        renderer.render(scene, camera);
        if(rendererAxesHelper) {
            rendererAxesHelper.render(axesHelperScene, axesHelperCamera);
            let nextPosition = controls.target.clone().sub( camera.position );
            axesHelperCamera.position.copy(nextPosition).normalize().negate();
            axesHelperCamera.lookAt(new THREE.Vector3(0, 0, 0));
        }
        //renderer.render(sceneHUD, cameraHUD);

        requestAnimationFrame(Render);
    }

    function Update() {
        // light.position.copy(camera.position).normalize();
        /*if (lastZoom != camera.zoom) {
         var zoom = 1;

         hudBitmap.clearRect(0, 0, dom.width, dom.height);

         hudBitmap.beginPath();
         hudBitmap.arc(-25 * zoom, 28 * zoom, 5, 0, 2 * Math.PI, false);
         hudBitmap.fillStyle = "red";
         hudBitmap.fill();

         hudBitmap.beginPath();
         hudBitmap.arc(-25 * zoom, -65 * zoom, 5, 0, 2 * Math.PI, false);
         hudBitmap.fillStyle = "red";
         hudBitmap.fill();

         hudBitmap.beginPath();
         hudBitmap.arc(65 * zoom, 28 * zoom, 5, 0, 2 * Math.PI, false);
         hudBitmap.fillStyle = "red";
         hudBitmap.fill();

         lastZoom = camera.zoom;
         }*/
    }

    function outerSelect(type, row_id) {
        let mesh = _find_in_meshes(ais.children, type, row_id);
        //unselect old
        meshUnselect(INTERSECT_ARR);
        INTERSECT_ARR = [];
        if (mesh) {
            INTERSECT_ARR.push(mesh);
        }
        //select new
        meshSelect(INTERSECT_ARR);

        if (typeof onSelected == "function") {
            onSelected.call({}, mesh, INTERSECT_ARR);
        }
    }
    
    function _find_in_meshes(children, type, row_id) {
        let found = null;
        _.each(children, (elem) => {
            if (!found && elem.children && elem.children.length) {
                found = _find_in_meshes(elem.children, type, row_id);
            }
            if (!found && type === 'node' && elem.item == row_id) {
                found = elem;
            }
            if (!found && type === 'member' && elem.item_no == row_id) {
                found = elem;
            }
            if (!found && type === 'equipment' && elem.parent.lc_id == row_id) {
                found = elem;
            }
            if (!found && type === 'rl_bracket' && elem.rl_id == row_id) {
                found = elem;
            }
        });
        return found;
    }

    /**
     * Select Mesh on the 3D Model
     * @param INTERSECT_ARR
     */
    function meshSelect(INTERSECT_ARR) {
        _.each(INTERSECT_ARR, (INTERSECT) => {
            if(INTERSECT && INTERSECT.parent && !INTERSECT.single_type) {
                INTERSECT.parent.children.forEach(function(child) {
                    if (child && child.material && child.type == 'Mesh') {
                        child.currentHex = child.currentHex || child.material.color.getHex();
                        child.material.color.setHex(0xff0000);
                    }
                });
                addAxis(INTERSECT.parent);

            } else {
                if (INTERSECT && INTERSECT.material) {
                    INTERSECT.currentHex = INTERSECT.currentHex || INTERSECT.material.color.getHex();
                    INTERSECT.material.color.setHex(0xff0000);

                    if(INTERSECT.type != 'node') {
                        addAxis(INTERSECT);
                    }
                }
            }
        });
    }

    /**
     * Unselect current Mesh
     * @param INTERSECT_ARR
     */
    function meshUnselect(INTERSECT_ARR) {
        _.each(INTERSECT_ARR, (INTERSECT) => {
            if (INTERSECT && INTERSECT.parent && !INTERSECT.single_type) {
                INTERSECT.parent.children.forEach(function (child) {
                    if (child && child.material && child.type == 'Mesh') {
                        child.material.color.setHex(child.currentHex);
                    }
                });
                //LAST = INTERSECT;
                removeAxis(INTERSECT.parent);
            } else {
                if (INTERSECT && INTERSECT.material) {
                    INTERSECT.material.color.setHex(INTERSECT.currentHex);
                    //LAST = INTERSECT;
                    removeAxis(INTERSECT);
                }
            }
        });
    }

    /**
     * 3D model clicked
     * @param mode
     * @returns {boolean}
     * @constructor
     */
    function Event(mode) {
        if(mode == 'dposs') return false;

        dom.onclick = onMouseDown;
        dom.oncontextmenu = onRightClick;

        function onRightClick(event) {

            if (event.button != 2/* || !camera.inPerspectiveMode*/) {
                return false;
            }

            /* Intersector v2 */
            let intersects = getIntersected(event);

            //Old
            // mouse.x = (event.offsetX / dom.width) * 2 - 1;
            // mouse.y = -(event.offsetY / dom.height) * 2 + 1;
            // mouse.unproject( camera );
            //
            // raycaster.set(camera.position, mouse.sub( camera.position ).normalize());
            //
            // let intersects = raycaster.intersectObjects(ais.children, true);

            let mesh = null;

            if (intersects.length > 0) {

                for(let i = 0; i < intersects.length; i++) {
                    if(intersects[i].object.type === 'Mesh' || intersects[i].object.type === 'node') {
                        mesh = intersects[i].object;
                        break;
                    }
                }

            }

            //unselect old
            meshUnselect(INTERSECT_ARR);
            INTERSECT_ARR = [];
            if (mesh) {
                INTERSECT_ARR.push(mesh);
            }
            //select new
            meshSelect(INTERSECT_ARR);

            if (typeof onRigthClickSelect === "function") {
                onRigthClickSelect.call({}, mesh, INTERSECT_ARR);
            }

            event.preventDefault();

        }

        function onMouseDown(event) {
            if ((event.button != 0 && event.button != 2)/* || !camera.inPerspectiveMode*/) {
                return false;
            }
            let just_selected = null;

            /* Intersector v2 */
            let intersects = getIntersected(event);

            //Old
            // mouse.x = (event.offsetX / dom.width) * 2 - 1;
            // mouse.y = -(event.offsetY / dom.height) * 2 + 1;
            //
            //
            // if (camera.inPerspectiveMode) {
            //     mouse.unproject( camera );
            //     raycaster.set(camera.position, mouse.sub( camera.position ).normalize());
            //     intersects = raycaster.intersectObjects(ais.children, true);
            // } else {
            //     vector = mouse.clone().unproject( camera );
            //     let direction = new THREE.Vector3( 0, 0, -1 ).transformDirection( camera.matrixWorld );
            //     raycaster.set( vector, direction );
            //     intersects = raycaster.intersectObjects( ais.children );
            //     /*vector = new THREE.Vector3(mouse.x , mouse.y , 0.5);
            //     ray = raycaster.pickingRay(vector , camera);
            //     intersects = ray.intersectObjects(ais.children);*/
            // }

            if (intersects.length > 0) {
                if (!_.find(INTERSECT_ARR, (sel) => { return sel == intersects[0].object; })) {

                    //unselect old
                    if (!event.ctrlKey) {
                        meshUnselect(INTERSECT_ARR);
                        INTERSECT_ARR = [];
                    }

                    //find interselect
                    var found = false;
                    for(var i = 0; i < intersects.length; i++) {
                        if(intersects[i].object.type == 'Mesh' || intersects[i].object.type == 'node') {
                            intersects[i].object.ctrlKey = event.ctrlKey || false;
                            just_selected = intersects[i].object;
                            INTERSECT_ARR.push( intersects[i].object );
                            found = true;
                            break;
                        }
                    }
                    if(!found) {
                        meshUnselect(INTERSECT_ARR);
                        INTERSECT_ARR = [];
                    }

                    //select new
                    meshSelect(INTERSECT_ARR);

                }
            } else {
                meshUnselect(INTERSECT_ARR);
                INTERSECT_ARR = [];
            }

            if (typeof onSelected == "function") {
                onSelected.call({}, just_selected, INTERSECT_ARR);
            }

            event.preventDefault();
        }
    }

    /**
     * Intersector v2
     * @returns {*}
     */
    function getIntersected(event) {
        let vector = new THREE.Vector3();
        if ( camera.inOrthographicMode ) {
            vector.set( ( event.offsetX / dom.width ) * 2 - 1, - ( event.offsetY / dom.height ) * 2 + 1, - 1 ); // z = - 1 important!
            vector.unproject( camera );
            let dir = new THREE.Vector3();
            dir.set( 0, 0, - 1 ).transformDirection( camera.matrixWorld );
            raycaster.set( vector, dir );
        } else if ( camera.inPerspectiveMode ) {
            vector.set( ( event.offsetX / dom.width ) * 2 - 1, - ( event.offsetY / dom.height ) * 2 + 1, 0.5 ); // z = 0.5 important!
            vector.unproject( camera );
            raycaster.set( camera.position, vector.sub( camera.position ).normalize() );
        }
        return raycaster.intersectObjects( ais.children, true );
    }

    function Resize() {
        dom = document.querySelector(element);

        var width = dom.innerWidth || dom.offsetWidth;
        var height = dom.innerHeight || dom.offsetHeight;

        if (dom.width != width || dom.height != height) {
            dom.width = width;
            dom.height = height;

            camera.setSize(dom.width, dom.height);
            camera.updateProjectionMatrix();

            renderer.setSize(dom.width, dom.height);

            if (!window.drawAisc) {
                Grid();
            }
        }
    }

    function Run(selector, mode) {
        element = selector;
        dom = document.querySelector(selector);

        dom.width = dom.innerWidth || dom.offsetWidth;
        dom.height = dom.innerHeight || dom.offsetHeight;

        Init(mode);
        Grid();
        Render();
        Event(mode);
    }

    function Draw(cached, objects, state, type, lc_data, shrink, equipments, dc, sectionsInfo, rls, extra) {
        clearCached(cached);
        drawRL(ais, equipments, rls, extra);

        var temp = [];
        var object = new THREE.Mesh();

        if (state == "update") {
            ais.traverse(function (child) {
                if (child instanceof THREE.Mesh) {
                    for (var key in objects) {
                        if (child.item == objects[key].id) {
                            temp.push(child);
                        }
                    }
                }
            });

            for (var key in temp) {
                ais.remove(temp[key]);
            }

            return ais;
        } else if (state == "delete") {
            ais.traverse(function (child) {
                if (child instanceof THREE.Mesh  || child instanceof THREE.Object3D) {
                    if (objects.length) {
                        for (var key in objects) {
                            if (child.item == objects[key].id) {
                                temp.push(child);
                            }
                        }
                    } else {
                        temp.push(child);
                    }
                }
            });

            for (var key in temp) {
                ais.remove(temp[key]);
            }

            return ais;
        } else {
            ais.position.set(0, 0, 0);
            ais.rotation.set(0, 0, 0);

            switch(type) {
                case 'nodes':
                    drawNodes(objects, ais, "redraw");
                    break;
                case 'cuboid':
                    drawCuboid(objects, ais, "redraw");
                    break;
                case 'cylinder':
                    drawCylinder(objects, ais, "redraw");
                    break;
                case 'sphere':
                    drawSphere(objects, ais, "redraw");
                    break;

                case 'flat_panel':
                    drawFlatPanel(objects, ais, "redraw");
                    break;
                case 'conical_dish_w_shroud':
                    drawConicalDishShroud(objects, ais, "redraw");
                    break;
                case 'cylindrical_dish_w_shroud':
                    drawCylinderDishShroud(objects, ais, "redraw");
                    break;
                case 'dish_w_radome':
                    drawDishRadom(objects, ais, "redraw");
                    break;
                case 'parabolic_grid_dish':
                    drawParabolicGridDish(objects, ais, "redraw");
                    break;

                case 'wid_objects':
                    drawObjectsWID(objects, ais, "redraw", shrink, equipments, dc, sectionsInfo);
                    break;

                case 'wl_sc_objects':
                    // TODO: drawObjectsWLSC();
                    break;

                case 'custom_object':
                    drawCustomFile(objects, ais, "redraw");
                    break;
                default:
                    drawObjects(objects, ais, "redraw", sectionsInfo);
                    break;
            }
        }
        Aspect();

        return ais;
    }

    function Grid() {
        // var custom = document.getElementById("webgl");
        var custom = document.getElementById("2d");
        // var canvas = custom.querySelector("canvas");

        // var canvas = null;

        // if (canvas == null) {
            var canvas = document.createElement("canvas");
        // }

        var context = canvas.getContext("2d");
        var zoomscale = 1000;

        canvas.width  = dom.width;
        canvas.height = dom.height;

        context.font = "18px Times";
        context.translate(canvas.width / 2, canvas.height / 2);
        context.scale(1.0, 1.0);

        context.clearRect(0, 0, canvas.width, canvas.height);

        context.lineWidth = 1;

        context.beginPath();
        context.moveTo(-canvas.width / 2, 0);
        context.lineTo(+canvas.width / 2, 0);
        context.strokeStyle = "red";
        context.stroke();

        context.beginPath();
        context.moveTo(0, -canvas.height / 2);
        context.lineTo(0, +canvas.height / 2);
        context.strokeStyle = "blue";
        context.stroke();

        context.lineWidth = 0;
        context.strokeStyle = "#DCDCDC";

        var Nbr_gridlines = Math.ceil( canvas.width / 2 / (1 / 12 * zoomscale) );

        for (var iline = 0; iline < Nbr_gridlines; iline++) {
            context.beginPath();
            context.moveTo(-canvas.width / 2, iline * 1 / 12 * zoomscale);
            context.lineTo(+canvas.width / 2, iline * 1 / 12 * zoomscale);

            context.beginPath();
            context.moveTo(iline * 1 / 12 * zoomscale, -canvas.height / 2);
            context.lineTo(iline * 1 / 12 * zoomscale, +canvas.height / 2);

            if(iline !=0){
                context.moveTo(-canvas.width / 2, -iline * 1/ 12 * zoomscale);
                context.lineTo(+canvas.width / 2, -iline * 1/ 12 * zoomscale);
                context.stroke();

                context.moveTo(-canvas.width / 2, +iline * 1 / 12 * zoomscale);
                context.lineTo(+canvas.width / 2, +iline * 1 / 12 * zoomscale);
                context.stroke();

                context.moveTo(-iline * 1 / 12 * zoomscale, -canvas.height / 2);
                context.lineTo(-iline * 1 / 12 * zoomscale, +canvas.height / 2);
                context.stroke();

                context.moveTo(+iline * 1 / 12 * zoomscale, -canvas.height / 2);
                context.lineTo(+iline * 1 / 12 * zoomscale, +canvas.height / 2);
                context.stroke();
            }
        }

        while (custom.hasChildNodes()) {
            custom.removeChild(custom.lastChild);
        }

        custom.appendChild(canvas);
    }

    function drawObjects(section, parent, action, sectionsInfo) {
        if (action == "redraw") {
            removeByType(parent, 'line');
            removeByType(parent, 'member');
            removeByType(scene, 'memberLabel');
            removeByType(parent, 'equipmentLabel');
        }

        var arrToRemove = [];
        parent.traverse(function (child) {
            if (child instanceof THREE.EdgesHelper) {
                arrToRemove.push(child);
            }
        });

        arrToRemove.forEach(function(child){
            parent.remove(child);
        });

        if (!section) {
            Grid();
            return false;
        }

        section.nodes = {};
        section.nodes.NodeE = [0, 0, 0];
        section.nodes.NodeS = [0, 1, 0];

        var app_p1 = section.nodes.NodeS;
        var app_p2 = section.nodes.NodeE;
        var app_p3 = '';

        var member_color = section.color || viewSettings.defMemberColor;
        var material = member_color ? "custom" : "metal";
        var rot_x2stdy = 0;

        // var object = drawAISCmember("byNodes", app_p1, app_p2, app_p3, section.shape, section.size, material, member_color, rot_x2stdy);

        var dbSRC    = section.src;
        var Type     = section.shape;
        var SizeType = section.name;
        var SizeUnit = section.unit;
        var Size1    = section.Size1;
        var Size2    = section.Size2;
        var GridSize2D = section.grid_size2d;

        var XD = '23D'; // 2D to draw 2D only, 3D to draw 3D, '23D' to draw both 2D and 3D.
        var shrk_pct = 0.0; // no shrinkage
        var object = drawAISCmember(XD, "byNodes", app_p1, app_p2, app_p3, dbSRC, Type, SizeType, SizeUnit, Size1, Size2, material, member_color, rot_x2stdy, shrk_pct, 'dposs', 0, GridSize2D, sectionsInfo);

        //camera.position.set(2*object.zoomscale, 2*object.zoomscale, 2*object.zoomscale);

        object.type = 'member';
        object.section = section.id;
        object.visible = true;
        object._row_hash = setCached(section._row_hash, 'member');

        object.traverse(function (child) {

            if (child instanceof THREE.Mesh) {
                child.material.polygonOffset = true;
                child.material.polygonOffsetFactor = 1;
                child.material.polygonOffsetUnits = 1;
                child.material.side = THREE.DoubleSide;


                let wfh = new THREE.EdgesHelper(child, viewSettings.edges_color || "#ffffff");

                wfh.material.opacity = 0.9;
                wfh.material.transparent = true;
                wfh.visible = viewSettings.wireframe;

                parent.add(wfh);
            }

        });

        parent.add(object);
    }

    function drawObjectsWID(objects, parent, action, shrink, equipments, dc, sectionsInfo) {
        if (action == "redraw") {
            removeByType(parent, 'line');
            removeByType(parent, 'member');
            removeByType(parent, 'memberLabel');
            removeByType(parent, 'equipmentLabel');
        }

        var arrToRemove = [];
        scene.traverse(function (child) {
            if (child instanceof THREE.EdgesHelper) {
                arrToRemove.push(child);
            }
        });

        arrToRemove.forEach(function(child){
            scene.remove(child);
        });

        objects.forEach(function (item) {
            if (isPresent(item, 'member')) {
                return;
            }

            if (item.other.node && item.other.node.s && item.other.node.e && item.other.node.s.length && item.other.node.e.length) {
                var approach = "byNodes";
            } else {
                var approach = "dirLth";
            }

            let len = 0;
            switch (approach) {
                case 'dirLth':
                    var app_p1 = "ny";
                    var app_p2 = item.length;
                    var app_p3 = "start";
                    len = Number(item.length);
                    break;
                case 'byNodes':
                    var app_p1 = item.other.node.s;
                    var app_p2 = item.other.node.e;
                    var app_p3 = '';
                    var vec1 = new THREE.Vector3( parseFloat(item.other.node.s[0]), parseFloat(item.other.node.s[1]), parseFloat(item.other.node.s[2]) );
                    var vec2 = new THREE.Vector3( parseFloat(item.other.node.e[0]), parseFloat(item.other.node.e[1]), parseFloat(item.other.node.e[2]) );
                    len = vec1.distanceTo(vec2);
                    break;
                default:
                    break;
            }

            var member_color = item.color || viewSettings.defMemberColor;
            var material = member_color ? "custom" : "metal";
            var rot_x2stdy = item.other.y2 * (Math.PI/180);
            
            var object = drawAISCmember('3D', 'byNodes', app_p1, app_p2, app_p3, 'V141', item.shape, "AISC_Manual", 'US_Customary', item.size1, item.size2, material, member_color, rot_x2stdy, 0.0, 'wid', shrink, '', sectionsInfo, item.Mbr_Name);

            if(object.children[0]) {
                object.children.forEach(function (children) {
                    children.item_no = item.id;
                });
            }

            if (approach == "dirLth") {
                object.position.set(item.other.x1 || 0, item.other.y1 || 0, item.other.z1 || 0);
                object.rotation.set(item.other.x2 || 0, item.other.y2 || 0, item.other.z2 || 0);
            }

            object.type = 'member';
            object.sec_id = item.sec_id;
            object.mat_id = item.mat_id;
            object.node_start_id = item.node_start_id;
            object.node_end_id = item.node_end_id;
            object.attached_eqpt_ids = item.attached_eqpt_ids || [];
            object.attached_postombrs = item.attached_postombrs || [];
            object.member_len = len;

            object.visible = viewSettings.members;
            object._row_hash = setCached(item._row_hash, 'member');

            object.traverse(function (child) {

                if (child instanceof THREE.Mesh) {

                    var wfh = new THREE.EdgesHelper(child, viewSettings.edges_color || "#ffffff");

                    wfh.material.opacity = 0.9;
                    wfh.material.transparent = true;
                    wfh._sett_type = 'member';
                    wfh.visible = wfh._sett_type === 'member' && viewSettings.edges_members && viewSettings.members;

                    scene.add(wfh);
                }
            });

            if(equipments) {
                equipments.forEach(function(eqpt) {
                    if(item.name == eqpt.lc.mbr_name) {
                        if(RegExp(/structure/gi).test( eqpt.eq.geometryType ) && eqpt.details) {
                            drawNodes(eqpt.details.nodes, object, "add");
                            drawObjectsWID(eqpt.details.objects, object, "redraw", shrink);
                        } else if(RegExp(/single.*object/gi).test( eqpt.eq.geometryType )) {
                            if(RegExp(/cuboid/gi).test( eqpt.eq.geometryShapeType )) {
                                drawCuboid({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2, eqpt.eq.d3],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);
                            } else if(RegExp(/flat.*panel/gi).test( eqpt.eq.geometryShapeType )) {
                                drawFlatPanel({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2, eqpt.eq.d3, eqpt.eq.d4],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);

                            } else if(RegExp(/cylinder/gi).test( eqpt.eq.geometryShapeType )) {
                                drawCylinder({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);
                            } else if(RegExp(/sphere/gi).test( eqpt.eq.geometryShapeType )) {
                                drawSphere({
                                    dimensions: [eqpt.eq.d1],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);

                            } else if(RegExp(/conical.*dish.*shroud/gi).test( eqpt.eq.geometryShapeType )) {
                                drawConicalDishShroud({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2, eqpt.eq.d3, eqpt.eq.d4],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);

                            } else if(RegExp(/cylinder.*dish.*shroud/gi).test( eqpt.eq.geometryShapeType )) {
                                drawCylinderDishShroud({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2, eqpt.eq.d3, eqpt.eq.d4],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc);     

                            } else if(RegExp(/dish.*radom/gi).test( eqpt.eq.geometryShapeType )) {
                                drawDishRadom({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2, eqpt.eq.d3, eqpt.eq.d4],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);

                            } else if(RegExp(/parabolic.*grid.*dish/gi).test( eqpt.eq.geometryShapeType )) {
                                drawParabolicGridDish({
                                    dimensions: [eqpt.eq.d1, eqpt.eq.d2, eqpt.eq.d3, eqpt.eq.d4],
                                    texture: {
                                        type: eqpt.eq.texture_type,
                                        color: eqpt.eq.color,
                                        name: eqpt.eq.texture
                                    },
                                    product: {
                                        id: eqpt.eq._id
                                    },
                                    _row_hash: eqpt.lc._row_hash,
                                }, object, "add", eqpt.lc, eqpt.eq.model);
                            }
                        }
                    }
                });
            }

            //axises changed oldZ = newY, oldY = newZ
            object.rotateY(rot_x2stdy);

            parent.add(object);

            if (item.nodes && item.nodes.NodeE && item.nodes.NodeS) {
                var geometry = new THREE.Geometry();

                geometry.vertices.push(parent.getObjectByName("node" + item.nodes.NodeE).position);
                geometry.vertices.push(parent.getObjectByName("node" + item.nodes.NodeS).position);

                var line = new THREE.Line(geometry, new THREE.LineBasicMaterial({color: 0x0051FF}));
                line.type = 'line';
                line.visible = viewSettings.wireframe;
                line._row_hash = item._row_hash;

                parent.add(line);
            }

            if (item.nodes && item.nodes.NodeE && item.nodes.NodeS) {
                var nodeSPosition = parent.getObjectByName("node" + item.nodes.NodeS).position;
                var nodeEPosition = parent.getObjectByName("node" + item.nodes.NodeE).position;

                var nodeDirection = (nodeSPosition.x <= nodeEPosition.x || nodeSPosition.y <= nodeEPosition.y || nodeSPosition.z <= nodeEPosition.z);

                // var nodeLabel = GetStaticLabel(item.name, 14, nodeDirection);

                var nodeLabel = makeTextSprite(item.name, {
                    fontsize: 32
                });

                SetAlongLine(nodeLabel, [
                    new THREE.Vector3(to_float(nodeSPosition.x), to_float(nodeSPosition.y), to_float(nodeSPosition.z)),
                    new THREE.Vector3(to_float(nodeEPosition.x), to_float(nodeEPosition.y), to_float(nodeEPosition.z))
                ], 0.5);

                nodeLabel.type = 'memberLabel';
                nodeLabel.visible = viewSettings.wireframeName;
                nodeLabel._row_hash = item._row_hash;
                adaptFigureScale(nodeLabel, camera);
                parent.add(nodeLabel);
            }

            if(dc) {
                parent.position.set(dc.dx, dc.dy, dc.dz);
                parent.rotation.set(dc.rotx * (Math.PI/180), dc.roty * (Math.PI/180), dc.rotz * (Math.PI/180));
            }

        });
    }

    function drawNodes(nodes, parent, action) {
        if (action == "redraw") {
            removeByType(parent, 'node');
            removeByType(parent, 'nodeLabel');
            removeByType(parent, 'equipment');
            removeByType(parent, 'customObject');
        }

        if (nodes) {
            nodes.forEach(function (node) {
                if (isPresent(node, 'node')) {
                    return;
                }

                var geometry = new THREE.SphereGeometry(0.1, 32, 32);
                var material = new THREE.MeshBasicMaterial({color: 0x000000});
                var sphere = new THREE.Mesh(geometry, material);

                sphere.position.set(node.x, node.y, node.z);
                sphere.item = node.no;
                sphere.single_type = 'node';
                sphere.type = 'node';
                sphere.name = 'node' + node.no;
                sphere.visible = viewSettings.nodes;
                sphere._row_hash = setCached(node._row_hash, 'node');
                adaptFigureScale(sphere, camera);

                /*
                 var nodeLabel = GetStaticLabel(node.node_name, 14);

                 nodeLabel.position.set(0, 0.1, 0);

                 nodeLabel.type = 'nodeLabel';
                 nodeLabel.visible = viewSettings.nodesName;
                 sphere.add(nodeLabel);
                 */
                if(node.node_name) {
                    var spritey = makeTextSprite(node.node_name, {
                        fontsize: 32
                    });

                    spritey.position.set(node.x, node.y, node.z);
                    spritey.type = 'nodeLabel';
                    spritey.visible = viewSettings.nodesName;
                    spritey._row_hash = setCached(node._row_hash, 'node');
                    adaptFigureScale(spritey, camera);
                    parent.add(spritey);
                }
                parent.add(sphere);
            });
        }
    }

    function drawCustomFile(data, parent, action) {
        if (action == "redraw") {
            clearScene();
            // removeByType(parent, 'customObject');
        }

        var loader = new THREE.OBJLoader();
        var mtlLoader = new THREE.MTLLoader('documents/product/' + data.id + '/3D/');

        var material = false;

        var object_name = data.file.split('.');

        mtlLoader.load(
            'documents/product/' + data.id + '/3D/' + object_name[0] + '.mtl',
            function (materials) {
                materials.preload();

                for (let key in materials.materials) {
                    material = materials.materials[key];

                    material.shading = THREE.SmoothShading;
                    material.side = THREE.DoubleSide;
                }


                if(data.file.indexOf('.obj') != -1) {
                    loader.load(

                        'documents/product/' + data.id + '/3D/' + data.file,

                        function ( object ) {

                            if(material) {
                                object.children[0].material = material;
                            }

                            object.type = 'customObject';

                            let box = new THREE.Box3().setFromObject(object);
                            let size = box.size();
                            let max = size.x;

                            if(size.y > max) {
                                max = size.y;
                            }

                            if(size.z > max) {
                                max = size.z;
                            }

                            let scale = 4 / max;

                            object.scale.set(scale, scale, scale);

                            parent.add( object );
                        }
                    );
                }

            }
        );
        
    }

    function clearScene(parent) {
        present_mesh = {};
        removeByType(parent, 'equipment');
        removeByType(parent, 'node');
        removeByType(parent, 'nodeLabel');
        removeByType(parent, 'line');
        removeByType(parent, 'member');
        removeByType(parent, 'memberLabel');
        removeByType(parent, 'equipmentLabel');
        removeByType(parent, 'customObject');

        let arrToRemove = [];
        scene.traverse(function (child) {
            if (child instanceof THREE.EdgesHelper) {
                arrToRemove.push(child);
            }
        });

        arrToRemove.forEach(function(child){
            scene.remove(child);
        });
    }

    function createPhongMaterial(options){
        return new THREE.MeshPhongMaterial({
            color: options.color || '#2222ff',
            specular: 0xcccccc,
            shininess: 20,
            emissive: 0x0,
            shading: THREE.SmoothShading,
            size: THREE.DoubleSide
       });
    }

    function drawFlatPanel(objects, parent, action, lc_data, label) {
        if (action == "redraw") {
            clearScene(parent);
        }

        let lineGeometry = new THREE.Geometry();
        let vertArray = lineGeometry.vertices;


        let d1 = to_float(objects.dimensions[0] / 12);
        let d2 = to_float(objects.dimensions[1] / 12);
        let d3 = to_float(objects.dimensions[2] / 12);
        let d4 = to_float(objects.dimensions[3] / 12);

        let dimensions = {
            "height": objects.dimensions[0],
            "width": objects.dimensions[1],
            "depth": objects.dimensions[2]
        };

        let sd3 = d3/2;
        let sd2 = d2/2;

        let irv = (d2 - 0.001) / 50;
        let MaxY = sd2;
        let Rat = d4 / MaxY;

        vertArray.push( new THREE.Vector3(-sd2, 0 , 0));
        vertArray.push( new THREE.Vector3(-sd2, d3 , 0));

        for(let x =- sd2; x <= sd2; x += irv){
            let y = Math.sqrt(sd2 * sd2 - x * x);
            vertArray.push( new THREE.Vector3(x, y * Rat + sd3*2 , 0));
        }

        vertArray.push( new THREE.Vector3(sd2, 0 , 0));
        vertArray.push( new THREE.Vector3(-sd2, 0 , 0));


        let material = getColorOrTexture(objects);
        
        let shape = new THREE.Shape(vertArray);

        let extrudeSettings = {
            steps: 1,
            amount: d1,
            bevelEnabled: false,
            bevelThickness: 0,
            bevelSize: 0,
            bevelSegments: 0
        };

        let geometry = new THREE.ExtrudeGeometry( shape, extrudeSettings );

        let group = new THREE.Mesh();

        let obj = new THREE.Mesh( geometry, material );

        obj.position.set(0, -d1/2, 0);
        obj.rotation.set(-Math.PI/2, 0, -Math.PI/2);


        obj.faces = {
            px:{ dir:[+1, 0,  0], area: d1*d2 },
            nx:{ dir:[-1, 0,  0], area: d1*d2 },
            pz:{ dir:[ 0, 0, +1], area: d1*d3 },
            nz:{ dir:[ 0, 0, -1], area: d1*d3 }                                    
        };

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        group.type = 'equipment';
        group.eqpt_id = objects.product.id;

        group.add(obj);

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            let newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float(lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), (lc_data.roty * (Math.PI/180)) + newD.roty, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);

        }

        createEdges(group);

        parent.add(group);
    }

    function drawCuboid(objects, parent, action, lc_data, label) {
        if (action == "redraw") {
            clearScene(parent);
        }

        var height = objects.dimensions[0] / 12;  //length
        var width = objects.dimensions[1] / 12;
        var depth = objects.dimensions[2] / 12;  //thickness

        let dimensions = {
            "height": objects.dimensions[0],
            "width": objects.dimensions[1],
            "depth": objects.dimensions[2]
        };

        var geometry = new THREE.BoxGeometry(depth, height, width);
        var material = getColorOrTexture(objects);

        var box = new THREE.Mesh( geometry, material );


        box.faces = {
            px:{ dir:[+1,  0,  0], area: height*width },
            nx:{ dir:[-1,  0,  0], area: height*width },
            py:{ dir:[ 0, +1,  0], area: depth*width },
            ny:{ dir:[-0, -1,  0], area: depth*width },
            pz:{ dir:[ 0,  0, +1], area: height*depth },
            nz:{ dir:[ 0,  0, -1], area: height*depth }
        };

        var group = new THREE.Mesh();
        //
        group.add(box);
        //
        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            let newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), (lc_data.roty * (Math.PI/180)) + newD.roty, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        parent.add(group);
    }

    function drawCylinder(objects, parent, action, lc_data, label) {
        if (action == "redraw") {
            clearScene(parent);
        }

        var height = objects.dimensions[0] / 12;
        var radius = objects.dimensions[1] / 24;

        let dimensions = {
            "height": objects.dimensions[0],
            "radius": objects.dimensions[1]
        };

        var geometry = new THREE.CylinderGeometry( radius, radius, height, 36 );
        var material = getColorOrTexture(objects);

        var cylinder = new THREE.Mesh( geometry, material );

        var group = new THREE.Mesh();

        group.add(cylinder);

        group.faces = {
            pxz:{ dir:[+1,  0,  0], area: height*radius },
            nxz:{ dir:[-1,  0,  0], area: height*radius },
            py:{ dir:[ 0, +1,  0], area: radius*radius/4*Math.PI },
            ny:{ dir:[ 0, -1,  0], area: radius*radius/4*Math.PI },
        };

        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            var newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), 0, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);
            group.rotation.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        parent.add( group );
    }

    function drawSphere(objects, parent, action, lc_data, label) {
        if (action == "redraw") {
            clearScene(parent);
        }

        var radius = objects.dimensions[0]/24;

        let dimensions = {
            "radius": objects.dimensions[0],
        };

        var geometry = new THREE.SphereGeometry(radius, 36, 36);
        var material = getColorOrTexture(objects);

        var sphere = new THREE.Mesh( geometry, material );

        var group = new THREE.Mesh();

        group.add(sphere);

        group.faces = {
            xyz:{ dir:[0,  0,  0], area: radius*radius/4*Math.PI  },
        }        

        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            var newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
        } else {
            group.position.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        parent.add( group );
    }

    function drawConicalDishShroud(objects, parent, action, lc_data, label) {

        if (action == "redraw") {
            clearScene(parent);
        }

        let lineGeometry = new THREE.Geometry();

        let d1 = to_float(objects.dimensions[0] / 12);
        let d2 = to_float(objects.dimensions[1] / 12);
        let d3 = to_float(objects.dimensions[2] / 12);
        let d4 = to_float(objects.dimensions[3] / 12);

        let dimensions = {
            "height": objects.dimensions[0],
            "width": objects.dimensions[1],
            "depth": objects.dimensions[2]
        };

        let sd1 = d2/2;

        let curve = new THREE.EllipseCurve(
            0,  0,            // ax, aY
            sd1, sd1,           // xRadius, yRadius
            0,  Math.PI/2,  // aStartAngle, aEndAngle
            false,            // aClockwise
            0                 // aRotation
        );

        let path = new THREE.Path( curve.getPoints( 50 ) );

        lineGeometry = path.createPointsGeometry( 50 );

        for(let i = 0; i < lineGeometry.vertices.length; i++){
            lineGeometry.vertices[i].z = lineGeometry.vertices[i].y;
            lineGeometry.vertices[i].y = lineGeometry.vertices[i].x;
            lineGeometry.vertices[i].x = 0;
        }


        lineGeometry.vertices.unshift(new THREE.Vector3(-0.01, 0.0, 0.0));

        lineGeometry.vertices.pop();

        let geometry = new THREE.LatheGeometry( lineGeometry.vertices, 48);
        var material = getColorOrTexture(objects);

        let obj = new THREE.Mesh( geometry, material );

        obj.position.set(d2/2, 0, 0);
        obj.rotation.set(0, -Math.PI/2, 0);

        let group = new THREE.Mesh();

        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        group.add(obj);

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            let newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), (lc_data.roty * (Math.PI/180)) + newD.roty, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);


        parent.add(group);

    }

    function drawCylinderDishShroud(objects, parent, action, lc_data, label) {

        if (action == "redraw") {
            clearScene(parent);
        }

        let lineGeometry = new THREE.Geometry();
        let vertArray = lineGeometry.vertices;


        let d1 = to_float(objects.dimensions[0] / 12);
        let d2 = to_float(objects.dimensions[1] / 12);
        let d3 = to_float(objects.dimensions[2] / 12);
        let d4 = to_float(objects.dimensions[3] / 12);

        let dimensions = {
            "height": objects.dimensions[0],
            "width": objects.dimensions[1],
            "depth": objects.dimensions[2]
        };


        vertArray.push( new THREE.Vector3(-0.001, 0, -d4), new THREE.Vector3(-d3/2, 0, 0), new THREE.Vector3(-d3/2, 0, d2), new THREE.Vector3(-0.001, 0, d2) );


        let geometry = new THREE.LatheGeometry( vertArray, 48);
        let material = getColorOrTexture(objects);

        let obj = new THREE.Mesh( geometry, material );

        obj.position.set(0, 0, d4);

        let group_rotation = new THREE.Mesh();

        group_rotation.add(obj);

        group_rotation.rotation.set(0, Math.PI/2, 0);


        let group = new THREE.Mesh();

        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        group.add(group_rotation);


        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            let newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), (lc_data.roty * (Math.PI/180)) + newD.roty, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        parent.add(group);

    }

    function drawDishRadom(objects, parent, action, lc_data, label) {

        if (action == "redraw") {
            clearScene(parent);
        }

        let lineGeometry = new THREE.Geometry();
        let vertArray = lineGeometry.vertices;

        let d1 = to_float(objects.dimensions[0] / 12);
        let d2 = to_float(objects.dimensions[1] / 12);
        let d3 = to_float(objects.dimensions[2] / 12);
        let d4 = to_float(objects.dimensions[3] / 12);

        let dimensions = {
            "height": objects.dimensions[0],
            "width": objects.dimensions[1],
            "depth": objects.dimensions[2]
        };

        let sd1 = d1/2;
        let irv = sd1 / 50.0;
        let y = 6 * sd1 * sd1 * sd1 * sd1 * sd1 + 2;

        let rat = d2/y;

        vertArray.push( new THREE.Vector3(0.01, 0.0, 0));
        vertArray.push( new THREE.Vector3(sd1, 0, d4));
        vertArray.push( new THREE.Vector3(sd1, 0, d4+d3));

        var tmpArr = [];
        for(let x=0; x<sd1; x += irv){
            let y = 6 * x * x * x * x * x+ 2;
            tmpArr.push( new THREE.Vector3( x, 0,d2 - y * rat+d4+d3));
        }
        for(let i=0; i<tmpArr.length; i++){
            vertArray.push(tmpArr[tmpArr.length-i-1]);
        }

        let geometry = new THREE.LatheGeometry( vertArray, 48);
        let material = getColorOrTexture(objects);

        let obj = new THREE.Mesh( geometry, material );

        obj.rotation.set(0, Math.PI/2, 0);


        let group = new THREE.Mesh();

        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        group.add(obj);

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            let newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), (lc_data.roty * (Math.PI/180)) + newD.roty, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        parent.add(group);

    }

    function drawParabolicGridDish(objects, parent, action, lc_data, label) {

        if (action == "redraw") {
            clearScene(parent);
        }

        let lineGeometry = new THREE.Geometry();
        let vertArray = lineGeometry.vertices;

        let d1 = to_float(objects.dimensions[0] / 12);
        let d2 = to_float(objects.dimensions[1] / 12);
        let d3 = to_float(objects.dimensions[2] / 12);
        let d4 = to_float(objects.dimensions[3] / 12);

        let sd1 = d1/2;
        let sd2 = d2/2;

        let vertArray2 = [];
        let irv = (d1 - 0.001) / 50;
        let MaxY = sd2;
        let Rat = sd1 / MaxY;


        for(let x =- sd1; x <= sd1; x += irv){
            let y = Math.sqrt(sd1 * sd1 - x * x);
            vertArray.push( new THREE.Vector3(x, y / Rat , 0));
            vertArray2.push( new THREE.Vector3(x, y / Rat + d4 , 0));
        }

        for(let i=1; i<=vertArray2.length; i++) {
            vertArray.push(vertArray2[vertArray2.length-i]);
        }

        vertArray.push( new THREE.Vector3(-sd1, 0 , 0));


        let material = getColorOrTexture(objects);
        let shape = new THREE.Shape(vertArray);

        let extrudeSettings = {
            steps: 1,
            amount: d3,
            bevelEnabled: false,
            bevelThickness: 0,
            bevelSize: 0,
            bevelSegments: 0
        };
        let geometry = new THREE.ExtrudeGeometry( shape, extrudeSettings );


        let obj = new THREE.Mesh( geometry, material );

        obj.position.set(-d4 - d2/2, -d3/2, 0);
        obj.rotation.set(-Math.PI/2, 0, -Math.PI/2);


        let group = new THREE.Mesh();


        group.type = 'equipment';
        group.eqpt_id = objects.product.id;
        group._row_hash = setCached(objects._row_hash, 'eqpt');

        group.add(obj);

        if(lc_data) {
            group.lc_id = lc_data._id;
            group._lc_extra = lc_data;

            let newD = rotateLcNodes(lc_data, group, parent);

            lc_data.tdx = newD.dx;
            lc_data.tdy = newD.dy;
            lc_data.tdz = newD.dz;

            group.position.set(lc_data.tdx ? to_float(lc_data.tdx) : 0, lc_data.tdy ? to_float(lc_data.tdy) : 0, lc_data.tdz ? to_float (lc_data.tdz) : 0);
            group.rotation.set(lc_data.rotx * (Math.PI/180), (lc_data.roty * (Math.PI/180)) + newD.roty, lc_data.rotz * (Math.PI/180));
        } else {
            group.position.set(0,0,0);
        }

        createEdges(group);

        let eqptLabel = makeTextSprite(label, {
            fontsize: 32
        });

        adaptSprite(eqptLabel, dimensions);

        eqptLabel.type = 'equipmentLabel';
        eqptLabel.visible = false;
        adaptFigureScale(eqptLabel, camera);
        group.add(eqptLabel);

        parent.add(group);

    }

    function rotateLcNodes(lc, mesh, parent) {
        if (parent) {
            let glob_top = window.threeHelper.glob_top_vector(parent, false);
            let glob_idx = Math.abs(glob_top.y);

            //important centers
            let global_center = to_float(lc._ma_gctr);
            let g_member_center = global_center + to_float(parent.position.y);
            let is_horizontal = glob_top.y == 0;
            let changed = false;

            //fill if empty
            if (lc.elev_g === null) {
                let value = g_member_center;
                if (to_float(lc.dist_to_node_s) && window.threeHelper.consistent_data(lc, parent, 'dist_to_node_s')) {
                    value += lc.dist_to_node_s * glob_idx;
                }
                lc.elev_g = Number(value).toFixed(2);
                changed = true;
            }
            if (lc.dist_to_node_s === null || lc.dy === null) {
                let value = 0;
                if (to_float(lc.elev_g) && window.threeHelper.consistent_data(lc, parent, 'elev_g')) {
                    value = (to_float(lc.elev_g) - g_member_center) / glob_idx;
                }
                if (lc.dist_to_node_s === null) {
                    lc.dist_to_node_s = lc.dy || Number(value).toFixed(2);
                }
                if (lc.dy === null) {
                    lc.dy = lc.dist_to_node_s || Number(value).toFixed(2);
                }
                changed = true;
            }

            //apply changes
            let watched = window.threeHelper.watcher3d_check(lc);
            if (watched) {
                watched = window.threeHelper.limit_by_member(lc, parent, watched);
                let w3d = window.threeHelper.watcher3d_field();
                if (w3d === 'origin_elev' && to_float(watched.val) && Math.abs(to_float(watched.old_val) - to_float(watched.val)) > 0.1) {
                    let elev_diff = to_float(watched.old_val) - to_float(watched.val);
                    let new_dist = (Number(lc.dist_to_node_s) + elev_diff/glob_idx).toFixed(2);
                    if (!is_horizontal && window.threeHelper.consistent_data(lc, parent, 'dist_to_node_s', new_dist)) {
                        lc.dist_to_node_s = new_dist;
                        lc.dy = lc.dist_to_node_s;
                    } else {
                        lc.elev_g = (to_float(lc.elev_g) + to_float(elev_diff)).toFixed(2);
                    }
                }
                if (w3d === 'elev_g' && window.threeHelper.consistent_data(lc, parent, 'elev_g', watched.val)) {
                    lc.elev_g = to_float(watched.val).toFixed(2);
                    lc.dist_to_node_s = Number((to_float(lc.elev_g) - g_member_center) / glob_idx).toFixed(2);
                    lc.dy = lc.dist_to_node_s;
                }
                if (['dist_to_node_s','dy'].indexOf(w3d) > -1 && window.threeHelper.consistent_data(lc, parent, 'dist_to_node_s', watched.val)) {
                    lc.dist_to_node_s = to_float(watched.val).toFixed(2);
                    lc.dy = lc.dist_to_node_s;
                    lc.elev_g = Number(g_member_center + lc.dist_to_node_s * glob_idx).toFixed(2);
                }
                changed = true;
                window.threeHelper.watcher3d_processed(lc);
            }

            //check consistence
            if (!window.threeHelper.consistent_data(lc, parent, 'elev_g')) {
                lc.elev_g = Number(g_member_center).toFixed(2);
                changed = true;
            }
            if (!window.threeHelper.consistent_data(lc, parent, 'dist_to_node_s')) {
                lc.dist_to_node_s = Number(0).toFixed(2);
                lc.dy = lc.dist_to_node_s;
                changed = true;
            }
            if (lc.dist_to_node_s != lc.dy) {
                lc.dy = lc.dist_to_node_s;
                changed = true;
            }
            let inconsistent_diff = Math.abs(to_float(lc.dist_to_node_s)*glob_idx - (to_float(lc.elev_g)-g_member_center)) > 0.1;
            if (!is_horizontal && inconsistent_diff) {
                lc.elev_g = Number(g_member_center).toFixed(2);
                lc.dist_to_node_s = Number(0).toFixed(2);
                lc.dy = lc.dist_to_node_s;
                changed = true;
            }

            //save changes
            if (changed) {
                if (!to_float(lc.elev_pd)) {
                    lc.elev_pd = lc.elev_g;
                }
                axios.post('?method=save_model', {
                    app_table: lc._app_tb,
                    model: {
                        _id: lc._id,
                        dy: lc.dy,
                        elev_g: lc.elev_g,
                        elev_pd: lc.elev_pd,
                        dist_to_node_s: lc.dist_to_node_s,
                    },
                }).then(({data}) => {
                    if (typeof onDistCalc == "function") {
                        onDistCalc.call({}, lc);
                    }
                }).catch(errors => {
                    Swal('Info', getErrors(errors));
                });
            }
        }

        // console.log(parent);
        // console.log(lc);
        return window.threeHelper.eqpt_center(lc, parent);
    }

    function createEdges(group) {

        group.traverse(function (child) {

            if (child instanceof THREE.Mesh) {
                let wfh = new THREE.EdgesHelper(child, viewSettings.edges_color || "#ffffff");

                wfh.material.opacity = 0.9;
                wfh.material.transparent = true;
                wfh._sett_type = 'eqpts';
                wfh.visible = wfh._sett_type === 'eqpts' && viewSettings.edges_eqpts && viewSettings.objects;

                scene.add(wfh);
            }

        });

    }


    function removeByType(parent, type) {
        var temp = [];
        var temp1 = [];

        parent.traverse(function (child) {
            if (child instanceof THREE.Mesh || child instanceof THREE.Line || child instanceof THREE.Object3D) {
                if (child.type == type && !isPresent(child, type)) {
                    temp.push(child);
                }
            }
        });

        for (var key in temp) {
            parent.remove(temp[key]);
        }

        scene.traverse(function (child) {
            if (child instanceof THREE.Sprite) {
                if (child.type == type && !isPresent(child, type)) {
                    temp1.push(child);
                }
            }
        });

        for (var key in temp1) {
            scene.remove(temp1[key]);
        }
    }

    function addAxis(parent) {

        var axisRadius = 0.01;
        var axisLength = 2;
        var axisTess = 24;

        var axisXMaterial = new THREE.MeshLambertMaterial({ color: 0xFF0000 });
        var axisYMaterial = new THREE.MeshLambertMaterial({ color: 0x00FF00 });
        var axisZMaterial = new THREE.MeshLambertMaterial({ color: 0x0000FF });
        axisXMaterial.side = THREE.DoubleSide;
        axisYMaterial.side = THREE.DoubleSide;
        axisZMaterial.side = THREE.DoubleSide;
        var axisX = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisXMaterial
        );
        var axisY = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisYMaterial
        );
        var axisZ = new THREE.Mesh(
            new THREE.CylinderGeometry(axisRadius, axisRadius, axisLength, axisTess, 1, true),
            axisZMaterial
        );
        axisX.rotation.z = - Math.PI / 2;
        axisX.position.x = axisLength/2-1;

        axisY.position.y = axisLength/2-1;

        axisZ.rotation.y = - Math.PI / 2;
        axisZ.rotation.z = - Math.PI / 2;
        axisZ.position.z = axisLength/2-1;

        var group = new THREE.Mesh();

        group.add( axisX );
        group.add( axisY );
        group.add( axisZ );

        var arrowX = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 4*axisRadius, 18*axisRadius, axisTess, 1, true),
            axisXMaterial
        );
        var arrowY = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 4*axisRadius, 18*axisRadius, axisTess, 1, true),
            axisYMaterial
        );
        var arrowZ = new THREE.Mesh(
            new THREE.CylinderGeometry(0, 4*axisRadius, 18*axisRadius, axisTess, 1, true),
            axisZMaterial
        );
        arrowX.rotation.z = - Math.PI / 2;
        arrowX.position.x = axisLength - 1 + axisRadius*4/2;

        arrowY.position.y = axisLength - 1 + axisRadius*4/2;

        arrowZ.rotation.z = - Math.PI / 2;
        arrowZ.rotation.y = - Math.PI / 2;
        arrowZ.position.z = axisLength - 1 + axisRadius*4/2;

        group.add( arrowX );
        group.add( arrowY );
        group.add( arrowZ );

        group.type = 'axises_help';

        //rotate axis helper in Local Coord System
        //axesHelperContainer.rotation.setFromQuaternion(parent.getWorldQuaternion());

        parent.add(group);
    }

    function removeAxis(parent) {
        var temp = [];

        parent.traverse(function (child) {
            if (child.type == 'axises_help') {
                temp.push(child);
            }
        });

        for (var key in temp) {
            parent.remove(temp[key]);
        }
    }

    function AspectDPoSS() {
        var box = new THREE.Box3().setFromObject(ais);
        var max = Math.abs(Math.max(box.size().x, box.size().y, box.size().z));

        if (max < Infinity) {
            if (camera.inPerspectiveMode) {
                camera.position.y = max*2;
                controls.target.set(0, box.size().y / 2, 0);

                gridZX.scale.set(max, max, max);
                gridXY.scale.set(max, max, max);
                gridYZ.scale.set(max, max, max);

                Axises.scale.set(max, max, max);
            } else if (camera.inOrthographicMode) {
                camera.setZoom(camera.zoom / max);
                controls.target.set(0, 0, 0);
            }
        }

        // camera.position.z = Math.abs(Math.max(box.size().x, box.size().y, box.size().z) + 5);
        // camera.lookAt(scene.position);
    }

    function Aspect() {
        var box = new THREE.Box3().setFromObject(ais);
        var max = Math.abs(Math.max(box.size().x, box.size().y, box.size().z));

        // camera.position.z = Math.abs(Math.max(box.size().x, box.size().y, box.size().z) + 5);
        // camera.lookAt(scene.position);
    }

    function Selected(callback) {
        onSelected = callback;
    }

    function RigthClickSelected(callback) {
        onRigthClickSelect = callback;
    }

    function cameraUpdate(callback) {
        onCameraUpdate = callback;
    }

    function onCalcDist(callback) {
        onDistCalc = callback;
    }

    function addLabel(text, props) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        canvas.width = 75;
        canvas.height = 75;
        context.font = props.fontSize + 'px Arial';
        context.textAlign = 'center';
        context.textBaseline = 'bottom';
        context.fillStyle = '#565656';
        context.fillText(text, context.measureText(text).width, props.fontSize * 2);

        const texture = new THREE.Texture(canvas);
        texture.needsUpdate = true;

        const spriteMaterial = new THREE.SpriteMaterial({map: texture});

        const sprite = new THREE.Sprite(spriteMaterial);
        sprite.position.set(0, 0, 0);
        sprite.center = new THREE.Vector2(-1, -1);
        if(props.position) sprite.position.set(props.position.x, props.position.y, props.position.z);

        return sprite;
    }

    function makeTextSprite(message, parameters) {
        if (parameters === undefined) {
            parameters = {};
        }

        var fontface = parameters.hasOwnProperty("fontface") ?
            parameters["fontface"] : "Arial";

        var fontsize = parameters.hasOwnProperty("fontsize") ?
            parameters["fontsize"] : 18;

        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');

        context.font = fontsize + "px " + fontface;
        context.fillStyle = "rgba(0, 0, 0, 1.0)";
        context.fillText(message, 0, fontsize * 2);

        var texture = new THREE.Texture(canvas);
        texture.needsUpdate = true;

        var spriteMaterial = new THREE.SpriteMaterial({
            map: texture
        });

        var sprite = new THREE.Sprite(spriteMaterial);
        sprite.position.set(0, 0, 1);
        // sprite.scale.set(2, 1, 1);
        adaptFigureScale(sprite, camera);
        return sprite;
    }

    function GetStaticLabel(text, font, direction) {
        font = font || 14;

        var canvas = document.createElement('canvas');
        canvas.width = 100;
        canvas.height = 40;

        var context = canvas.getContext('2d');
        context.font = font + "px Arial";
        context.fillStyle = "rgba(0, 0, 0, 1)";
        context.fillText(text, 0, 14);

        var texture = new THREE.Texture(canvas);
        texture.needsUpdate = true;

        var material = new THREE.MeshBasicMaterial({
            map: texture,
            side: THREE.DoubleSide,
            transparent: true
        });

        var geometry = new THREE.PlaneGeometry(canvas.width, canvas.height);

        if (direction !== undefined) {
            if (direction === true) {
                geometry.applyMatrix(new THREE.Matrix4().makeRotationZ(Math.PI / 2));
                geometry.applyMatrix(new THREE.Matrix4().makeRotationY((-Math.PI / 3) - 0.1));
            } else {
                geometry.applyMatrix(new THREE.Matrix4().makeRotationZ(-Math.PI / 2));
                geometry.applyMatrix(new THREE.Matrix4().makeRotationY((-Math.PI / 3) - 0.3));
            }
        }

        geometry.verticesNeedUpdate = true;

        var mesh = new THREE.Mesh(
            geometry,
            material
        );

        mesh.position.set(0, 0, 0);
        mesh.scale.set(0.01, 0.01, 0.01);

        return mesh;
    }

    function adaptSprite(sprite, dimensions) {
        sprite.scale.set(1, 0.58, 1);
        if (dimensions.radius) {
            sprite.position.set(0, 1, dimensions.radius / 14);
            return;
        }
        sprite.position.set(-0.1, 0, dimensions.width / 14);
    }

    function SetAlongLine(marker, points, delta) {
        var up = new THREE.Vector3(0, 1, 0);

        var spline = new THREE.SplineCurve3(points);
        var pt = spline.getPoint(delta);

        var tangent = spline.getTangent(delta).normalize();
        var axis = new THREE.Vector3().crossVectors(up, tangent).normalize();

        var radians = Math.acos(up.dot(tangent));

        marker.position.set(pt.x, pt.y, pt.z);
        marker.quaternion.setFromAxisAngle(axis, radians);

        //var material = new THREE.LineBasicMaterial({
        //    color: 0xff00f0
        //});
        //
        //var geometry = new THREE.Geometry();
        //for (var i = 0; i < spline.getPoints(100).length; i++) {
        //    geometry.vertices.push(spline.getPoints(100)[i]);
        //}
        //
        //var line = new THREE.Line(geometry, material);
        //scene.add(line);
    }

    function allClear() {
        if (ais) {
            clearScene(ais);
            clearRL(ais);
        }
    }

    /**
     * get color or texture for shape
     * @param objects
     * @returns {*}
     */
    function getColorOrTexture(objects) {
        let material;
        if (objects.texture && String(objects.texture.type).toLowerCase() == 'texture') {
            material = new THREE.MeshLambertMaterial();
            material.map = THREE.ImageUtils.loadTexture('/file/' + objects.texture.name);
            material.map.wrapS = material.map.wrapT = THREE.RepeatWrapping;
        } else {
            material = createPhongMaterial({ color: objects.texture.color || '#2222ff' });
        }
        return material;
    }

    /**
     *
     * @param ais
     */
    function clearRL(ais) {
        for (var key in rl_meshes) {
            ais.remove(rl_meshes[key]);
        }
        rl_meshes = [];
    }

    /**
     *
     */
    function drawRL(ais, equipments, rls, extra)
    {
        clearRL(ais);
        let hide_with_eqpt = extra && extra.rl_sett && extra.rl_sett.hidden;

        if (rls) {
            _.each(rls.rows, (el) => {
                let eq_vis = !hide_with_eqpt ||
                    _.find(equipments, (eq) => {
                        return eq.lc.equipment == el.equipment
                            && eq.lc.mbr_id == el.lc_rec_id;
                    });
                if (el.mbr_node && el.eqpt_node && eq_vis) {
                    var lcolor = el.lc_color || viewSettings.defRLColor || '#0000ff';
                    var mbrMat = createPhongMaterial({color: lcolor});
                    mbrMat.needsUpdate = true;
                    var mbrNode = new THREE.Vector3( parseFloat(el.mbr_node.x), parseFloat(el.mbr_node.y), parseFloat(el.mbr_node.z) );
                    var eqptNode = new THREE.Vector3( parseFloat(el.eqpt_node.x), parseFloat(el.eqpt_node.y), parseFloat(el.eqpt_node.z) );
                    var RL_line = new THREE.Line3(mbrNode, eqptNode);
                    var height = mbrNode.distanceTo(eqptNode);
                    var radius = (viewSettings.rl_size || 1) / 24;
                    var geometry = new THREE.CylinderGeometry( radius, radius, height, 36 );
                    var cylinder = new THREE.Mesh( geometry, mbrMat );
                    cylinder.position.copy( RL_line.center() );
                    cylinder.lookAt(eqptNode);
                    cylinder.rotateX(Math.PI/2);
                    cylinder.rotateY(-Math.PI/2);
                    cylinder.rl_id = el._id;
                    cylinder.single_type = 'rl_bracket';
                    cylinder.visible = !!viewSettings.rl_view;
                    cylinder.color_lc = lcolor;
                    rl_meshes.push(cylinder);
                    ais.add( cylinder );
                }
            });
        }
    }

    /**
     *
     * @type {Run}
     */
    exports.run = Run;
    exports.enableAxesMinimap = EnableAxesMinimap;
    exports.draw = Draw;
    exports.clearAll = allClear;
    exports.selected = Selected;
    exports.outerSelect = outerSelect;
    exports.cameraUpdate = cameraUpdate;
    exports.cameraChange = changeCamera;
    exports.onCalcDist = onCalcDist;
    exports.rightClickSelected = RigthClickSelected;
    exports.changeGridSettings = ChangeGridSettings;
    exports.changeCameraPosition = ChangeCameraPosition;
    exports.getCurentScreenshotURL = GetCurentScreenshotURL;
    exports.changeViewSettingsWID = ChangeViewSettingsWID;
    exports.changeViewSettingsWLSC = ChangeViewSettingsWLSC;
    exports.changeViewSettingsDPOSS = ChangeViewSettingsDPOSS;
})(window.webgl || (window.webgl = {}));