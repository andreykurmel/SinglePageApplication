Terrain = function (textureFile, visible) {
	//Generate a terrain
	var imageSuffix = ".jpg";
	var gt = THREE.ImageUtils.loadTexture( textureFile + imageSuffix);
	var ggSize = 2000;
	var gg = new THREE.PlaneBufferGeometry( ggSize, ggSize );
	var gm = new THREE.MeshPhongMaterial( {
		map: gt, 
		shading: THREE.SmoothShading
	} );

	this.TerrainPlatter = new THREE.Mesh( gg, gm );
	this.TerrainPlatter.rotation.x = - Math.PI / 2;
	this.TerrainPlatter.position.y = -100;
	this.TerrainPlatter.material.map.repeat.set( 128, 128 );
	this.TerrainPlatter.material.map.wrapS = this.TerrainPlatter.material.map.wrapT = THREE.RepeatWrapping;
	this.TerrainPlatter.receiveShadow = true;
	this.TerrainPlatter.visible = visible;
	this.TerrainPlatter.type = "terrain";
	
	// scene.add(this.TerrainPlatter);
	
	this.isVisible = false;
	return this;
};



/* TerrainPlatter.prototype = {
	constructor: TerrainPlatter,
	
	toggleVisible: function() {
		this.isVisible = this.isVisible == true ? false : true;
		this.TerrainPlatter.visible = this.isVisible;
	},
	
	// var imageSuffix = ".jpg";
	setTexture: function(textureFile) {
		var gt = THREE.ImageUtils.loadTexture( textureFile);
		var gm = new THREE.MeshPhongMaterial({
			map: gt, 
			shading: THREE.SmoothShading
		});
		
		this.TerrainPlatter.material = gm;
		this.TerrainPlatter.material.map.repeat.set( 256, 256 );
		this.TerrainPlatter.material.map.wrapS = this.TerrainPlatter.material.map.wrapT = THREE.RepeatWrapping;
		
		scene.add(this.TerrainPlatter);
		
	}

	// getCurrentGround: function() {return this.TerrainPlatter;}
} */