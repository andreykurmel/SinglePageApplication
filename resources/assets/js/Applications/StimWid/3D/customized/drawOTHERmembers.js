function drawArbitrary(dir, nodalCrdts, length, lthDir, orginAt, offset_rot, hole, holeShape, holeSize, material, description){

	// rectPoints_bfRot = [[-xLength/2, -yLength/2],
	// 					[-xLength/2,  yLength/2], 
	// 					[xLength/2,  yLength/2], 
	// 					[xLength/2, -yLength/2]];

	// rectPoints_afRot = [];

	// var rectPoints = [];
	// for( var inode = 0; inode < 4; inode++ ){
	// 	console.log('rectPoints_bfRot[0]: ' + rectPoints_bfRot[0]);
	// 	rectPoints_afRot[inode] = [rectPoints_bfRot[inode][0]*Math.cos(offset_rot) + rectPoints_bfRot[inode][1]*Math.sin(offset_rot), 
	// 						   	  -rectPoints_bfRot[inode][0]*Math.sin(offset_rot) + rectPoints_bfRot[inode][1]*Math.cos(offset_rot)];
	// 	rectPoints.push( new THREE.Vector3 (rectPoints_afRot[inode][0], rectPoints_afRot[inode][1]));							   	   
	// };

	var arbitraryPoints_bfRot = [];
	var arbitraryPoints_afRot = [];
	var arbitraryPoints = [];
	var nodeNbr = nodalCrdts.length;
	for( var inode = 0; inode < nodeNbr; inode++ ){
		arbitraryPoints_bfRot[inode] = [Number(nodalCrdts[inode].x), Number(nodalCrdts[inode].y)];
		arbitraryPoints_afRot[inode] = [arbitraryPoints_bfRot[inode][0]*Math.cos(offset_rot) - arbitraryPoints_bfRot[inode][1]*Math.sin(offset_rot), 
		+arbitraryPoints_bfRot[inode][0]*Math.sin(offset_rot) + arbitraryPoints_bfRot[inode][1]*Math.cos(offset_rot)];			
		arbitraryPoints.push( new THREE.Vector3 (arbitraryPoints_afRot[inode][0], arbitraryPoints_afRot[inode][1]));
	}

	var shape = new THREE.Shape(arbitraryPoints);

	//console.log('hole: ' + hole + ', holeShape: ' +  holeShape +  ', holeSize: ' + holeSize);
	var holePath = [];
	if(hole==1 && holeShape && holeSize){
		console.log('has hole: yes! ');
		var ox = 0; oy = 0;
		var ox = 0; oy = 0;
		var holePath = makeHolepath(ox, oy, holeShape, holeSize);
		shape.holes.push( holePath );
	}
	geometry = new THREE.ExtrudeGeometry( shape, {bevelEnabled: false, amount:length} );

	// geometry = memberAlignment(orginAt, geometry);
	geometry = memberAlignment(geometry, orginAt, dir);
	
	mesh = new THREE.Mesh( geometry, setMaterial(material) );
	mesh.name = 'Arbitrary cross section member';	
	mesh.dimensions = {};
	mesh.dimensions.hole = hole;
	mesh.dimensions.holeShape = holeShape;
	mesh.dimensions.holeSize  = holeSize;
	mesh.dimensions.length = length;
	mesh.localVertex = arbitraryPoints;
	mesh.matValue = material;	
	mesh.description = description;
	//ray_objects.push(mesh);
	//console.log('drawArbitrary', mesh);
	return(mesh)
}


