function setTexture(material, parameter){

	var textureImage;
	var texture;

	switch (material){
	case 'steel':
		switch (parameter){
		case 'threaded_bolt':
			textureImage = 'images/textures/metal/thread_64.jpg';
		break;
		
		case 'galvanised':
			textureImage = 'images/textures/metal/galvanised_steel_tin_512.jpg';
		break;
		
		default:
		break;
		
		}
		break;
	case 'concrete':
	case 'Concrete':
		switch (parameter){
		case 'cracked':
			textureImage = 'images/textures/concrete/concrete_cracked_512.png';
			break;
		case 'uncracked':
			textureImage = 'images/textures/concrete/concrete1.jpg';
			break
		default:
			textureImage = 'images/textures/concrete/concrete1.jpg';
			break;
		}
		
		break;

	case 'masonry':
	case 'Masonry':
		textureImage = 'images/textures/masonry/masonry_tile_512.jpg';
		break;

	default:
		textureImage = '';
		break;
	};

	if(textureImage!=''){texture = THREE.ImageUtils.loadTexture(textureImage); return texture;}else{return '';}
}