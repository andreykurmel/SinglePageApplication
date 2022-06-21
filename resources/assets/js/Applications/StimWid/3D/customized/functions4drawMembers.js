function getParams(script) {
    // Find all script tags
    var scripts = document.getElementsByTagName("script");

    // Look through them trying to find ourselves
    for (var i = 0; i < scripts.length; i++) {
        if (scripts[i].src.indexOf("/" + script) > -1) {
            // Get an array of key=value strings of params
            var pa = scripts[i].src.split("?").pop().split("&");

            // Split each key=value into array, the construct js object
            var p = {};

            for (var j = 0; j < pa.length; j++) {
                var kv = pa[j].split("=");

                p[kv[0]] = kv[1];
            }

            return p;
        }
    }

    // No scripts match
    return {};
}

function getshapeDataX(dbSRC, Type, SizeType, SizeUnit, Size1, Size2) {
    var params = getParams("functions4drawMembers.js");
    var path = params.path || '';

    var shapeResult = jQuery.ajax({
        url: path + "db/talk2db.php",
        method: "POST",
        data: {
            action: "get_aisc_sizes_detail",
            Data2Query: 'MtStr_G',
            Fields: 'Shape',
            dbSRC: dbSRC,
            Type: Type,
            SizeType: SizeType,
            SizeUnit: SizeUnit,
            Size1: Size1,
            Size2: Size2
        },
        dataType: 'json',
        async: false
    }).responseText;
    var shapeArray = jQuery.parseJSON(shapeResult);

    return (shapeArray[0]);
}

function extrudeShape(points, height) {
    var shape = new THREE.Shape();
    shape.moveTo(points[0][0], 0);
    for (var i = 0; i < points.length; i++) {
        shape.lineTo(points[i][0], points[i][1]);
    }
    var mat = new THREE.MeshLambertMaterial();
    geometry = new THREE.ExtrudeGeometry(shape, {
        bevelEnabled: false,
        amount: height,
        uvGenerator: THREE.ExtrudeGeometry.WorldUVGenerator
    });
    geometry.applyMatrix(new THREE.Matrix4().makeRotationX(-Math.PI / 2));
    mesh = new THREE.Mesh(geometry, mat);
    return mesh;
}

function makeHolepath(ox, oy, holeShape, holeSize) {

    switch (holeShape) {
        case 'Square':
        case 'square':
            var sideLength = holeSize;
            var holePath = new THREE.Path();
            holePath.moveTo(-sideLength / 2 + ox, +sideLength / 2 + oy);
            holePath.lineTo(-sideLength / 2 + ox, -sideLength / 2 + oy);
            holePath.lineTo(+sideLength / 2 + ox, -sideLength / 2 + oy);
            holePath.lineTo(+sideLength / 2 + ox, +sideLength / 2 + oy);
            holePath.lineTo(-sideLength / 2 + ox, +sideLength / 2 + oy);
            break;

        case 'Circular':
        case 'circular':
            var holeRadius = holeSize;
            var holePath = new THREE.Path();
            holePath.absarc(ox, oy, holeRadius, 0, Math.PI * 2, true);
            break;

        default:
            break;
    }

    return holePath;

}

function memberAlignment(geometry, orginAt, dir) {

    var Rot_X_angle = 0;

    switch (dir) {
        case 'nx':
            break;
        case 'px':
            break;
        case 'ny':
            Rot_X_angle = +Math.PI / 2;
            break;
        case 'py':
            Rot_X_angle = -Math.PI / 2;
            break;
        case 'nz':
            break;
        case 'pz':
            break;
        default:
            Rot_X_angle = 0;
            break;
    }

    geometry.applyMatrix(new THREE.Matrix4().makeRotationX(Rot_X_angle));

    switch (orginAt) {
        case 'start':
            break;

        case 'center':
            geometry.center(geometry);
            break;

        case 'end':
            break;

        default:
            break;
    }

    return geometry;

}

function extrudeLinearMember(crosec, NodeS, NodeE) {

    var lineCurve = new THREE.LineCurve(
        new THREE.Vector3(parseFloat(NodeS[0]), parseFloat(NodeS[1]), parseFloat(NodeS[2])),
        new THREE.Vector3(parseFloat(NodeE[0]), parseFloat(NodeE[1]), parseFloat(NodeE[2]))
    );



    var v1 = new THREE.Vector3(parseFloat(NodeS[0]), parseFloat(NodeS[1]), parseFloat(NodeS[2])),
        v2 = new THREE.Vector3(parseFloat(NodeE[0]), parseFloat(NodeE[1]), parseFloat(NodeE[2]));


    // geometry = new THREE.ExtrudeGeometry(crosec, {
    //     bevelEnabled: false,
    //     amount: '',
    //     steps: 1,
    //     extrudePath: lineCurve
    // });

    var geometry = new THREE.ExtrudeGeometry(crosec, {
        bevelEnabled: false,
        steps: 1,
        amount: v1.distanceTo( v2 )
    });

    // geometry.applyMatrix(new THREE.Matrix4().makeRotationY(rot_x2stdy));
    // geometry.verticesNeedUpdate = true;
    return geometry;
}