function drawRectangle(dir, D1, D2, D3, D4, D5, length, lthDir, orginAt, offset_roty, hole, holeShape, holeSize, material, description){

	var xLength = D1;
	var yLength = D2; 

	rectPoints_bfRot = [[-xLength/2, -yLength/2],
	[-xLength/2,  yLength/2], 
	[xLength/2,  yLength/2], 
	[xLength/2, -yLength/2]];

	rectPoints_afRot = [];

	switch(dir){
		case 'ny':
			offset_roty = -offset_roty;
		break;
		default:
		break;
	}

	var rectPoints = [];
	for( var inode = 0; inode < 4; inode++ ){
		console.log('rectPoints_bfRot[0]: ' + rectPoints_bfRot[0]);
		rectPoints_afRot[inode] = [rectPoints_bfRot[inode][0]*Math.cos(offset_roty) - rectPoints_bfRot[inode][1]*Math.sin(offset_roty), 
		+rectPoints_bfRot[inode][0]*Math.sin(offset_roty) + rectPoints_bfRot[inode][1]*Math.cos(offset_roty)];
		rectPoints.push( new THREE.Vector3 (rectPoints_afRot[inode][0], rectPoints_afRot[inode][1]));							   	   
	};

	// rectPoints.push( new THREE.Vector3 (-xLength/2,  yLength/2 ));
	// rectPoints.push( new THREE.Vector3 ( xLength/2,  yLength/2 ));
	// rectPoints.push( new THREE.Vector3 ( xLength/2, -yLength/2 ));

	var shape = new THREE.Shape(rectPoints);

	console.log('hole: ' + hole + ', holeShape: ' +  holeShape +  ', holeSize: ' + holeSize);
	var holePath = [];
	if(hole==1 && holeShape && holeSize){
		console.log('has hole: yes! ');
		var ox = 0; oy = 0; 
		holePath = makeHolepath(ox, oy, holeShape, holeSize);
		shape.holes.push( holePath );		
	}
	geometry = new THREE.ExtrudeGeometry( shape, {bevelEnabled: false, amount:length} );

	// geometry = memberAlignment(orginAt, geometry);
	geometry = memberAlignment(geometry, orginAt, dir);

	mesh = new THREE.Mesh( geometry, setMaterial(material) );
	mesh.name = 'Rect: xLength-yLength = ' + xLength + ' x ' + yLength;	
	mesh.dimensions = {};
	mesh.dimensions.xLength = xLength;
	mesh.dimensions.yLength = yLength;	
	mesh.dimensions.hole = hole;
	mesh.dimensions.holeShape = holeShape;			
	mesh.dimensions.holeSize  = holeSize;
	mesh.dimensions.length = length;
	mesh.localVertex = rectPoints;
	mesh.matValue = material;	
	mesh.description = description;


//	ray_objects.push(mesh);
//    console.log('drawRectangle', mesh);
return(mesh)
}


// function drawRegular(dir, shapeSize, D1, material, centerPivot, hole, holeSize, holeType, regEdges, regEdgeLen){
	function drawRegularPolygon(dir, D1, D2, D3, D4, D5, length, lthDir, orginAt, offset_roty, hole, holeShape, holeSize, material, description){

		var sidenbr = D1; 
		var sidelth = D2; 	

		var results = polygonQuery(sidenbr, sidelth, undefined, undefined, undefined, undefined);
		var anglePerside = results[0];
// var sidelth
var OD_f2f = results[2];
var OD_c2c = results[3];
var crfrc  = results[4];

	switch(dir){
		case 'ny':
			offset_roty = -offset_roty; // positive value means positive in the cs (xy->z) of object without rotation. This adjustment is to accomodate the rotation.
		break;
		default:
		break;
	}

var RP_cnrs = [];

var cnr_angle = 0, x = 0, y = 0, z = 0;

for( var i = 0; i < sidenbr; i++ ) {
	x_bfRot = OD_c2c/2 * Math.cos( cnr_angle );
	z_bfRot = OD_c2c/2 * Math.sin( cnr_angle );

	x_afRot = x_bfRot*Math.cos(offset_roty) - z_bfRot*Math.sin(offset_roty);
	z_afRot = x_bfRot*Math.sin(offset_roty) + z_bfRot*Math.cos(offset_roty);

	RP_cnrs.push(new THREE.Vector2(x_afRot, z_afRot));
	// RP_cnrs.push(new THREE.Vector3(x_afRot, 0 , z_afRot));
	cnr_angle += anglePerside;
}   
var shape = new THREE.Shape(RP_cnrs);

if(hole && holeSize != undefined){
	var ox = 0; oy = 0; 
	var holePath = makeHolepath(ox, oy, holeShape, holeSize);
	shape.holes.push( holePath );	
}

// var v1 = new THREE.Vector3( 0, 0, 0 );
// var v2 = new THREE.Vector3( 0, -length, 0 );
// var path = new THREE.LineCurve3( v1, v2 )
// var extrudeSettings = {bevelEnabled: false, steps: 1, extrudePath: path};
// var geometry = new THREE.ExtrudeGeometry( shape,  extrudeSettings);

var geometry = new THREE.ExtrudeGeometry( shape, {bevelEnabled: false, amount:length} );

// geometry = memberAlignment(orginAt, geometry);
geometry = memberAlignment(geometry, orginAt, dir);

mesh = new THREE.Mesh(geometry, setMaterial(material));
mesh.dimensions = {};
mesh.dimensions.sidenbr = sidenbr;
mesh.dimensions.sidelength = sidelth;
mesh.dimensions.thickness = length;
mesh.localVertex = RP_cnrs;
mesh.name = 'Regular polygon';

	//ray_objects.push(mesh);
	mesh.matValue = material;
        //console.log('drawRegularPolygon', mesh);
	return(mesh);
};

