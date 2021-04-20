function setupGui(Assembly2Draw) {

effectController = {
EArmVO:0,
EArmRot:0
};

var gui = new dat.GUI();
	
h = gui.addFolder("Mounting Structure");
h.add(effectController, "EArmVO", -0.5, 0.5, 0.01).name("Extend Arm Vert Offset");
h.add(effectController, "EArmRot", -89.0, 89.0, 0.025).name("Rotate Arms");

Arms = ['AssemblyA','AssemblyB','AssemblyC'];

	for (iArm = 0; iArm < Arms.length; iArm++) {
		var Assembly = Arms[iArm];
		
		effectController[Assembly] = {
ArmDist:0,
ArmRot:0,
PlateHR:0,
Pipe1H:0.35,
Pipe1V:0,
Pipe1R:0,
Pipe2H:0,
Pipe2V:0,
Pipe2R:0,
Pipe3H:-0.35,
Pipe3V:0,
Pipe3R:0,
		};
	
	x = gui.addFolder(Assembly);
	x.add(effectController[Assembly], "ArmDist", 0, 100, 0.01).name("Move Arm Hori");
	x.add(effectController[Assembly], "ArmRot", -89.0, 89.0, 0.025).name("Rotate Arm");
	x.add(effectController[Assembly], "PlateHR", -89.0, 89.0, 0.025).name("Rotate Plate");
	x.add(effectController[Assembly], "Pipe1H", -0.5, 0.5, 0.01).name("Pipe 1 Horizontal");
	x.add(effectController[Assembly], "Pipe1V", -0.5, 0.5, 0.01).name("Pipe 1 Vertical");
	x.add(effectController[Assembly], "Pipe1R", -90.0, 90.0, 0.025).name("Pipe 1 Rot");
	x.add(effectController[Assembly], "Pipe2H", -0.5, 0.5, 0.01).name("Pipe 2 Horizontal");
	x.add(effectController[Assembly], "Pipe2V", -0.5, 0.5, 0.01).name("Pipe 2 Vertical");
	x.add(effectController[Assembly], "Pipe2R", -90.0, 90.0, 0.025).name("Pipe 2 Rot");
	x.add(effectController[Assembly], "Pipe3H", -0.5, 0.5, 0.01).name("Pipe 3 Horizontal");
	x.add(effectController[Assembly], "Pipe3V", -0.5, 0.5, 0.01).name("Pipe 3 Vertical");
	x.add(effectController[Assembly], "Pipe3R", -90.0, 90.0, 0.025).name("Pipe 3 Rot");
	
};	

}