function setOpacity(Object, OpacityValue){
	OpacityValue = Number(OpacityValue);
	if(OpacityValue < 0) OpacityValue = 0;
	if(OpacityValue > 1) OpacityValue = 1;
	Object.material.opacity = OpacityValue;
	if(Object.children[0] !== undefined)
	    Object.children[0].material.opacity = OpacityValue;
}
