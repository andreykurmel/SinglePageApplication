
function update(){
	
	if(Intersected)
	{
		Intersected.matrixWorld.decompose( position, quaternion, scale )
		axis.position.copy(position);
		axis.quaternion.x = quaternion.x;
		axis.quaternion.y = quaternion.y;
		axis.quaternion.z = quaternion.z;
		axis.quaternion.w = quaternion.w;
		axis.updateMatrixWorld( true );
	}
	
	if(RMV12_363)
	{
		RMV12_363.rotation.y = effectController.EArmRot*Math.PI/180;
		RMV12_363.position.y = effectController.EArmVO*100 + 20;
	}
	
	
	Arms = ['AssemblyA','AssemblyB','AssemblyC'];
	for (iArm = 0; iArm < Arms.length; iArm++) {
		var Assembly = Arms[iArm];
		// alert(Assembly);
		if(RMV12_363[Assembly]) //check for assembly A
		{
			switch (Assembly){
			case 'AssemblyA':
				RMV12_363[Assembly].rotation.y =  effectController[Assembly].ArmRot*Math.PI/180 + Math.PI; 
				RMV12_363[Assembly].position.z = -effectController[Assembly].ArmDist;
				// RMV12_363[Assembly].position.x =  effectController[Assembly].ArmDist*?						
				break;
			case 'AssemblyB':
				RMV12_363[Assembly].rotation.y = effectController[Assembly].ArmRot*Math.PI/180 + Math.PI + Math.PI*2/3; 
				RMV12_363[Assembly].position.z = effectController[Assembly].ArmDist*0.49999999999999933;
				RMV12_363[Assembly].position.x = effectController[Assembly].ArmDist*(-0.866025403784439);		
				break;
			case 'AssemblyC':
				RMV12_363[Assembly].rotation.y = effectController[Assembly].ArmRot*Math.PI/180 + Math.PI - Math.PI*2/3; 
				RMV12_363[Assembly].position.z = effectController[Assembly].ArmDist*0.4999999999999999;
				RMV12_363[Assembly].position.x = effectController[Assembly].ArmDist*0.8660254037844387;	
				break;
			default:
			};
			
			RMV12_363[Assembly].dummy_plate.rotation.y = effectController[Assembly].PlateHR*Math.PI/180;
			RMV12_363[Assembly].dummy_vert_1.position.z = effectController[Assembly].Pipe1H*P3150.P3150.dimensions.D1*0.97;
			RMV12_363[Assembly].dummy_vert_1.rotation.y = effectController[Assembly].Pipe1R*Math.PI/180;
			RMV12_363[Assembly].dummy_vert_1.VP.position.z = -effectController[Assembly].Pipe1V*A.A.dimensions.D1;			

			RMV12_363[Assembly].dummy_vert_2.position.z = effectController[Assembly].Pipe2H*P3150.P3150.dimensions.D1*0.97;
			RMV12_363[Assembly].dummy_vert_2.rotation.y = effectController[Assembly].Pipe2R*Math.PI/180;
			RMV12_363[Assembly].dummy_vert_2.VP.position.z = -effectController[Assembly].Pipe2V*A.A.dimensions.D1;	

			RMV12_363[Assembly].dummy_vert_3.position.z = effectController[Assembly].Pipe3H*P3150.P3150.dimensions.D1*0.97;
			RMV12_363[Assembly].dummy_vert_3.rotation.y = effectController[Assembly].Pipe3R*Math.PI/180;
			RMV12_363[Assembly].dummy_vert_3.VP.position.z = -effectController[Assembly].Pipe3V*A.A.dimensions.D1;			
		}
	};	
}
