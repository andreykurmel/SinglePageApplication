function setMaterial(type, use) {

    switch (type) {
        case 'metal':
        case 'steel':

            switch (use) {
                case 'flat':
                    var material = new THREE.MeshLambertMaterial({color: 0xAAAAAA, shading: THREE.SmoothShading});
                    break;

                // case 'round':
                // var material = new THREE.MeshLambertMaterial( { color: 0xAAAAAA, specular: 0xCCCCCC, shininess: 20, shading: THREE.SmoothShading} );
                // break;

                default:
                var material = new THREE.MeshPhongMaterial({
                    color: 0x777777,
                    specular: 0xffffff,
                    shininess: 75,
                    emissive: 0x555555,
                    shading: THREE.SmoothShading
                });
                    break;
            }
            break;

        case 'aluminium':
            var material = new THREE.MeshPhongMaterial({color: 0xeeeeee, specular: 0xfefefe, shininess: 5});
            break;

        case 'chrome':
            //var gen = new THREE.ExtrudeGeometry.WorldUVGenerator();
            var material = new THREE.MeshLambertMaterial({
                transparent: true,
                uvGenerator: THREE.ExtrudeGeometry.WorldUVGenerator
            });
            break;

        case 'le':
            var material = new THREE.MeshPhongMaterial({
                color: __GRAY2,
                specular: __WHITE,
                // map: textureDiff,
                // wireframe: true,
                shading: THREE.FlatShading,
                overdraw: true,
                side: THREE.DoubleSide,
                reflectivity: 0.3,
                metal: true
            });

            break;

        case 'custom':
            var material = new THREE.MeshPhongMaterial({
                color: Number( String(use).replace(/#/gi, '0x') ),
                specular: Number( String(use).replace(/#/gi, '0x') ),
                //color: String(use).replace(/#/gi, '0x'),
                //specular: 0xffffff,
                //shininess: 25,
                //shading: THREE.SmoothShading
            });
            break;

        default:
            break;
    }

    return material;
}