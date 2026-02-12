var skyboxname = 'skybox02';

switch(skyboxname){

case 'skybox02':
	var imagePrefix = "images/textures/" + skyboxname + "/sky_";
	var directions = ["posX", "negX", "posY", "negY", "posZ", "negZ"];
	var imageSuffix = ".jpg";
	break;
case 'dunes':
	var imagePrefix = "textures/dunes/dunes_";

	// var directions = ["back", "front", "top", "bottom", "right", "left"];	
	var directions = ["back", "front", "top", "bottom", "right", "left"];
	var imageSuffix = ".jpg";
	break;
default:
	break;
}
var skyGeometry = new THREE.BoxGeometry( 2000, 2000, 2000 );

var materialArray = [];
for (var i = 0; i < 6; i++)
	materialArray.push( new THREE.MeshBasicMaterial({
	map: THREE.ImageUtils.loadTexture( imagePrefix + directions[i] + imageSuffix ),
	side: THREE.BackSide
	}));

var skyMaterial = new THREE.MeshFaceMaterial( materialArray );
var skyBox = new THREE.Mesh( skyGeometry, skyMaterial );
