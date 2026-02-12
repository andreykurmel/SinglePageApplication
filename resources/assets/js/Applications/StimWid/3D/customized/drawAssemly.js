var RMV12_363 =  new THREE.Object3D();

function drawAssembly(AssemblyNo){
 	
	switch (AssemblyNo){
	case 'RMV12-363':
		//Assembly A
			AssemblyA = new THREE.Object3D();
			var PartNo = 'SV197-36'; //ExArm & ExPipe
		SV197_36 = new drawPart(PartNo); 

		dummy_plate = new THREE.Object3D();
		dummy_plate.name = "dummy_plate";
			dummy_plate.position.z = SV197_36.ExArm.dimensions.D1;
			SV197_36.add(dummy_plate);

			var PartNo = 'SP216'; //C Plate
		SP216 = new drawPart(PartNo); 
			SP216.rotation.y = Math.PI/2;
			SP216.position.z = SP216.SP216.dimensions.bf/2 + SV197_36.ExArm.ExPipe.dimensions.OD; 
			dummy_plate.add(SP216); // <------

			var PartNo = 'UB1306'; //UBolt1
		UB1306 = new drawPart(PartNo);  
			UB1306.rotation.z = Math.PI/2;
			UB1306.rotation.x = Math.PI/2;
			UB1306.position.x = SP216.SP216.dimensions.bf/2 + SV197_36.ExArm.ExPipe.dimensions.OD;
			UB1306.position.y = SV197_36.ExArm.ExPipe.dimensions.D1/2 * 0.6;

		UB1306_2 = duplicate(UB1306); //Ubolt2
			UB1306_2.position.y = -SV197_36.ExArm.ExPipe.dimensions.D1/2 * 0.6;

			SP216.add(UB1306_2);
			SP216.add(UB1306);

			var PartNo = 'P3150'; //Horizontal Pipe
		P3150 = new drawPart(PartNo);   
			P3150.position.x = -(SP216.SP216.dimensions.bf/2 + P3150.P3150.dimensions.OD);
			SP216.add(P3150);

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

			P3150.add(UB5458);
			P3150.add(UB5458_2);
			P3150.add(UB5458_3);
			P3150.add(UB5458_4);

		dummy_vert_1 = new THREE.Object3D(); //dummy to hold the vertical structures
			dummy_vert_1.name = "dummy_vert_1";
			dummy_vert_1.rotation.x = Math.PI/2
			P3150.add(dummy_vert_1);

			var PartNo = 'SP219'; 
		SP219 = new drawPart(PartNo); //C Plate for vertical structures
			SP219.position.x = - (SP219.SP219.dimensions.bf/2 + P3150.P3150.dimensions.OD)
			dummy_vert_1.add(SP219);

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
			
			SP219.add(UB5458_6);
			SP219.add(UB5458_5);

		var PartNo = 'A'; A = new drawPart(PartNo);  
			A.position.x = - (SP219.SP219.dimensions.bf/2 + A.A.dimensions.OD) 
			SP219.add(A);

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
			P3150.add(dummy_vert_2); //attach the dummy to the pipe

		Vpipe2 = duplicate(SP219); //duplicate the vertical pipe system
			dummy_vert_2.add(Vpipe2) //attach the vertical pipe system to the dummy

		dummy_vert_3 = new THREE.Object3D(); //create a dummy first for the new vertical pipe system
			dummy_vert_3.name = "dummy_vert_3";
			dummy_vert_3.rotation.x = Math.PI/2
			P3150.add(dummy_vert_3); //attach the dummy to the pipe

		Vpipe3 = duplicate(SP219); //duplicate the vertical pipe system
			dummy_vert_3.add(Vpipe3) //attach the vertical pipe system to the dummy
			
		AssemblyA.add(SV197_36);

		//Assembly B
		AssemblyB = duplicate(SV197_36);

		//Assembly C
		AssemblyC = duplicate(SV197_36);
	
			RMV12_363.add(AssemblyA);
			RMV12_363.add(AssemblyB);
			RMV12_363.add(AssemblyC);


		//Controller properties - this part is hardcoded for now

		RMV12_363.AssemblyA = AssemblyA;
		RMV12_363.AssemblyA.dummy_plate = RMV12_363.AssemblyA.children[0].children[1]; // Why children[1] not children[0];
		RMV12_363.AssemblyA.dummy_vert_1 = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[5];
		RMV12_363.AssemblyA.dummy_vert_2 = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[6];
		RMV12_363.AssemblyA.dummy_vert_3 = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[7];
		RMV12_363.AssemblyA.dummy_vert_1.VP = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[5].children[0].children[3].children[0];
		RMV12_363.AssemblyA.dummy_vert_2.VP = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[6].children[0].children[3].children[0];
		RMV12_363.AssemblyA.dummy_vert_3.VP = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[7].children[0].children[3].children[0];
		RMV12_363.AssemblyA.dummy_vert_1.A  = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[5].children[0].children[3].children[0]; //A pipe of Assembly A
		RMV12_363.AssemblyA.dummy_vert_2.A  = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[6].children[0].children[3].children[0]; //A pipe of Assembly A
		RMV12_363.AssemblyA.dummy_vert_3.A  = RMV12_363.AssemblyA.children[0].children[1].children[0].children[3].children[7].children[0].children[3].children[0]; //A pipe of Assembly A

		
		RMV12_363.AssemblyB = AssemblyB;
		RMV12_363.AssemblyB.dummy_plate = RMV12_363.AssemblyB.children[1]
		RMV12_363.AssemblyB.dummy_vert_1 = RMV12_363.AssemblyB.children[1].children[0].children[3].children[5];
		RMV12_363.AssemblyB.dummy_vert_2 = RMV12_363.AssemblyB.children[1].children[0].children[3].children[6];
		RMV12_363.AssemblyB.dummy_vert_3 = RMV12_363.AssemblyB.children[1].children[0].children[3].children[7];
		RMV12_363.AssemblyB.dummy_vert_1.VP = RMV12_363.AssemblyB.children[1].children[0].children[3].children[5].children[0].children[3].children[0];
		RMV12_363.AssemblyB.dummy_vert_2.VP = RMV12_363.AssemblyB.children[1].children[0].children[3].children[6].children[0].children[3].children[0];
		RMV12_363.AssemblyB.dummy_vert_3.VP = RMV12_363.AssemblyB.children[1].children[0].children[3].children[7].children[0].children[3].children[0];
		RMV12_363.AssemblyB.dummy_vert_1.A  = RMV12_363.AssemblyB.children[1].children[0].children[3].children[5].children[0].children[3].children[0];
		RMV12_363.AssemblyB.dummy_vert_2.A  = RMV12_363.AssemblyB.children[1].children[0].children[3].children[6].children[0].children[3].children[0];
		RMV12_363.AssemblyB.dummy_vert_3.A  = RMV12_363.AssemblyB.children[1].children[0].children[3].children[7].children[0].children[3].children[0];

		
		RMV12_363.AssemblyC = AssemblyC;
		RMV12_363.AssemblyC.dummy_plate = RMV12_363.AssemblyC.children[1]
		RMV12_363.AssemblyC.dummy_vert_1 = RMV12_363.AssemblyC.children[1].children[0].children[3].children[5];
		RMV12_363.AssemblyC.dummy_vert_2 = RMV12_363.AssemblyC.children[1].children[0].children[3].children[6];
		RMV12_363.AssemblyC.dummy_vert_3 = RMV12_363.AssemblyC.children[1].children[0].children[3].children[7];
		RMV12_363.AssemblyC.dummy_vert_1.VP = RMV12_363.AssemblyC.children[1].children[0].children[3].children[5].children[0].children[3].children[0];
		RMV12_363.AssemblyC.dummy_vert_2.VP = RMV12_363.AssemblyC.children[1].children[0].children[3].children[6].children[0].children[3].children[0];
		RMV12_363.AssemblyC.dummy_vert_3.VP = RMV12_363.AssemblyC.children[1].children[0].children[3].children[7].children[0].children[3].children[0];
		RMV12_363.AssemblyC.dummy_vert_1.A  = RMV12_363.AssemblyC.children[1].children[0].children[3].children[5].children[0].children[3].children[0];
		RMV12_363.AssemblyC.dummy_vert_2.A  = RMV12_363.AssemblyC.children[1].children[0].children[3].children[6].children[0].children[3].children[0];
		RMV12_363.AssemblyC.dummy_vert_3.A  = RMV12_363.AssemblyC.children[1].children[0].children[3].children[7].children[0].children[3].children[0];

		//Push A,B,C Letter in the name of objects
		pushNamesA(RMV12_363.AssemblyA);
		pushNamesB(RMV12_363.AssemblyB);
		pushNamesC(RMV12_363.AssemblyC);

		//var PartNo = 'G58R-24'; G58R_24 = new drawPart(PartNo);  Assembly.add(G58R_24);
		//var PartNo = 'G58R-48'; G58R_48 = new drawPart(PartNo);  Assembly.add(G58R_48);
		/*
		var PartNo = 'A58234'; A58234 = new drawPart(PartNo);   Assembly.add(A58234);
		var PartNo = 'A58FW'; A58FW = new drawPart(PartNo);   Assembly.add(A58FW);
		var PartNo = 'G58LW'; G58LW = new drawPart(PartNo);   Assembly.add(G58LW);
  		var PartNo = 'A58NUT'; A58NUT = new drawPart(PartNo);   Assembly.add(A58NUT);
  		
  		var PartNo = 'G58FW'; G58FW = new drawPart(PartNo);   Assembly.add(G58FW);
  		var PartNo = 'G58NUT'; G58NUT = new drawPart(PartNo);   Assembly.add(G58NUT);
  		
  		var PartNo = 'G12NUT'; G12NUT = new drawPart(PartNo);   Assembly.add(G12NUT);
		var PartNo = 'G12FW'; G12FW = new drawPart(PartNo);   Assembly.add(G12FW);
		var PartNo = 'G12LW'; G12LW = new drawPart(PartNo);   Assembly.add(G12LW);
		
		var PartNo = 'P3150'; P3150 = new drawPart(PartNo);   Assembly.add(P3150);
		var PartNo = 'A'; A = new drawPart(PartNo);   Assembly.add(A);
		*/

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

function pushNamesA(object){
var i=0;
	do{
			object.children[i].name = object.children[i].name + ", A"
		if(object.children[i].children.length>0)
			pushNamesA(object.children[i])
		i++
	} while (i < object.children.length)
}
function pushNamesB(object){
var i=0;
	do{
			object.children[i].name = object.children[i].name + ", B"
		if(object.children[i].children.length>0)
			pushNamesB(object.children[i])
		i++
	} while (i < object.children.length)
}
function pushNamesC(object){
var i=0;
	do{
			object.children[i].name = object.children[i].name + ", C"
		if(object.children[i].children.length>0)
			pushNamesC(object.children[i])
		i++
	} while (i < object.children.length)
}

