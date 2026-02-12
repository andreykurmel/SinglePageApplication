var RMV12_363 =  new THREE.Object3D();

function drawAssembly(AssemblyNo){
	
	switch (AssemblyNo){
	case 'RMV12-363':

		var PartNo = 'SV197-36'; //ExArm & ExPipe
		SV197_36 = new drawPart(PartNo); 

		dummy_plate = new THREE.Object3D();
		dummy_plate.name = "dummy_plate";
		dummy_plate.position.z = SV197_36.ExArm.dimensions.D1;
		// SV197_36.add(dummy_plate);
		SV197_36.dummy_plate = dummy_plate;

		var PartNo = 'SP216'; //C Plate
		SP216 = new drawPart(PartNo); 
		SP216.rotation.y = Math.PI/2;
		SP216.position.z = SP216.SP216.dimensions.bf/2 + SV197_36.ExArm.ExPipe.dimensions.OD; 
		// dummy_plate.add(SP216); // <------
		dummy_plate.SP216 = SP216; 

		var PartNo = 'UB1306'; //UBolt1
		UB1306 = new drawPart(PartNo);  
		UB1306.rotation.z = Math.PI/2;
		UB1306.rotation.x = Math.PI/2;
		UB1306.position.x = SP216.SP216.dimensions.bf/2 + SV197_36.ExArm.ExPipe.dimensions.OD;
		UB1306.position.y = SV197_36.ExArm.ExPipe.dimensions.D1/2 * 0.6;

		UB1306_2 = duplicate(UB1306); //Ubolt2
		UB1306_2.position.y = -SV197_36.ExArm.ExPipe.dimensions.D1/2 * 0.6;

		// SP216.add(UB1306_2);
		SP216.UB1306_2 = UB1306_2;
		
		// SP216.add(UB1306);
		SP216.UB1306 = UB1306;

		var PartNo = 'P3150'; //Horizontal Pipe
		P3150 = new drawPart(PartNo);   
		P3150.position.x = -(SP216.SP216.dimensions.bf/2 + P3150.P3150.dimensions.OD);
		// SP216.add(P3150);
		SP216.P3150 = P3150;

		var PartNo = 'UB5458';
		UB5458 = new drawPart(PartNo); //UBolt between CPlate and Horizontal Pipe
		
		UB5458.rotation.z = -Math.PI/2;
		UB5458.position.z = SP216.SP216.dimensions.d/2 *0.9;

		UB5458_2 = duplicate(UB5458); //UBolt between CPlate and Horizontal Pipe
		UB5458_2.position.z = SP216.SP216.dimensions.d/2 *0.4;

		UB5458_3 = duplicate(UB5458); //UBolt between CPlate and Horizontal Pipe
		UB5458_3.position.z = -SP216.SP216.dimensions.d/2 *0.4;

		UB5458_4 = duplicate(UB5458); //UBolt between CPlate and Horizontal Pipe
		UB5458_4.position.z = -SP216.SP216.dimensions.d/2 *0.9;

		// P3150.add(UB5458);
		P3150.UB5458 = UB5458;
		
		// P3150.add(UB5458_2);
		P3150.UB5458_2 = UB5458_2;
		
		// P3150.add(UB5458_3);
		P3150.UB5458_3 =UB5458_3;
		
		// P3150.add(UB5458_4);
		P3150.UB5458_4 = UB5458_4;

		dummy_vert_1 = new THREE.Object3D(); //dummy to hold the vertical structures
		dummy_vert_1.name = "dummy_vert_1";
		dummy_vert_1.rotation.x = Math.PI/2
		// P3150.add(dummy_vert_1);
		P3150.dummy_vert_1 = dummy_vert_1;

		var PartNo = 'SP219'; 
		SP219 = new drawPart(PartNo); //C Plate for vertical structures
		SP219.position.x = - (SP219.SP219.dimensions.bf/2 + P3150.P3150.dimensions.OD)
		// dummy_vert_1.add(SP219);
		dummy_vert_1.SP219 = SP219;

		UB5458_5 = duplicate(UB5458); //UBolt between CPlate and Horizontal Pipe
		UB5458_5.position.y = -SP219.SP219.dimensions.d/2*0.6;
		UB5458_5.rotation.z = Math.PI/2;
		UB5458_5.rotation.x = Math.PI/2;
		UB5458_5.position.x = SP219.SP219.dimensions.bf/2 + P3150.P3150.dimensions.OD;
		UB5458_5.position.z = 0;

		UB5458_6 = duplicate(UB5458); //UBolt between CPlate and Horizontal Pipe
		UB5458_6.position.y = SP219.SP219.dimensions.d/2*0.6;
		UB5458_6.rotation.z = Math.PI/2;
		UB5458_6.rotation.x = Math.PI/2;
		UB5458_6.position.x = SP219.SP219.dimensions.bf/2 + P3150.P3150.dimensions.OD;
		UB5458_6.position.z = 0;
		
		// SP219.add(UB5458_6);
		SP219.UB5458_6 = UB5458_6;
		
		// SP219.add(UB5458_5);
		SP219.UB5458_5 = UB5458_5;

		var PartNo = 'A'; A = new drawPart(PartNo);  
		A.position.x = - (SP219.SP219.dimensions.bf/2 + A.A.dimensions.OD) 
		// SP219.add(A);
		SP219.A = A;

		var PartNo = 'UB1212'; 
		UB1212 = new drawPart(PartNo);//UBolt between CPlate and Horizontal Pipe
		UB1212.rotation.z = -Math.PI/2;
		UB1212.position.x = -(SP219.SP219.dimensions.bf/2 + A.A.dimensions.OD) //-(P3150.P3150.dimensions.OD + SP219.SP219.dimensions.bf + A.A.dimensions.OD);
		UB1212.position.z = SP219.SP219.dimensions.d/2*0.8;

		UB1212_2 = duplicate(UB1212);//UBolt between CPlate and Horizontal Pipe
		UB1212_2.position.z = -SP219.SP219.dimensions.d/2*0.8;
		SP219.add(UB1212);
		SP219.add(UB1212_2);

		dummy_vert_2 = new THREE.Object3D(); //create a dummy first for the new vertical pipe system
		dummy_vert_2.name = "dummy_vert_2";
		dummy_vert_2.rotation.x = Math.PI/2
		// P3150.add(dummy_vert_2); //attach the dummy to the pipe
		P3150.dummy_vert_2 = dummy_vert_2;

		Vpipe2 = duplicate(SP219); //duplicate the vertical pipe system
		dummy_vert_2.add(Vpipe2); //attach the vertical pipe system to the dummy
		dummy_vert_2.Vpipe2 = Vpipe2;

		dummy_vert_3 = new THREE.Object3D(); //create a dummy first for the new vertical pipe system
		dummy_vert_3.name = "dummy_vert_3";
		dummy_vert_3.rotation.x = Math.PI/2
		// P3150.add(dummy_vert_3); //attach the dummy to the pipe
		P3150.dummy_vert_3 = dummy_vert_3;

		Vpipe3 = duplicate(SP219); //duplicate the vertical pipe system
		// dummy_vert_3.add(Vpipe3) //attach the vertical pipe system to the dummy
		dummy_vert_3.Vpipe3 = Vpipe3;

		RMV12_363[Assembly].SV197_36 = SV197_36;
		// RMV12_363.AssemblyA.dummy_plate = RMV12_363.AssemblyA.children[0].children[1]; // Why children[1] not children[0];
		RMV12_363[Assembly].dummy_plate = RMV12_363[Assembly].SV197_36.dummy_plate;
		
		RMV12_363[Assembly].dummy_vert_1 = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_1;
		RMV12_363[Assembly].dummy_vert_2 = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_2;
		RMV12_363[Assembly].dummy_vert_3 = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_3;		
		
		RMV12_363[Assembly].dummy_vert_1.VP = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_1;
		RMV12_363[Assembly].dummy_vert_2.VP = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_2;
		RMV12_363[Assembly].dummy_vert_3.VP = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_3;
		
		RMV12_363[Assembly].dummy_vert_1.A = RMV12_363[Assembly].SV197_36.dummy_plate.SP216.P3150.dummy_vert_1.SP219.A;

		//Push A,B,C Letter in the name of objects
		pushNames(RMV12_363[Assembly],"A");
		pushNames(RMV12_363[Assembly],"B");
		pushNames(RMV12_363[Assembly],"C");

		return(RMV12_363);
		break;
	case 'RMV12-372':
		break;
	case 'RMV12-384':
		break;
	case 'RMV12-396':
		break;	
	default:
		break;
	};
};