function drawCircular(dir, D1, D2, D3, D4, D5, length, lthDir, orginAt, hole, holeShape, holeSize, material, description){

	var OR = D1; // outer radius

	var shape = new THREE.Shape();
	// shape.arc(center_x, center_y, radius, start_angle, end_angle, clockwise);
	shape.absarc( 0, 0, OR, Math.PI*2, 0, true );

	if(hole == 1 && holeShape && holeSize != undefined){
		var ox = 0; oy = 0; 
		var holePath = makeHolepath(ox, oy, holeShape, holeSize);
		shape.holes.push( holePath );
	}

	geometry = new THREE.ExtrudeGeometry( shape, {bevelEnabled: false, amount:length} );

	// geometry = memberAlignment(orginAt, geometry);
	geometry = memberAlignment(geometry, orginAt, dir);

	mesh = new THREE.Mesh( geometry, setMaterial(material, 'round') );
	mesh.dimensions = {};
	mesh.dimensions.OR = OR;
	mesh.dimensions.length = length;
	mesh.name = 'Circ-OR: ' + OR;

	var localVertex = [];	
	localVertex.push({x: mesh.position.x, y: mesh.position.y, z: mesh.position.z});
	//ray_objects.push(mesh);
	mesh.localVertex = localVertex;
	mesh.matValue = material;
    //console.log('drawCircular', mesh);
	return(mesh);
}


