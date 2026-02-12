//This function will read all the object's children and will transform them intro properties
var all_childrens = [];
function pushProperties(object){
	var i=0;
	do{
		if(object.children[i].name!=="")
			all_childrens.push(object.children[i]);
		if(object.children[i].children.length>0)
			pushProperties(object.children[i])
		i++
	} while (i < object.children.length)
}

var SV197_36;
function drawPart(PartNo){
	var dummy = new THREE.Object3D(); //create a dummy object that will hold all the objects

	switch(PartNo){
		case 'LWRM':  
		//Details to be found out, not to be modelled for now		
		break;

		case 'G58R-24':  
		G58R_24 = new drawElement(99, "SR", "1.125", "pipe", "chrome", "PartNo: G58R-24", false);
		dummy.add(G58R_24);
		dummy.G58R_24 = G58R_24;
		return(dummy) //returns the dummy object which will be passed to the assemly object this way avoiding the script to create several objects for one
		break;

		case 'G58R-48': 
		G58R_48 = new drawElement(39, "SR", "1.75", "pipe", "chrome", "PartNo: G58R-48", false);
		dummy.add(G58R_48);
		dummy.G58R_48 = G58R_48;
		return(dummy) //returns the dummy object which will be passed to the assemly object this way avoiding the script to create several objects for one
		break;

		case 'SV197-36':  
		ExArm = new drawElement( 36, "HHS(Rect)", "HSS5X5X3/8", "36 HHS Rect", "chrome", "PartNo: SV197-36 ExArm", false);
		ExPipe = new drawElement(12, "Pipe",'P2x.154',"18 in P4x.237", "chrome", "PartNo: SV197-36 ExPipe", true);
		ExPipe.rotation.x = Math.PI/2;
		ExPipe.position.z = ExArm.dimensions.D1;

		ExArm.add(ExPipe);
		dummy.add(ExArm); //make the dummy parent of all objects
		dummy.ExArm = ExArm; //We will push the object's properties to the dummy so it will be reocgnized by the assembly object
		dummy.ExArm.ExPipe = ExPipe;
		//One square and four triangular plates, nuts, washers, and bolts not to be modeled for now				
		return(dummy) //returns the dummy object which will be passed to the assemly object this way avoiding the script to create several objects for one
		break;

		case 'A58234':  
		A58234 = new drawElement(12, "Hex Bolt",'none',"Hex Bolt", "chrome", "PartNo: A58234", true);
		dummy.add(A58234);
		dummy.A58234 = A58234
		return(dummy)
		break;
		case 'A58FW':  
		A58FW = new drawElement(3, "Donut",0.3,"Donut", "chrome", "PartNo: A58FW", true);
		dummy.add(A58FW);
		dummy.A58FW = A58FW;
		return(dummy)
		break;

		case 'G58LW':  
		G58LW = new drawElement(3.5, "Donut",0.3,"Donut", "chrome", "PartNo: G58LW", true);
		dummy.add(G58LW);
		dummy.G58LW = G58LW;
		return(dummy)
		break;

		case 'A58NUT':  
		A58NUT = new drawElement(3, "Nut",2,"Nut", "chrome", "PartNo: A58NUT", true);
		dummy.add(A58NUT);
		dummy.A58NUT = A58NUT;
		return(dummy)
		break;

		case 'UB5458': 
		UB5458 = new drawElement(6, "U Bolt",3.52,"U Bolt", "metal", "PartNo: UB5458", false);
		dummy.add(UB5458);
		dummy.UB5458 = UB5458;
		return(dummy)
		break;

		case 'G58FW':  
		G58FW = new drawElement(6, "Donut",0.3,"Donut", "chrome", "PartNo: G58FW", true);
		dummy.add(G58FW);
		dummy.G58FW = G58FW;
		return(dummy)
		break;

		case 'G58NUT':  
		G58NUT = new drawElement(3, "Nut",2,"Nut", "chrome", "PartNo: G58NUT", true);
		dummy.add(G58NUT);
		dummy.G58NUT = G58NUT;
		return(dummy)
		break;

		case 'UB1306':  
		UB1306 = new drawElement(8.2, "U Bolt",5.11,"U Bolt", "metal", "PartNo: UB1306", false);
		dummy.add(UB1306);
		dummy.UB1306 = UB1306;
		return(dummy)
		break;

		case 'G12NUT':  
		G12NUT = new drawElement(2, "Nut",3,"Nut", "chrome", "PartNo: G12NUT", true);
		dummy.add(G12NUT);
		dummy.G12NUT = G12NUT;
		return(dummy)
		break;

		case 'G12FW':  
		G12FW = new drawElement(11, "Donut",0.7,"Donut", "chrome", "PartNo: G12FW", true);
		dummy.add(G12FW);
		dummy.G12FW = G12FW;
		return(dummy)
		break;

		case 'G12LW': 
		G12LW = new drawElement(11, "Donut",0.7,"Donut", "chrome", "PartNo: G12LW", true);
		dummy.add(G12LW);
		dummy.G12LW = G12LW;
		return(dummy)
		break;

		case 'UB1212': 
		UB1212 = new drawElement(3.3, "U Bolt",2.3,"U Bolt", "metal", "PartNo: UB1212", false);
		dummy.add(UB1212);
		dummy.UB1212 = UB1212;
		return(dummy)
		break;

		case 'P3150':  
		P3150 = new drawElement(150, "Pipe",'P1.25x.14',"Pipe", "chrome", "PartNo: P3150", true);
		dummy.add(P3150);
		dummy.P3150 = P3150;
		return(dummy)
		break;

		case 'A':  
		A = new drawElement(63, "Pipe",'P.75x.154',"Pipe", "chrome", "PartNo: A", true);
		dummy.add(A);
		dummy.A = A;
		return(dummy)
		break;

		case 'SP219':  
		SP219 = new drawElement(8, "C",'C6X10.5',"C Profile", "chrome", "PartNo: SP219", true);
		dummy.add(SP219);
		dummy.SP219 = SP219;
		return(dummy)
		break;

		case 'SP216': 
		SP216 = new drawElement(12, "C", "C10X15.3", "LARGE SUPPORT CROSS PLATE", "chrome", "PartNo: SP216", true);
		dummy.add(SP216);
		dummy.SP216 = SP216;
		return(dummy)
		break;

		default:
		alert("Something went wrong! Check drawPart")
		break;
	};
	
};

