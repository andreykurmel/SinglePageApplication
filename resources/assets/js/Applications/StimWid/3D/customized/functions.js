function changeOpacity(objectName, opacity_value){
	if(opacity_value < 0) opacity_value = 0;
	if(opacity_value > 1) opacity_value = 1;
	// console.log(opacity_value);
	[objectName].material.opacity = Number(opacity_value);
	if([objectName].children[0] !== undefined)
		[objectName].children[0].material.opacity = Number(opacity_value);
}

function duplicate(object){
		clonedObject = object.clone();
		pushChildren(clonedObject)
    return(clonedObject);
}

//function to push all colned elements in ray_objects and to separate the materials
function pushChildren(object){
	var i=0;
	do{
		ray_objects.push(object.children[i]);
		if(object.children[i].material)
		{
			var hex = object.children[i].material.color.getHex();
			switch(hex){
			case 11184810 :  
				var material = new THREE.MeshPhongMaterial( { color: 0xaaaaaa, specular: 0xababab, shininess: 10, metal:true } );
				break;
			case 3355443 :
				var material = new THREE.MeshPhongMaterial( { color: 0x333333, specular: 0xCCCCCC, shininess: 20 } );
				break;
			case 15658734 :
				var material = new THREE.MeshPhongMaterial( { color: 0xeeeeee, specular: 0xfefefe, shininess: 5 } );
				break;
			}
			object.children[i].material = material;
		}
		if(object.children[i].children.length>0)
			pushChildren(object.children[i])
		i++
	} while (i < object.children.length)
}

function pushNames(object,Arm){
var i=0;
	do{
			object.children[i].name = object.children[i].name + ", " + Arm
		if(object.children[i].children.length>0)
			pushNames(object.children[i],Arm)
		i++
	} while (i < object.children.length)
}