function drawPolygonSection(dir, nbrSides, thickness, ctr2cnr_s, ctr2cnr_e, lth_s2e, rot_1stcnr){
	var degreePerside = 360 / nbrSides;
	var geometry = new THREE.Geometry();
	var i, degreeAtcorner, face;

	if(!dir) dir = 'py';
	if(!rot_1stcnr) rot_1stcnr = 0;

    //Vertices
    //First ring
    for (i = 0; i < nbrSides; i++) {
    	degreeAtcorner = rot_1stcnr + (i * degreePerside) * Math.PI / 180;
    	geometry.vertices.push(new THREE.Vector3(Math.cos(degreeAtcorner) * ctr2cnr_s, 0, Math.sin(degreeAtcorner) * ctr2cnr_s));
    }

    for (i = 0; i < nbrSides; i++) {
    	degreeAtcorner = rot_1stcnr + (i * degreePerside) * Math.PI / 180;
    	geometry.vertices.push(new THREE.Vector3(Math.cos(degreeAtcorner) * (ctr2cnr_s - thickness), 0, Math.sin(degreeAtcorner) * (ctr2cnr_s - thickness)));
    }

    //Second ring
    for (i = 0; i < nbrSides; i++) {
    	degreeAtcorner = rot_1stcnr + (i * degreePerside) * Math.PI / 180;
    	geometry.vertices.push(new THREE.Vector3(Math.cos(degreeAtcorner) * ctr2cnr_e, lth_s2e, Math.sin(degreeAtcorner) * ctr2cnr_e));
    }

    for (i = 0; i < nbrSides; i++) {
    	degreeAtcorner = rot_1stcnr + (i * degreePerside) * Math.PI / 180;
    	geometry.vertices.push(new THREE.Vector3(Math.cos(degreeAtcorner) * (ctr2cnr_e - thickness), lth_s2e, Math.sin(degreeAtcorner) * (ctr2cnr_e - thickness)));
    }

    //Faces
    var ringStep = 2 * nbrSides;
    for (i = 0; i < nbrSides - 1; i++) {

    	//First ring
    	face = new THREE.Face3(
    		i,
    		i + 1,
    		i + nbrSides + 1);
    	geometry.faces.push(face);
    	face = new THREE.Face3(
    		i + nbrSides + 1,
    		i + nbrSides,
    		i);
    	geometry.faces.push(face);

    	//Second ring
    	face = new THREE.Face3(
    		i + ringStep,
    		i + ringStep + nbrSides,
    		i + ringStep + nbrSides + 1);
    	geometry.faces.push(face);
    	face = new THREE.Face3(
    		i + ringStep + nbrSides + 1,
    		i + ringStep + 1,
    		i + ringStep);
    	geometry.faces.push(face);

    	//Inner mesh
    	face = new THREE.Face3(
    		i + nbrSides + 1,
    		i + ringStep + nbrSides + 1,
    		i + ringStep + nbrSides);
    	geometry.faces.push(face);
    	face = new THREE.Face3(
    		i + ringStep + nbrSides,
    		i + nbrSides,
    		i + nbrSides + 1);
    	geometry.faces.push(face);

    	//Outer mesh
    	face = new THREE.Face3(
    		i,
    		i + ringStep,
    		i + ringStep + 1);
    	geometry.faces.push(face);
    	face = new THREE.Face3(
    		i + ringStep + 1,
    		i + 1,
    		i);
    	geometry.faces.push(face);
    }

    //Last round
    //First ring
    face = new THREE.Face3(
    	nbrSides - 1,
    	0,
    	nbrSides);
    geometry.faces.push(face);
    face = new THREE.Face3(
    	nbrSides,
    	2 * nbrSides - 1,
    	nbrSides - 1);
    geometry.faces.push(face);

    //Second ring
    face = new THREE.Face3(
    	nbrSides - 1 + ringStep,
    	2 * nbrSides - 1 + ringStep,
    	nbrSides + ringStep);
    geometry.faces.push(face);
    face = new THREE.Face3(
    	nbrSides + ringStep,
    	ringStep,
    	nbrSides - 1 + ringStep);
    geometry.faces.push(face);

    //Inner mesh
    face = new THREE.Face3(
    	nbrSides,
    	nbrSides + ringStep,
    	2 * nbrSides - 1 + ringStep);
    geometry.faces.push(face);
    face = new THREE.Face3(
    	2 * nbrSides - 1 + ringStep,
    	2 * nbrSides - 1,
    	nbrSides);
    geometry.faces.push(face);

	//                //Outer mesh
	face = new THREE.Face3(
		nbrSides - 1,
		nbrSides - 1 + ringStep,
		ringStep);
	geometry.faces.push(face);
	face = new THREE.Face3(
		ringStep,
		0,
		nbrSides - 1);
	geometry.faces.push(face);

    //Compute normals
    geometry.computeFaceNormals();
    geometry.computeVertexNormals();
    // geometry.computeTangents();
	// geometry.computeCentroids();
	geometry.dynamic = true;

	// var polySectionMaterial = material.clone();
	// polySectionMaterial.shading = THREE.FlatShading;
	// var mesh = new THREE.Mesh(geometry, polySectionMaterial);

	// mesh = new THREE.Mesh( geometry, setMaterial(material));

    //var material = new THREE.MeshPhongMaterial( { color: 0x555555, specular: 0xCCCCCC, shininess: 20,side:THREE.BackSide,shading: THREE.SmoothShading} );
	mesh = new THREE.Mesh( geometry,  new THREE.MeshLambertMaterial( { color: 0xAAAAAA, shading: THREE.FlatShading} )/*setMaterial('steel', 'monopole')*/);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    //console.log('section', mesh);
	return mesh;

	// var polySection = new THREE.Object3D();
	// polySection.add(mesh);

	// return polySection;
}