/*
dummy = new THREE.Object3D(); //create a dummy object that will hold all the objects
	SP216 =  new drawElement(15, "C",'C10X15.3',"LARGE SUPPORT CROSS PLATE", "chrome", "SP216", true);
	SP216.rotation.y = Math.PI/2
	//Move the plate from the center of the pipe
	SP216.position.z = SP216.dimensions.bf/2;
	
	P3150 =  new drawElement(150, "Pipe",'P3x.216', "3-1/2 in X 150 in SCH 40 GALVANIZED PIPE", "chrome", "P3150", true);
	P3150.position.x = -(P3150.dimensions.OD + SP216.dimensions.bf/2);
	SP216.add(P3150)

	P263_Helper = new THREE.Object3D(); 
	P263_Helper.position.z = P3150.dimensions.D1*0.5*0.75
	P3150.add(P263_Helper);

	SP219 = new drawElement(6, "C",'C6X10.5', "U Bolt 1", "chrome", "SP219", true);
	SP219.rotation.x = Math.PI/2
	SP219.position.x = -(P3150.dimensions.OD + SP219.dimensions.bf/2);
	P263_Helper.add(SP219);

	P263 = new drawElement(63, "Pipe",'P2.375x.154', "2-3/8 in O.D. SCH. 40 in PIPE", "chrome", "P263", true);
	//P263.rotation.y = Math.PI/2;
	P263.position.x = -(SP219.dimensions.bf/2 + P263.dimensions.OD);
	SP219.add(P263)

	dummy.SP216 = SP216;
	dummy.SP216.P3150 = P3150;
	dummy.SP216.P3150.SP219 = SP219;
	dummy.SP216.P3150.SP219.P263 = P263;
	dummy.add(SP216)
	return(dummy)*/