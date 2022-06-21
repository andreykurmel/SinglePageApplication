//fixed variables
var container, scene, camera, renderer, controls, effectController;
var SCREEN_WIDTH = window.innerWidth; 
var SCREEN_HEIGHT = window.innerHeight; 

//Variables for raycaster
var projector = new THREE.Projector(), 
mouse_vector = new THREE.Vector3(),
mouse = { x: 0, y: 0, z: 1 },
ray = new THREE.Raycaster( new THREE.Vector3(0,0,0), new THREE.Vector3(0,0,0) );
intersects = []; 
var Intersected;

var keyboard;
//Accuracy for displaying numbers on screen and XML
var accuracy = 4;
var axis;
var world_text, local_text;

//Sections properties (to be read from SQL)
// var I_d = 23.6, I_bf = 7.01, I_T = 20.75, I_tw = 0.395, I_tf = 0.505, I_k1 = 1,I_height = 200;
// var L_d = 5, L_b = 3.5, L_xbar = 0.854, L_ybar = 1.6, L_t = 0.375;
// var Pipe_OD =2.25 , Pipe_tnom = 0.322;
// var PipeS_OD =1 , PipeS_tnom = 0.322;
// var UB_rad = 2.6, UB_thick = 0.2, UB_Length = 5.2;
// var UB_Length = 7.6, UB_Width = 4.7, UB_thread = 2.75, UB_thick = 0.26;
// var UBT2_Length = 6.6, UBT2_Width = 4.7, UBT2_thread = 3.2, UBT2_thick = 0.26;
// var Nut_rad = UB_thick*1.72, Nut_thick = UB_thick*1;

//Variables to get wolrd coordinates from world
var parentName;
var position = new THREE.Vector3();
var quaternion = new THREE.Quaternion();
var scale = new THREE.Vector3();

//Collector for on click event
var ray_objects = [];

//Objects in the scene
var ExArm, ExPipe ; 
var LCP1, P3150, P263_Helper, SP219, P263;

//Assemblies

var SV197_36,SP216;