function drawHexBolt(dir, height, radius, thread, NutOffset, material, centerPivot){
	var shape = new THREE.Shape();
	shape.moveTo(-(radius*Math.sqrt(3))/2,-radius/2);
	shape.lineTo(0,-radius);	
	shape.lineTo((radius*Math.sqrt(3))/2,-radius/2);
	shape.lineTo((radius*Math.sqrt(3))/2,radius/2);
	shape.lineTo(0,radius);
	shape.lineTo(-(radius*Math.sqrt(3))/2,radius/2);
	shape.lineTo(-(radius*Math.sqrt(3))/2,-radius/2);

	var geometry = new THREE.ExtrudeGeometry( shape, {bevelEnabled: false, amount:radius/2.25} );

	if(centerPivot) geometry.center(geometry);

	var cylinder_geo = new THREE.CylinderGeometry(radius/2, radius/2, height - thread, 16, 16, false);
	cylinder_geo.applyMatrix( new THREE.Matrix4().makeRotationX( - Math.PI / 2 ) );
	cylinder_geo.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0, height/2 - thread/2) )

	var thread_geo = new THREE.CylinderGeometry(radius/2.2, radius/2.2, thread, 16, 16, false);
	thread_geo.applyMatrix( new THREE.Matrix4().makeRotationX( - Math.PI / 2 ) );
	thread_geo.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0, height - thread/2) )

	THREE.GeometryUtils.merge(geometry, cylinder_geo);
	THREE.GeometryUtils.merge(geometry, thread_geo);

	var nut = new drawNut(radius/2.2, radius/2.25 ,setMaterial(material), true);
	nut.position.z = height - NutOffset;

	THREE.GeometryUtils.merge(geometry, nut);

	mesh = new THREE.Mesh( geometry, setMaterial(material) );
	mesh.dimensions = {};
	mesh.dimensions.radius = radius;
	mesh.name = 'Hex Bolt ' + radius;
	//ray_objects.push(mesh);
	mesh.matValue = material;
	return(mesh)
}

function drawNut(dir, innerRadius, height , orginAt, material){
	var radius = innerRadius*2.0;
	var shape = new THREE.Shape();
	shape.moveTo(-(radius*Math.sqrt(3))/2,-radius/2);
	shape.lineTo(0,-radius);	
	shape.lineTo((radius*Math.sqrt(3))/2,-radius/2);
	shape.lineTo((radius*Math.sqrt(3))/2,radius/2);
	shape.lineTo(0,radius);
	shape.lineTo(-(radius*Math.sqrt(3))/2,radius/2);
	shape.lineTo(-(radius*Math.sqrt(3))/2,-radius/2);

	var holePath = new THREE.Path();
	holePath.absarc(0, 0, innerRadius, 0, Math.PI*2, false );
	shape.holes.push( holePath );

	var geometry = new THREE.ExtrudeGeometry( shape, {bevelEnabled: false, amount:height} );

	switch(orginAt){
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

	mesh = new THREE.Mesh( geometry, setMaterial(material) );
	
	mesh.dimensions = {};
	mesh.dimensions.radius = radius;
	mesh.name = 'Nut ' + innerRadius;
	//ray_objects.push(mesh);
    //console.log('drawNut', mesh);
	return(mesh)
}


function drawDonut(dir, radius, tubeRadius, material, centerPivot){
	var arc = 350*Math.PI/180;
	var geometry = new THREE.TorusGeometry( radius, tubeRadius, 16, 32, arc );
	if(centerPivot)
		geometry.center(geometry);
	mesh = new THREE.Mesh(geometry, setMaterial(material));
	mesh.dimensions = {};
	mesh.dimensions.radius = radius;
	mesh.dimensions.tubeRadius = tubeRadius;
	mesh.name = "Donut " + radius;
	ray_objects.push(mesh);
	mesh.matValue = material;
	return(mesh)
}


function drawUBolt(dir, height, width, thickness, thread, leftNut, rightNut, NutOffset, material, centerPivot){
//Create the extrusion path
var points = [];
	//Push the first point
	points.push(new THREE.Vector3(width/2 ,(height-width/2)-thread,0));
	points.push(new THREE.Vector3(width/2 ,((height-width/2)-thread)/2,0));
	//Draw half circle
	var resolution = 9;
	var size = 180/resolution;
	var radius = width/2;
	for(var i=0; i <=resolution; i++) {
		var segment = ( i * size ) * Math.PI / 180;
		points.push( 
			new THREE.Vector3( 
				Math.cos( segment ) * radius,
				-Math.sin( segment ) * radius,
				0
				));
	}
	//Push the last point
	points.push(new THREE.Vector3(-width/2,((height-width/2)-thread)/2,0));
	points.push(new THREE.Vector3(-width/2,(height-width/2)-thread,0));
	//Create the spline from the above points
	var spline = new THREE.SplineCurve3(points);
	//Extrude settings
	var extrudeSettings = {
		steps           : 100,
		bevelEnabled    : false,
		extrudePath     : spline
	};

	//Create the section shape
	var arcShape = new THREE.Shape();
	arcShape.absarc( 0, 0, thickness, 0, Math.PI*2, true );

	//Create the geometry
	var geometry = new THREE.ExtrudeGeometry( arcShape, extrudeSettings );
	if(centerPivot)
		geometry.center(geometry);

	//Create the thread parts
	var cylinderL = new THREE.Mesh(new THREE.CylinderGeometry(thickness*0.8, thickness*0.8, thread, 16, resolution, false),setMaterial(material));
	cylinderL.position.x = -width/2;
	cylinderL.position.y = height - width/2 -thread/2;

	THREE.GeometryUtils.merge(geometry, cylinderL);

	var cylinderR = new THREE.Mesh(new THREE.CylinderGeometry(thickness*0.8, thickness*0.8, thread, 16, resolution, false),setMaterial(material));
	cylinderR.position.x = width/2;
	cylinderR.position.y = height - width/2 -thread/2;

	THREE.GeometryUtils.merge(geometry, cylinderR);

	//Create Nuts
	if(leftNut){
		var NutLeft = new drawNut(thickness*0.81, thickness ,setMaterial(material));
		NutLeft.rotation.x = Math.PI/2;
		NutLeft.position.x = -width/2;
		NutLeft.position.y = height - width/2 - NutOffset;
		THREE.GeometryUtils.merge(geometry, NutLeft);
	}

	if(rightNut){
		var NutRight = new drawNut(thickness*0.81, thickness ,setMaterial(material));
		NutRight.rotation.x = Math.PI/2;
		NutRight.position.x = width/2;
		NutRight.position.y = height - width/2 - NutOffset
		THREE.GeometryUtils.merge(geometry, NutRight);
	}

	var mesh = new THREE.Mesh(geometry,setMaterial(material));
	mesh.dimensions = {};
	mesh.dimensions.height = height;
	mesh.dimensions.width = width;
	mesh.dimensions.thickness = thickness;
	mesh.dimensions.thread = thread;
	mesh.dimensions.leftNut = leftNut;
	mesh.dimensions.rightNut = rightNut;
	mesh.dimensions.NutOffset = NutOffset;
	mesh.name = 'U Bolt ' + height + " " + width;
	ray_objects.push(mesh);
	mesh.matValue = material;
	return(mesh)
}



