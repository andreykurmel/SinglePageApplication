function toNum($var) {
    let res = parseFloat($var);
    return isNaN(res) ? 0 : res;
}

function fraction2decimal($val) {
    let res = 0;
    if ($val) {
        let arr = String($val).replace(/\s/gi, ' ').split(' ');
        arr.forEach((el) => {
            if (el.indexOf('/') > -1) {
                let frac = el.split('/');
                res += toNum(frac[0]) / toNum(frac[1]);
            } else {
                res += toNum(el);
            }
        });
    }
    return res;
}

function get_nodes_for_aisc_shape(Shape, shapeData, Size2 ){

    var nodes = '', nodes_hole = '', nodes_ctrl = '';

    switch (Shape) {
        case 'W':
        case "I":
        case "M":
        case "HP":

            // console.log('d: ' + shapeData.d + ', bf: ' + shapeData.b_f + ', t:' + shapeData.t + ', k_det: ' + shapeData.k_det + ', t_w: ' + shapeData.t_w + ', t_f: ' + shapeData.t_f + ', k_1: ' + shapeData.k_1);

            var d = parseFloat(shapeData.d) / 12;
            var bf = parseFloat(shapeData.b_f) / 12;
            var max_dim_lth = Math.max(d, bf); // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            /*            for (var idir = 0; idir < 3; idir++) {
             app_p1[idir] = app_p1[idir]*zoomscale;
             app_p2[idir] = app_p2[idir]*zoomscale;
             }*/

            var T = parseFloat(shapeData.t) / 12;
            // console.log('T_before: ' + T);
            if (!T) {
                // console.log('k_det, before parseFloat: ' + shapeData.k_det);
                // console.log('k_det, after parseFloat: ' + parseFloat(shapeData.k_det));
                // console.log('parseFloat(shapeData.k_det): ' + parseFloat(shapeData.k_det));
                // console.log('fraction2decimal(shapeData.k_det): ' + fraction2decimal(shapeData.k_det));
                // var k_det = parseFloat(shapeData.k_det) / 12;
                var k_det = fraction2decimal(shapeData.k_det) / 12;
                T = d - 2 * k_det;
            }
            // console.log('T_after: ' + T);

            var T = T * zoomscale;
            var d = d * zoomscale;
            var bf = bf * zoomscale;

            var tw = parseFloat(shapeData.t_w) / 12 * zoomscale;

            var tf = parseFloat(shapeData.t_f) / 12 * zoomscale;
            // var k1 = parseFloat(shapeData.k_1) / 12*zoomscale;
            var k1 = fraction2decimal(shapeData.k_1) / 12 * zoomscale;

            // console.log('shapeData.k_1: ' + shapeData.k_1);
            // console.log('parseFloat(shapeData.k_1): ' + parseFloat(shapeData.k_1));
            // console.log('fraction2decimal(shapeData.k_1): ' + fraction2decimal(shapeData.k_1));
            //
            // console.log('zoomscale: ' + zoomscale);
            // console.log('d: ' + d + ', bf: ' + bf + ', T: ' + T + ', tw: ' + tw + ', tf: ' + tf + ', k1: ' + k1);

            var nodes = [
                [-bf / 2, -d / 2],
                [bf / 2, -d / 2],
                [bf / 2, -d / 2 + tf],

                [k1, -d / 2 + tf],

                [tw / 2, -T / 2],
                [tw / 2, T / 2],

                [k1, d / 2 - tf],

                [bf / 2, d / 2 - tf],
                [bf / 2, d / 2],
                [-bf / 2, d / 2],
                [-bf / 2, d / 2 - tf],
                [-k1, d / 2 - tf],
                [-tw / 2, T / 2],
                [-tw / 2, -T / 2],
                [-k1, -d / 2 + tf],
                [-bf / 2, -d / 2 + tf],
                [-bf / 2, -d / 2]];

            var nbr_div = 3;
            var inode = 3;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            var nodes_ctrl_1 = [+(node_mdl[0] - dist / nbr_div), +(node_mdl[1] - dist / nbr_div)]; 
            var nodes_ctrl_2 = [+(node_mdl[0] - dist / nbr_div), -(node_mdl[1] - dist / nbr_div)]; 
            var nodes_ctrl_3 = [-(node_mdl[0] - dist / nbr_div), -(node_mdl[1] - dist / nbr_div)]; 
            var nodes_ctrl_4 = [-(node_mdl[0] - dist / nbr_div), +(node_mdl[1] - dist / nbr_div)];

            var nodes_ctrl = [nodes_ctrl_1, nodes_ctrl_2, nodes_ctrl_3, nodes_ctrl_4];

            nodes = [crdtTrft(nodes)];
            var nodes_ctrl = [crdtTrft(nodes_ctrl)];   

            break;

        case "WT":
        case "MT":
            var d = parseFloat(shapeData.d) / 12;
            var bf = parseFloat(shapeData.b_f) / 12;
            var max_dim_lth = Math.max(d, bf); // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            var T = parseFloat(shapeData.t) / 12;
            // console.log('T_before: ' + T);
            if (!T) {
                // console.log('k_det, before parseFloat: ' + shapeData.k_det);
                // console.log('k_det, after parseFloat: ' + parseFloat(shapeData.k_det));
                // console.log('parseFloat(shapeData.k_det): ' + parseFloat(shapeData.k_det));
                // console.log('fraction2decimal(shapeData.k_det): ' + fraction2decimal(shapeData.k_det));
                // var k_det = parseFloat(shapeData.k_det) / 12;
                var k_det = fraction2decimal(shapeData.k_det) / 12;
                T = d - 1 * k_det;
            }
            // console.log('T_after: ' + T);

            var T = T * zoomscale;
            var d = d * zoomscale;
            var bf = bf * zoomscale;

            var tw = parseFloat(shapeData.t_w) / 12 * zoomscale;

            var tf = parseFloat(shapeData.t_f) / 12 * zoomscale;
            // var k1 = parseFloat(shapeData.k_1) / 12*zoomscale;
            // var k1 = fraction2decimal(shapeData.k_1) / 12 * zoomscale;
            var k_des = parseFloat(shapeData.k_des) / 12 * zoomscale;
            var k1 = k_des - tf + tw/2; // assume the curve is 90 degree

            var y = parseFloat(shapeData.y) / 12 * zoomscale;

            var xx0 = tw/2, yy0 = -(d - y);
            var xx1 = tw/2, yy1 = y - k_des;
            var xx2 = k1,   yy2 = y - tf;
            var xx3 = bf/2, yy3 = y - tf;
            var xx4 = bf/2, yy4 = y;
            var xx5 = -xx4, yy5 = yy4;
            var xx6 = -xx3, yy6 = yy3;
            var xx7 = -xx2, yy7 = yy2;
            var xx8 = -xx1, yy8 = yy1;
            var xx9 = -xx0, yy9 = yy0;

            var nodes = [
                [xx0, yy0],
                [xx1, yy1],
                [xx2, yy2],
                [xx3, yy3],
                [xx4, yy4],
                [xx5, yy5],
                [xx6, yy6],
                [xx7, yy7],                                                                                                                
                [xx8, yy8], 
                [xx9, yy9]];

            // var xx_list = [xx0, xx1, xx2, xx3, xx4, xx5, xx6, xx7, xx8, xx9];
            // var x_max = Math.max.apply(null, xx_list);
            // var x_min = Math.min.apply(null, xx_list);

            // var yy_list = [yy0, yy1, yy2, yy3, yy4, yy5, yy6, yy7, yy8, yy9];
            // var y_max = Math.max.apply(null, yy_list);
            // var y_min = Math.min.apply(null, yy_list);                

            var nbr_div = 3;
            var inode = 1;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            var nodes_ctrl_1 = [+(node_mdl[0] - dist / nbr_div), (node_mdl[1] + dist / nbr_div)]; 
            var nodes_ctrl_2 = [-(node_mdl[0] - dist / nbr_div), (node_mdl[1] + dist / nbr_div)]; 


            var nodes_ctrl = [nodes_ctrl_1, nodes_ctrl_2];

            nodes = [crdtTrft(nodes)];
            var nodes_ctrl = [crdtTrft(nodes_ctrl)];         
            break;              

        case "S":
            var d  = parseFloat(shapeData.d) / 12;
            var bf = parseFloat(shapeData.b_f) / 12;

            var tf = parseFloat(shapeData.t_f) / 12;

            var tw = parseFloat(shapeData.t_w) / 12;

            // var xbar = parseFloat(shapeData.x) / 12; // not applicable for S

            var k_des = parseFloat(shapeData.k_des) / 12;

            var angle = 130.26883890*Math.PI/180;
            var beta = Math.PI - angle; // PI - 2*(PI - angle);

            var slope = 2/12;

            var R1    = (tf - slope*(bf - tw)/2)/(Math.sin(beta) - slope*(1 - Math.cos(beta))); // (12*tf - (bf - tw))/(12*Math.sin(beta) - 2 + 2*Math.cos(beta)); 

            var R2    = (k_des - (bf - tw)/2*slope - tf) / (Math.sin(beta) - slope*(1 - Math.cos(beta))); // 12*(k_des - (bf - tw)/12 - tf) / (12*Math.sin(beta) - 2*(1 - Math.cos(beta)));

            
            // console.log(R1);
            // console.log(R2);

            var str1  = R1 * Math.sin(beta/2) *2;
            var str2  = R2 * Math.sin(beta/2) *2;

            var max_dim_lth = Math.max(d, bf); // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            d = d * zoomscale;
            bf = bf * zoomscale;
            tf = tf * zoomscale;
            tw = tw * zoomscale;
            // xbar  = xbar * zoomscale;
            k_des = k_des * zoomscale;

            R1   = R1 * zoomscale;     R2 = R2 * zoomscale;
            str1 = str1 * zoomscale; str2 = str2 * zoomscale;

            var x_0 = -xbar, y_0 = -d / 2;
            var x_1 = -xbar + bf, y_1 = -d / 2;

            var R_fl = tf;
            var slope = (16 + 2/3)/100;
            var alpha = Math.atan(slope);
            var delta_x_1_to_2 = R_fl*Math.sin(alpha);
            var delta_y_1_to_2 = R_fl*Math.cos(alpha);

            var x_2 =  x_1 -  delta_x_1_to_2;          

            var R_f2w = tw;
            var x_4 = -xbar + tw;
            // var x_3 = x-4 + R_f2w - R_f2w*Math.sin(alpha);

            var delta_x_2_to_3 =  0;

            var shape = new THREE.Shape();

            var xx0 = -bf/2; yy0 = -d / 2;
            var xx1 = -xx0;  yy1 = yy0;

            var xx2 = bf/2 - str1*Math.cos((Math.PI - beta)/2); // -xbar + bf - tf
            var yy2 = -d / 2      + str1*Math.sin((Math.PI - beta)/2); // -d / 2 + tf

            var xx4 = tw/2;
            var yy4 = -d / 2 + k_des;

            var xx3 = xx4 + str2*Math.cos((Math.PI - beta)/2); // -xbar + tw + tw;
            var yy3 = yy4 - str2*Math.sin((Math.PI - beta)/2); // -d / 2 + tf; 

            var xx5 = xx4, yy5 = -yy4; // d / 2 - tf - tw   
            var xx6 = xx3, yy6 = -yy3; // d / 2 - tf
            var xx7 = xx2, yy7 = -yy2; // d / 2 - tf
            var xx8 = xx1, yy8 = -yy1;

            var xx9 = -xx8,  yy9 = yy8;
            var xx10 = -xx7, yy10 = yy7;
            var xx11 = -xx6, yy11 = yy6;
            var xx12 = -xx5, yy12 = yy5;
            var xx13 = -xx4, yy13 = yy4;
            var xx14 = -xx3, yy14 = yy3;
            var xx15 = -xx2, yy15 = yy2;

            var nodes = [
                [xx0, yy0],
                [xx1, yy1],
                [xx2, yy2],
                [xx3, yy3],
                [xx4, yy4],
                [xx5, yy5],
                [xx6, yy6],
                [xx7, yy7],
                [xx8, yy8],
                [xx9, yy9],
                [xx10, yy10],
                [xx11, yy11],
                [xx12, yy12],
                [xx13, yy13],
                [xx14, yy14],                                                 
                [xx15, yy15]];

            // var xx_list = [xx0, xx1, xx2, xx3, xx4, xx5, xx6, xx7, xx8, xx9, xx10, xx11, xx12, xx13, xx14, xx15];
            // var x_max = Math.max.apply(null, xx_list);
            // var x_min = Math.min.apply(null, xx_list);

            // var yy_list = [yy0, yy1, yy2, yy3, yy4, yy5, yy6, yy7, yy8, yy9, yy10, yy11, yy12, yy13, yy14, yy15];
            // var y_max = Math.max.apply(null, yy_list);
            // var y_min = Math.min.apply(null, yy_list);

            var nbr_div = 3;

            var inode = 1;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            // console.log(dist);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            // console.log(node_mdl);
            var node_ctrl_1 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] + dist / nbr_div)];
            var node_ctrl_4 = [node_ctrl_1[0], -node_ctrl_1[1]];

            var inode = 3;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            var node_ctrl_2 = [(node_mdl[0] - dist / nbr_div), (node_mdl[1] - dist / nbr_div)];
            var node_ctrl_3 = [node_ctrl_2[0], -node_ctrl_2[1]];
            // console.log(nodes);

            var node_ctrl_5 = [-node_ctrl_4[0], +node_ctrl_4[1]];
            var node_ctrl_6 = [-node_ctrl_3[0], +node_ctrl_3[1]];
            var node_ctrl_7 = [-node_ctrl_2[0], +node_ctrl_2[1]];
            var node_ctrl_8 = [-node_ctrl_1[0], +node_ctrl_1[1]];

            var nodes_ctrl = [node_ctrl_1, node_ctrl_2, node_ctrl_3, node_ctrl_4, node_ctrl_5, node_ctrl_6, node_ctrl_7, node_ctrl_8];

            nodes = [crdtTrft(nodes)];
            var nodes_ctrl = [crdtTrft(nodes_ctrl)]; 
            // console.log(nodes_ctrl);        
            break;

        case "ST":

            var d  = parseFloat(shapeData.d) / 12;
            var bf = parseFloat(shapeData.b_f) / 12;

            var tf = parseFloat(shapeData.t_f) / 12;

            var tw = parseFloat(shapeData.t_w) / 12;

            // var xbar = parseFloat(shapeData.x) / 12; // not applicable for S

            var k_des = parseFloat(shapeData.k_des) / 12;
            var k1 = k_des - tf + tw/2; // assume the curve is 90 degree            

            var angle = 130.26883890*Math.PI/180;
            var beta = Math.PI - angle; // PI - 2*(PI - angle);

            var slope = 2/12;

            var R1    = (tf - slope*(bf - tw)/2)/(Math.sin(beta) - slope*(1 - Math.cos(beta))); // (12*tf - (bf - tw))/(12*Math.sin(beta) - 2 + 2*Math.cos(beta)); 

            var R2    = (k_des - (bf - tw)/2*slope - tf) / (Math.sin(beta) - slope*(1 - Math.cos(beta))); // 12*(k_des - (bf - tw)/12 - tf) / (12*Math.sin(beta) - 2*(1 - Math.cos(beta)));

            // console.log(R1);
            // console.log(R2);

            var str1  = R1 * Math.sin(beta/2) *2;
            var str2  = R2 * Math.sin(beta/2) *2;

            var max_dim_lth = Math.max(d, bf); // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            d = d * zoomscale;
            bf = bf * zoomscale;
            tf = tf * zoomscale;
            tw = tw * zoomscale;
            // xbar  = xbar * zoomscale;
            k_des = k_des * zoomscale;

            R1 = R1 * zoomscale; R2 = R2 * zoomscale;
            str1 = str1 * zoomscale; str2 = str2 * zoomscale;             

            var y = parseFloat(shapeData.y) / 12;

            var x_0 = -xbar, y_0 = -d / 2;
            var x_1 = -xbar + bf, y_1 = -d / 2;

            var R_fl = tf;
            var slope = (16 + 2/3)/100;
            var alpha = Math.atan(slope);
            var delta_x_1_to_2 = R_fl*Math.sin(alpha);
            var delta_y_1_to_2 = R_fl*Math.cos(alpha);

            var x_2 =  x_1 -  delta_x_1_to_2;          

            var R_f2w = tw;
            var x_4 = -xbar + tw;
            // var x_3 = x-4 + R_f2w - R_f2w*Math.sin(alpha);

            var delta_x_2_to_3 =  0;

            var shape = new THREE.Shape();

            var xx0 = tw/2, yy0 = -(d - y);
            var xx1 = tw/2, yy1 = y - k_des;
            var xx2 = xx1 + str2*Math.cos((Math.PI - beta)/2);
            var yy2 = yy1 + str2*Math.sin((Math.PI - beta)/2);
            var xx4 = bf/2, yy4 = y;
            var xx3 = xx4 - str1*Math.cos((Math.PI - beta)/2);
            var yy3 = yy4 - str1*Math.sin((Math.PI - beta)/2);

            var xx5 = -xx4, yy5 = yy4;
            var xx6 = -xx3, yy6 = yy3;
            var xx7 = -xx2, yy7 = yy2;
            var xx8 = -xx1, yy8 = yy1;
            var xx9 = -xx0, yy9 = yy0;

            var nodes = [
                [xx0, yy0],
                [xx1, yy1],
                [xx2, yy2],
                [xx3, yy3],
                [xx4, yy4],
                [xx5, yy5],
                [xx6, yy6],
                [xx7, yy7],
                [xx8, yy8],
                [xx9, yy9]];

            // var xx_list = [xx0, xx1, xx2, xx3, xx4, xx5, xx6, xx7, xx8, xx9];
            // var x_max = Math.max.apply(null, xx_list);
            // var x_min = Math.min.apply(null, xx_list);

            // var yy_list = [yy0, yy1, yy2, yy3, yy4, yy5, yy6, yy7, yy8, yy9];
            // var y_max = Math.max.apply(null, yy_list);
            // var y_min = Math.min.apply(null, yy_list);                

            var nbr_div = 3;

            var inode = 1;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            // console.log(dist);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            // console.log(node_mdl);
            var node_ctrl_1 = [(node_mdl[0] - dist / nbr_div), (node_mdl[1] + dist / nbr_div)];
            var node_ctrl_4 = [-node_ctrl_1[0], +node_ctrl_1[1]];

            var inode = 3;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            var node_ctrl_2 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] - dist / nbr_div)];
            var node_ctrl_3 = [-node_ctrl_2[0], +node_ctrl_2[1]];

            var nodes_ctrl = [node_ctrl_1, node_ctrl_2, node_ctrl_3, node_ctrl_4];

            nodes = [crdtTrft(nodes)];
            var nodes_ctrl = [crdtTrft(nodes_ctrl)]; 
            // console.log(nodes_ctrl);               

            break;            

        case "L":
        case "L_E":
        case "L_uE":
        case "Single Equal Angle":
        case "Single unEqual Angle":
        case "Single Angle":

            // console.log('L / shapeData.d: ' + shapeData.d);

            var d = parseFloat(shapeData.d) / 12;
            var b = parseFloat(shapeData.b) / 12; // d >= b
            var max_dim_lth = Math.max(d, b); // to the get the maxium dimension in two directions.
            // var max_dim_lth = b; // b is always greater than d
            //     var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft

            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            // console.log('zoomscale: ' + zoomscale);          

            var d = d * zoomscale;
            var b = b * zoomscale;
            var t = parseFloat(shapeData.t) / 12 * zoomscale;
            var xbar = parseFloat(shapeData.x) / 12 * zoomscale;
            var ybar = parseFloat(shapeData.y) / 12 * zoomscale;

            // console.log('d = ' + d + ', b = ' + b + ', t =  ' + t + ', xbar = ' + xbar + ', ybar = ' + ybar);
            // console.log(-xbar, d - ybar);

            // var xx0 = -xbar,     yy0 = b - ybar;
            // var xx1 = -xbar + t, yy1 = b - ybar - t;
            // var xx2 = -xbar + t, yy2 = -ybar + t + t;
            // var xx3 = -xbar + t + t, yy3 = -ybar + t;
            // var xx4 = -xbar + d - t, yy4 = -ybar + t;
            // var xx5 = -xbar + d, yy5 = -ybar;
            // var xx6 = -xbar,     yy6 = -ybar;
            // var xx7 = -xbar + t, yy7 = b - ybar;
            // var xx8 = 0,         yy8 = b - ybar;
            // var xx9 = -xbar + d, yy9 = 0;            

            // in shape's local coordinate system
            var nodes = [[-xbar, b - ybar],
            [-xbar + t, b - ybar - t],
            [-xbar + t, -ybar + t + t],
            [-xbar + t + t, -ybar + t],
            [-xbar + d - t, -ybar + t],
            [-xbar + d, -ybar],
            [-xbar, -ybar],
            [-xbar + t, b - ybar],
            [0, b - ybar],
            [-xbar + d, 0]];

            // var xx_list = [xx0, xx1, xx2, xx3, xx4, xx5, xx6, xx7, xx8, xx9];
            // var x_max = Math.max.apply(null, xx_list);
            // var x_min = Math.min.apply(null, xx_list);

            // var yy_list = [yy0, yy1, yy2, yy3, yy4, yy5, yy6, yy7, yy8, yy9];
            // var y_max = Math.max.apply(null, yy_list);
            // var y_min = Math.min.apply(null, yy_list);            

            var nodes_part1 = nodes;

            var nbr_div = 3;

            var inode = 0;
            var dist = Math.pow(Math.pow(nodes_part1[inode][0] - nodes_part1[inode + 1][0], 2) + Math.pow(nodes_part1[inode][1] - nodes_part1[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes_part1[inode][0] + nodes_part1[inode + 1][0]) / 2, (nodes_part1[inode][1] + nodes_part1[inode + 1][1]) / 2];
            var node_part1_ctrl_1 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] + dist / nbr_div)];                                   

            var inode = 2;
            var dist = Math.pow(Math.pow(nodes_part1[inode][0] - nodes_part1[inode + 1][0], 2) + Math.pow(nodes_part1[inode][1] - nodes_part1[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes_part1[inode][0] + nodes_part1[inode + 1][0]) / 2, (nodes_part1[inode][1] + nodes_part1[inode + 1][1]) / 2];
            var node_part1_ctrl_2 = [node_mdl[0] - dist / nbr_div, node_mdl[1] - dist / nbr_div];          

            var inode = 4;
            var dist = Math.pow(Math.pow(nodes_part1[inode][0] - nodes_part1[inode + 1][0], 2) + Math.pow(nodes_part1[inode][1] - nodes_part1[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes_part1[inode][0] + nodes_part1[inode + 1][0]) / 2, (nodes_part1[inode][1] + nodes_part1[inode + 1][1]) / 2];
            var node_part1_ctrl_3 = [node_mdl[0] + dist / nbr_div, node_mdl[1] + dist / nbr_div];
                    
            var nodes_part1_ctrl = [node_part1_ctrl_1, node_part1_ctrl_2, node_part1_ctrl_3]; 

            nodes = [crdtTrft(nodes)];
            var nodes_ctrl = [crdtTrft(nodes_part1_ctrl)];

            break;

        case '2L_E':
        case '2L_LLBB':
        case '2L_SLBB':

            var d = parseFloat(shapeData.d) / 12;
            var b = parseFloat(shapeData.b) / 12; // d <= b in AISC table, always

            var max_dim_lth = 2 * b;

            // var zoomscale1 = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            if(Shape == '2L_SLBB'){
                var dx = d;
                var bx = b;
                var d = bx * zoomscale;
                var b = dx * zoomscale;
            }else{
                var d = d * zoomscale;
                var b = b * zoomscale;
            }

            var t = parseFloat(shapeData.t) / 12 * zoomscale;
            // var xbar = parseFloat(shapeData.x)/12*zoomscale;
            var ybar = parseFloat(shapeData.y) / 12 * zoomscale; // only ybar is provided in db
            var xbar = 0;

            // console.log('d = ' + d + ', b = ' + b + ', t =  ' + t + ', xbar = ' + xbar + ', ybar = ' + ybar);
            // console.log(-xbar, d - ybar);

            // in shape's local coordinate system
            var nodes_part1 = [
                [-xbar, b - ybar],
                [-xbar + t, b - ybar - t],
                [-xbar + t, -ybar + t + t],
                [-xbar + t + t, -ybar + t],
                [-xbar + d - t, -ybar + t],
                [-xbar + d, -ybar],
                [-xbar, -ybar],
                [-xbar + t, b - ybar],
                [0, b - ybar],
                [-xbar + d, 0]
            ];

            var sp_3thX = getPosition(Size2,"X", 3);
            // console.log('Size2: ' + Size2);
            // console.log('sp_3thX: ' + sp_3thX);
            if(sp_3thX == Size2.length){
                // no spacing in the Size2.
                var spacing = 0;
            }else{
                // console.log('Size2.substr(sp_3thX + 1, 3): ' + Size2.substr(sp_3thX + 1, 3));
                // console.log('fraction2decimal(Size2.substr(sp_3thX + 1, 3)): ' + fraction2decimal(Size2.substr(sp_3thX + 1, 3)));
                var spacing = fraction2decimal(Size2.substr(sp_3thX + 1, 3)) / 12 * zoomscale;
                if(isNaN(spacing)) {
                    spacing = 1 / 12 * zoomscale;
                }
            }
            // console.log('spacing: ' + spacing);

            for (var i = 0; i < nodes_part1.length; i++) {
                nodes_part1[i][0] = nodes_part1[i][0] + spacing / 2;
            }

            var nbr_div = 3;

            var inode = 0;
            var dist = Math.pow(Math.pow(nodes_part1[inode][0] - nodes_part1[inode + 1][0], 2) + Math.pow(nodes_part1[inode][1] - nodes_part1[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes_part1[inode][0] + nodes_part1[inode + 1][0]) / 2, (nodes_part1[inode][1] + nodes_part1[inode + 1][1]) / 2];
            var node_part1_ctrl_1 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] + dist / nbr_div)];                                   

            var inode = 2;
            var dist = Math.pow(Math.pow(nodes_part1[inode][0] - nodes_part1[inode + 1][0], 2) + Math.pow(nodes_part1[inode][1] - nodes_part1[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes_part1[inode][0] + nodes_part1[inode + 1][0]) / 2, (nodes_part1[inode][1] + nodes_part1[inode + 1][1]) / 2];
            var node_part1_ctrl_2 = [node_mdl[0] - dist / nbr_div, node_mdl[1] - dist / nbr_div];          

            var inode = 4;
            var dist = Math.pow(Math.pow(nodes_part1[inode][0] - nodes_part1[inode + 1][0], 2) + Math.pow(nodes_part1[inode][1] - nodes_part1[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes_part1[inode][0] + nodes_part1[inode + 1][0]) / 2, (nodes_part1[inode][1] + nodes_part1[inode + 1][1]) / 2];
            var node_part1_ctrl_3 = [node_mdl[0] + dist / nbr_div, node_mdl[1] + dist / nbr_div];
                    
            var nodes_part1_ctrl = [node_part1_ctrl_1, node_part1_ctrl_2, node_part1_ctrl_3];


            var nodes_part2 = [];
            var nodes_part2_ctrl = [];

            for (var i = 0; i < nodes_part1.length; i++) {
                nodes_part2[i] = [];
                nodes_part2[i][0] = nodes_part1[i][0] * -1;
                nodes_part2[i][1] = nodes_part1[i][1];
            }

            for (var i = 0; i < nodes_part1_ctrl.length; i++) {
                nodes_part2_ctrl[i] = [];
                nodes_part2_ctrl[i][0] = nodes_part1_ctrl[i][0] * -1;
                nodes_part2_ctrl[i][1] = nodes_part1_ctrl[i][1];                            
            }

            var nodes = [crdtTrft(nodes_part1), crdtTrft(nodes_part2)];
            var nodes_ctrl = [crdtTrft(nodes_part1_ctrl), crdtTrft(nodes_part2_ctrl)];

/*            console.log('nodes: ');
            console.log(nodes);

            console.log('nodes_ctrl: ');
            console.log(nodes_ctrl);*/

            break;

        case "C":
        case "MC":
            var d  = parseFloat(shapeData.d) / 12;
            var bf = parseFloat(shapeData.b_f) / 12;
            var tf = parseFloat(shapeData.t_f) / 12;
            var tw = parseFloat(shapeData.t_w) / 12;
            var xbar = parseFloat(shapeData.x) / 12;
            var k_des = parseFloat(shapeData.k_des) / 12;

            var angle = 130.26883890*Math.PI/180;
            var beta = Math.PI - angle; // PI - 2*(PI - angle);

            var slope = 2/12;

            var R1    = (tf - slope*(bf - tw)/2)/(Math.sin(beta) - slope*(1 - Math.cos(beta))); // (12*tf - (bf - tw))/(12*Math.sin(beta) - 2 + 2*Math.cos(beta)); 

            var R2    = (k_des - (bf - tw)/2*slope - tf) / (Math.sin(beta) - slope*(1 - Math.cos(beta))); // 12*(k_des - (bf - tw)/12 - tf) / (12*Math.sin(beta) - 2*(1 - Math.cos(beta)));

            // console.log(R1);
            // console.log(R2);

            var str1  = R1 * Math.sin(beta/2) *2;
            var str2  = R2 * Math.sin(beta/2) *2;

            var max_dim_lth = Math.max(d, bf); // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            d = d * zoomscale;
            bf = bf * zoomscale;
            tf = tf * zoomscale;
            tw = tw * zoomscale;
            xbar  = xbar * zoomscale;
            k_des = k_des * zoomscale;

            R1 = R1 * zoomscale; str1 = str1 * zoomscale;
            R2 = R2 * zoomscale; str2 = str2 * zoomscale;

            var x_0 = -xbar, y_0 = -d / 2;
            var x_1 = -xbar + bf, y_1 = -d / 2;

            var R_fl = tf;
            var slope = (16 + 2/3)/100;
            var alpha = Math.atan(slope);
            var delta_x_1_to_2 = R_fl*Math.sin(alpha);
            var delta_y_1_to_2 = R_fl*Math.cos(alpha);

            var x_2 =  x_1 -  delta_x_1_to_2;          

            var R_f2w = tw;
            var x_4 = -xbar + tw;
            // var x_3 = x-4 + R_f2w - R_f2w*Math.sin(alpha);

            var delta_x_2_to_3 =  0;

            // var shape = new THREE.Shape();

            var xx2 = -xbar  + bf - str1*Math.cos((Math.PI - beta)/2); // -xbar + bf - tf
            var yy2 = -d / 2      + str1*Math.sin((Math.PI - beta)/2); // -d / 2 + tf

            var xx4 = -xbar + tw;
            var yy4 = -d / 2 + k_des;

            var xx3 = xx4 + str2*Math.cos((Math.PI - beta)/2); // -xbar + tw + tw;
            var yy3 = yy4 - str2*Math.sin((Math.PI - beta)/2); // -d / 2 + tf; 

            var xx5 = xx4;  // -xbar + tw
            var yy5 = -yy4; // d / 2 - tf - tw   

            var xx6 = xx3; // -xbar + tw + tw
            var yy6 = -yy3; // d / 2 - tf

            var xx7 = xx2; // -xbar + bf - tf
            var yy7 = -yy2 // d / 2 - tf

            var nodes = [
                [-xbar,           -d / 2],
                [-xbar + bf,      -d / 2],
                [xx2, yy2],
                [xx3, yy3],
                [xx4, yy4],
                [xx5, yy5],
                [xx6, yy6],
                [xx7, yy7],
                [-xbar + bf,       d / 2],
                [-xbar,            d / 2],
                [-xbar,           -d / 2]];

            var nbr_div = 3;

            var inode = 1;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            // console.log(dist);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            // console.log(node_mdl);
            var node_ctrl_1 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] + dist / nbr_div)];
            var node_ctrl_4 = [node_ctrl_1[0], -node_ctrl_1[1]];

            var inode = 3;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            var node_ctrl_2 = [(node_mdl[0] - dist / nbr_div), (node_mdl[1] - dist / nbr_div)];
            var node_ctrl_3 = [node_ctrl_2[0], -node_ctrl_2[1]];
            // console.log(nodes);

            var nodes_ctrl = [node_ctrl_1, node_ctrl_2, node_ctrl_3, node_ctrl_4];

            nodes = [crdtTrft(nodes)];
            var nodes_ctrl = [crdtTrft(nodes_ctrl)]; 
            // console.log(nodes_ctrl);

            break;

        case "HSS_Rect":
        case "HSS_Sqr":
        case "HSS(Rect)":
        case "HSS(Sqr)":

            var Ht = parseFloat(shapeData.Ht) / 12;
            var B = parseFloat(shapeData.B_upr) / 12;
            var max_dim_lth = Math.max(Ht, B); // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            //if(!zoomscale) zoomscale = 400;
            Ht = Ht * zoomscale;
            B = B * zoomscale;

            var h = parseFloat(shapeData.h) / 12 * zoomscale;
            var b = parseFloat(shapeData.b) / 12 * zoomscale;

            var t_des = parseFloat(shapeData.t_des) / 12 * zoomscale;

            var f1 = (Ht - h) / 2;

            // console.log("shapeData.h",shapeData.h,shapeData.b, zoomscale);

            nodes = [
                [-b / 2 + f1, -h / 2],
                [b / 2 - f1, -h / 2],
                [b / 2, -h / 2 + f1],
                [b / 2, h / 2 - f1],
                [b / 2 - f1, h / 2],
                [-b / 2 + f1, h / 2],
                [-b / 2, h / 2 - f1],
                [-b / 2, -h / 2 + f1],
                [-b / 2 + f1, -h / 2]];

            var f1 = (Ht - h) / 4; // adjustable for the inner curved corner
            nodes_hole = [
                [-b / 2 + f1 + t_des, -h / 2 + t_des],
                [b / 2 - f1 - t_des, -h / 2 + t_des],
                [b / 2 - t_des, -h / 2 + f1 + t_des],
                [b / 2 - t_des, h / 2 - f1 - t_des],
                [b / 2 - f1 - t_des, h / 2 - t_des],
                [-b / 2 + f1 + t_des, h / 2 - t_des],
                [-b / 2 + t_des, h / 2 - f1 - t_des],
                [-b / 2 + t_des, -h / 2 + f1 + t_des],
                [-b / 2 + f1 + t_des, -h / 2 + t_des]];

            var nbr_div = 3;

            var inode = 1;;
            var dist = Math.pow(Math.pow(nodes[inode][0] - nodes[inode + 1][0], 2) + Math.pow(nodes[inode][1] - nodes[inode + 1][1], 2), 1 / 2);
            var node_mdl = [(nodes[inode][0] + nodes[inode + 1][0]) / 2, (nodes[inode][1] + nodes[inode + 1][1]) / 2];
            var node_ctrl_o1 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] - dist / nbr_div)];
            var node_ctrl_o2 = [+node_ctrl_o1[0], -node_ctrl_o1[1]];
            var node_ctrl_o3 = [-node_ctrl_o1[0], -node_ctrl_o1[1]];
            var node_ctrl_o4 = [-node_ctrl_o1[0], +node_ctrl_o1[1]];

            // console.log(nodes);

            // var nodes_ctrl_o = [node_ctrl_o1, node_ctrl_o2, node_ctrl_o3, node_ctrl_o4];

            var nbr_div = 5;
            var inode = 1;
            var dist = Math.pow(Math.pow(nodes_hole[inode][0] - nodes_hole[inode + 1][0], 2) + Math.pow(nodes_hole[inode][1] - nodes_hole[inode + 1][1], 2), 1 / 2);
            // console.log(dist);
            var node_mdl = [(nodes_hole[inode][0] + nodes_hole[inode + 1][0]) / 2, (nodes_hole[inode][1] + nodes_hole[inode + 1][1]) / 2];
            // console.log(node_mdl);
            var node_ctrl_i1 = [(node_mdl[0] + dist / nbr_div), (node_mdl[1] - dist / nbr_div)];
            var node_ctrl_i2 = [+node_ctrl_i1[0], -node_ctrl_i1[1]];
            var node_ctrl_i3 = [-node_ctrl_i1[0], -node_ctrl_i1[1]];
            var node_ctrl_i4 = [-node_ctrl_i1[0], +node_ctrl_i1[1]];

            // console.log(nodes_hole);

            // var nodes_ctrl_i = [node_ctrl_i1, node_ctrl_i2, node_ctrl_i3, node_ctrl_i4];

            var nodes_ctrl = [node_ctrl_o1, node_ctrl_o2, node_ctrl_o3, node_ctrl_o4, node_ctrl_i1, node_ctrl_i2, node_ctrl_i3, node_ctrl_i4];

            nodes = [crdtTrft(nodes)];
            nodes_hole = [crdtTrft(nodes_hole)];    
            // nodes_ctrl_o = [crdtTrft(nodes_ctrl_o)];        
            // nodes_ctrl_i = [crdtTrft(nodes_ctrl_i)]; 
            nodes_ctrl = [crdtTrft(nodes_ctrl)]; 

            break;

        case "PIPE":
        case "pipe":
        case "Pipe":
        case "HSS_Rnd":        
        case "HSS(Rnd)":
        case "Pipe(STD)":
        case "Pipe(XS)":
        case "Pipe(XXS)":

            var OD = parseFloat(shapeData.OD) / 12;
            var t_nom = parseFloat(shapeData.t_nom) / 12;

            var max_dim_lth = OD; // to the get the maxium dimension in two directions.
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            OD = OD * zoomscale;
            t_nom = t_nom * zoomscale;

            nodes = [OD];
            nodes_hole = [t_nom];

            break;

        case "SR":
            var OD = parseFloat(shapeData.OD) / 12;
            var t_nom = parseFloat(shapeData.t_nom) / 12;
            var max_dim_lth = OD;
            // var zoomscale = (canvas.height * 2 / 4) / max_dim_lth; // the plot will fit up to 2/4 of canvas width, pixles/ft
            var zoomscale = 1;
            // if(canvas) {
            //     var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
            // }

            OD = OD * zoomscale;
            nodes = [OD];

            nodes_hole = t_nom;

        default:
            // alert("Something went wrong - the given Shape: <" + Shape + "> not found - !");
            break;
    }

    // HSS: nodes = [crdtTrft(nodes)]; nodes = [[x, y]];
    // 2L:  nodes = [crdtTrft(nodes_part1), crdtTrft(nodes_part2)]; nodes_part1 =[[x, y]];
    var x_max = 0, x_min = 0, z_max = 0, z_min = 0;

    // console.log('START: inside drawAISCmember -before xy_max_min: ');
    // console.log(nodes);  
    // console.log('END: inside drawAISCmember -before xy_max_min: ');

    for (var ipart = 0; ipart < nodes.length; ipart++) {
        // console.log('ipart: ' + ipart);
        var nodes_part = nodes[ipart];
        for (var inode = 0; inode < nodes_part.length; inode++) {
            // console.log('inode: ' + inode);
            // console.log('before the comparision: ');
            // console.log('x_max: ' + x_max + ', x_min: ' + x_min + ', y_max: ' + y_max + ', y_min:' + y_min);
            // console.log('nodes_part[inode][0/1]: ' + nodes_part[inode][0] + ', ' + nodes_part[inode][1]);
            if( nodes_part[inode][0] > z_max){ z_max = nodes_part[inode][0] };
            if( nodes_part[inode][0] < z_min){ z_min = nodes_part[inode][0] };
            if( nodes_part[inode][1] > x_max){ x_max = nodes_part[inode][1] };
            if( nodes_part[inode][1] < x_min){ x_min = nodes_part[inode][1] };
            // console.log('after the comparision: ');
            // console.log('x_max: ' + x_max + ', x_min: ' + x_min + ', y_max: ' + y_max + ', y_min:' + y_min);
            // console.log('nodes_part[inode][0/1]: ' + nodes_part[inode][0] + ', ' + nodes_part[inode][1]);                                  
        }
    }
    var xz_max_min = {};
    xz_max_min['x_max'] = x_max;
    xz_max_min['x_min'] = x_min;
    xz_max_min['z_max'] = z_max;
    xz_max_min['z_min'] = z_min;

    // console.log('START: inside drawAISCmember: -after xz_max_min: ');
    // console.log('nodes: ');
    // console.log(nodes[0]);
    // console.log('nodes[0][0]: ' + nodes[0][0]);
    // console.log('nodes[0][0][1]: ' + nodes[0][0][1]);    
    // console.log('xz_max_min: ');    
    // console.log(xz_max_min);
    // console.log('END: inside drawAISCmember: -after xz_max_min: ');

    return [nodes, nodes_hole, nodes_ctrl, max_dim_lth, zoomscale, xz_max_min];
}

function drawAISCmember(XD, approach, app_p1, app_p2, app_p3, dbSRC, Shape, SizeType, SizeUnit, Size1, Size2, material, mat_color, rot_x2stdy, shrk_pct, mode, shrink_p, GridSize2D, sectionsInfo, Mbr_Name) {
    var mesh = new THREE.Mesh();

    shrink_p = shrink_p/100;
    
    // alert('Shape: ' + Shape);

    if (XD == "2D" || XD == "23D") {
        clear2D();

        var dom = document.getElementById("webgl");
        var element = document.getElementById("2d");
        var canvas = element.querySelector("canvas");

        if (canvas == null) {
            canvas = document.createElement("canvas");
        }

        var ctx_ShapeBdry = canvas.getContext("2d");

        canvas.width = dom.width;
        canvas.height = dom.height;

        ctx_ShapeBdry.font = "18px Times";
        ctx_ShapeBdry.translate(canvas.width / 2, canvas.height / 2);
        ctx_ShapeBdry.scale(1.0, 1.0);

        ctx_ShapeBdry.clearRect(0, 0, canvas.width, canvas.height);
    }

    if (XD == "3D" || XD == "23D") {

    }

    switch (approach) {

        case 'dirLth':

            break;

        case 'byNodes':
            var os_x = parseFloat(app_p1[0]), os_y = parseFloat(app_p1[1]), os_z = parseFloat(app_p1[2]);
            var oe_x = parseFloat(app_p2[0]), oe_y = parseFloat(app_p2[1]), oe_z = parseFloat(app_p2[2]);
            var lth_s2e = Math.pow(Math.pow(os_x - oe_x, 2) + Math.pow(os_y - oe_y, 2) + Math.pow(os_z - oe_z, 2), 0.5);
            var v_dx = (oe_x - os_x) / lth_s2e, v_dy = (oe_y - os_y) / lth_s2e, v_dz = (oe_z - os_z) / lth_s2e;
            var shrk_lth = shrk_pct * lth_s2e;
            var ns_x = os_x + v_dx * shrk_lth, ns_y = os_y + v_dy * shrk_lth, ns_z = os_z + v_dz * shrk_lth;
            var ne_x = oe_x - v_dx * shrk_lth, ne_y = oe_y - v_dy * shrk_lth, ne_z = oe_z - v_dz * shrk_lth;
            var app_p1 = [ns_x, ns_y, ns_z], app_p2 = [ne_x, ne_y, ne_z];

            break;

        default:
            break;
    }

    //console.log('drawAISCmember - dbSRC: ' + dbSRC + ', Shape: ' + Shape + ', SizeType: ' + SizeType + ', SizeUnit: ' + SizeUnit + ', Size1: ' + Size1 + ', Size2: ' + Size2 + ', XD: ' + XD + ', approach: ' + approach);
    var shapeData = {};
    if(sectionsInfo && Size2) {
        shapeData = _.find(sectionsInfo, {AISC_Size2: Size2}) || {};
        /*sectionsInfo.forEach(function (awesomeItem) {
            if ((Shape === awesomeItem.Type) && (Size1 === awesomeItem.AISC_Size1) && (Size2 === awesomeItem.AISC_Size2)) {
                shapeData = awesomeItem;
            }
        });*/
    }
    [nodes, nodes_hole, nodes_ctrl, max_dim_lth, zoomscale] = get_nodes_for_aisc_shape(Shape, shapeData, Size2 );

    if(canvas) {
        var zoomscale2D = (canvas.height * 2 / 4) / max_dim_lth;
    }    

    if (XD == "2D" || XD == "23D") {
        drawAISC_2D(Shape, nodes, nodes_hole, nodes_ctrl, zoomscale, zoomscale2D, GridSize2D);
    }

    if (XD == "3D" || XD == "23D") {

        for (var idir = 0; idir < 3; idir++) {
            app_p1[idir] = app_p1[idir] * zoomscale;
            app_p2[idir] = app_p2[idir] * zoomscale;
        }

        mesh = new draw_3D(Shape, approach, app_p1, app_p2, app_p3, nodes, nodes_hole, nodes_ctrl, material, mat_color, mode, shrink_p);

        var newNodes = pulledCloserNodes([app_p1[0], app_p1[1], app_p1[2]], [app_p2[0], app_p2[1], app_p2[2]], shrink_p);

        var V1b = new THREE.Vector3(app_p1[0], app_p1[1], app_p1[2]);
        var V2b = new THREE.Vector3(app_p2[0], app_p2[1], app_p2[2]);
        var V1 = new THREE.Vector3(newNodes.NodeS[0], newNodes.NodeS[1], newNodes.NodeS[2]);
        var V2 = new THREE.Vector3(newNodes.NodeE[0], newNodes.NodeE[1], newNodes.NodeE[2]);

        var dist = distanceVector(V1, V2);

        var Line = new THREE.Line3(new THREE.Vector3(V1.x, (V1.y  + dist), V1.z), V1);
        var Line2 = new THREE.Line3(new THREE.Vector3(app_p2[0], app_p2[1], app_p2[2]), new THREE.Vector3(app_p1[0], app_p1[1], app_p1[2]));

        mesh.traverse(function (child) {
           if (child instanceof THREE.Mesh) {
               var myGeometry = child.geometry;

               for( var i = 0; i< myGeometry.vertices.length; i++  ){
                   myGeometry.vertices[i] = myGeometry.vertices[i].sub( Line.center() );
               }
           }
        });

        mesh.position.copy( Line2.center() );

        mesh.lookAt(V2);
        mesh.rotateX(Math.PI/2);
        mesh.rotateY(-Math.PI/2);
    }

    mesh.Shape = Shape;
    mesh.SizeType = SizeType;
    mesh.SizeUnit = SizeUnit;
    mesh.Size1 = Size1;
    mesh.Size2 = Size2;
    mesh.shapeData = shapeData;
    mesh.nodes = nodes;
    mesh.Mbr_Name = Mbr_Name;

    mesh.zoomscale = zoomscale;

    ray_objects.push(mesh);
    return (mesh);
}

THREE.Geometry.prototype.lookAt = function () {

    var obj = new THREE.Object3D();

    return function lookAt( position, vector ) {

        obj.position.copy(position);

        obj.lookAt( vector );

        obj.updateMatrix();

        this.applyMatrix( obj.matrix );

    };

}();

function draw_3D(Shape, approach, app_p1, app_p2, app_p3, nodes, nodes_hole, nodes_ctrl, material, mat_color, mode, shrink_p) {
    var newNodes = {};

    switch (approach) {
        case 'dirLth':
            var dir = app_p1;
            var length = app_p2;
            var orginAt = app_p3;
            break;

        case 'byNodes':
            var NodeS = app_p1;
            var NodeE = app_p2;
            newNodes.NodeS = app_p1;
            newNodes.NodeE = app_p2;
            break;

        default:
            break;
    }

    var txtOn = false;

    var mesh = new THREE.Mesh();
    var group = new THREE.Mesh();
    var geometry;

    for (var i = 0; i < nodes.length; i++) {
        var ctx_ShapeBdry = new THREE.Shape();
        var path_Hole = new THREE.Path();

        var nodes_part = nodes[i];
        var nodes_part_ctrl = nodes_ctrl[i];
        var nodes_part_hole = nodes_hole[i];

        var crossSection = drawShapeBdry(Shape, nodes_part, nodes_part_hole, nodes_part_ctrl, ctx_ShapeBdry, path_Hole, txtOn);

        if (approach == "byNodes") {
            if(mode == 'wid') {
                newNodes = pulledCloserNodes(NodeS, NodeE, shrink_p);
            }

            geometry = extrudeLinearMember(crossSection, newNodes.NodeS, newNodes.NodeE);
        } else {
            geometry = new THREE.ExtrudeGeometry(crossSection, {
                bevelEnabled: false,
                amount: length
            });
        }

        geometry.lookAt(new THREE.Vector3().fromArray(newNodes.NodeS), new THREE.Vector3( newNodes.NodeS[0], newNodes.NodeS[1] + 1, newNodes.NodeS[2]));
        geometry.verticesNeedUpdate = true;

        mesh = new THREE.Mesh(geometry, setMaterial(material, mat_color));
        mesh.matValue = material;

        group.add(mesh);
    }
    return group;
}


function drawAISC_2D(Shape, nodes, nodes_hole, nodes_ctrl, zoomscale, zoomscale2D, GridSize2D) {
    window.drawAisc = true;

    var step = 1;

    switch (GridSize2D) {
        case '1in':
            step = 1/12;
            break;
        case '3in':
            step = 1/4;
            break;
        case '6in':
            step = 1/2;
            break;
        case '1ft':
            step = 1;
            break;
        default:
            break;
    }

    // zoomscale2D = zoomscale2D;

    var dom = document.getElementById("webgl");
    var element = document.getElementById("2d");
    // var canvas = element.querySelector("canvas");
    //
    // if (canvas == null) {
    //     canvas = document.createElement("canvas");
    // }

    var canvas = document.createElement("canvas");

    var ctx_ShapeBdry = canvas.getContext("2d");
    var path_Hole     = canvas.getContext("2d");

    var doit;

    var globalScale = 1;

    function drawAll() {
        drawGrids();

        var txtOn = false;

        for (var i = 0; i < nodes.length; i++) {
            var nodes_part = nodes[i];
            var nodes_part_ctrl = nodes_ctrl[i];
            var nodes_part_hole = nodes_hole[i];
            ctx_ShapeBdry = drawShapeBdry(Shape, nodes_part, nodes_part_hole, nodes_part_ctrl, ctx_ShapeBdry, path_Hole, txtOn);
        }

        ctx_ShapeBdry.stroke();
        ctx_ShapeBdry.fillStyle = "black";
        ctx_ShapeBdry.fill();

        // drawLabel_2D(Shape, ctx_ShapeBdry, zoomscale2D, nodes);
    }

    function drawGrids() {

        ctx_ShapeBdry.lineWidth = 1 / zoomscale2D;

        ctx_ShapeBdry.strokeStyle = "red";
        ctx_ShapeBdry.beginPath();
        ctx_ShapeBdry.moveTo(-canvas.width / 2, 0);
        ctx_ShapeBdry.lineTo(+canvas.width / 2, 0);
        ctx_ShapeBdry.stroke();

        ctx_ShapeBdry.strokeStyle = "blue";
        ctx_ShapeBdry.beginPath();
        ctx_ShapeBdry.moveTo(0, -canvas.height / 2);
        ctx_ShapeBdry.lineTo(0, +canvas.height / 2);
        ctx_ShapeBdry.stroke();

        ctx_ShapeBdry.lineWidth = 0;
        ctx_ShapeBdry.strokeStyle = "#DCDCDC";

        var Nbr_gridlines = Math.ceil((canvas.width * 2) / (step * zoomscale2D));

        for (var iline = 0; iline < Nbr_gridlines; iline++) {
            ctx_ShapeBdry.beginPath();
            ctx_ShapeBdry.moveTo(-canvas.width / 2, (iline * step) / 1);
            ctx_ShapeBdry.lineTo(+canvas.width / 2, (iline * step) / 1);

            ctx_ShapeBdry.beginPath();
            ctx_ShapeBdry.moveTo((iline * step) / 1, -canvas.height / 2);
            ctx_ShapeBdry.lineTo((iline * step) / 1, +canvas.height / 2);

            if (iline != 0) {
                ctx_ShapeBdry.moveTo(-canvas.width / 2, -(iline * step) / 1);
                ctx_ShapeBdry.lineTo(+canvas.width / 2, -(iline * step) / 1);
                ctx_ShapeBdry.stroke();

                ctx_ShapeBdry.moveTo(-canvas.width / 2, +(iline * step) / 1);
                ctx_ShapeBdry.lineTo(+canvas.width / 2, +(iline * step) / 1);
                ctx_ShapeBdry.stroke();

                ctx_ShapeBdry.moveTo(-(iline * step) / 1, -canvas.height / 2);
                ctx_ShapeBdry.lineTo(-(iline * step) / 1, +canvas.height / 2);
                ctx_ShapeBdry.stroke();

                ctx_ShapeBdry.moveTo(+(iline * step) / 1, -canvas.height / 2);
                ctx_ShapeBdry.lineTo(+(iline * step) / 1, +canvas.height / 2);
                ctx_ShapeBdry.stroke();
            }
        }
    }


    function onMouseWheel(event) {
        event.preventDefault();
        event.stopPropagation();
        var delta = 0;

        if (event.wheelDelta !== undefined) {
            delta = event.wheelDelta;
        } else if (event.detail !== undefined) {
            delta = -event.detail;
        }

        if (delta > 0) {
            if(globalScale < 2) {
                globalScale = globalScale * 1.05;
                wheelResize('in');
            }
        } else if (delta < 0) {
            if(globalScale > 0.2) {
                globalScale = globalScale * 0.95;
                wheelResize('out');
            }
        }
    }

    function wheelResize(dir) {
        ctx_ShapeBdry.clearRect(-canvas.width,-canvas.height,2*canvas.width,2*canvas.height);

        if(dir == 'in') {
            ctx_ShapeBdry.scale(1.05, 1.05);
        } else {
            ctx_ShapeBdry.scale(0.95, 0.95);
        }

        drawAll();
    }

    function onResize(event) {
        canvas.width = dom.width;
        canvas.height = dom.height;

        ctx_ShapeBdry.clearRect(-canvas.width,-canvas.height,2*canvas.width,2*canvas.height);

        ctx_ShapeBdry.font = "18px Times";
        ctx_ShapeBdry.translate(canvas.width / 2, canvas.height / 2);

        ctx_ShapeBdry.scale(1 * zoomscale2D, 1 * zoomscale2D);

        globalScale = 1;

        drawAll();
    }

    canvas.addEventListener('mousewheel', onMouseWheel, false);
    canvas.addEventListener('MozMousePixelScroll', onMouseWheel, false); // firefox

    window.onresize = function() {
        doit = setTimeout(function() {
            onResize();
            clearTimeout(doit);
        }, 10);
    };

    // window.addEventListener('resize', onResize, false);

    while (element.hasChildNodes()) {
        element.removeChild(element.lastChild);
    }

    element.appendChild(canvas);

    onResize();
}

function reset2D() {
    if (window && window.dispatchEvent) {
        window.dispatchEvent(new Event("resize"));
    }
}

function clear2D() {
    var dom = document.getElementById("webgl");
    var element = document.getElementById("2d");
    var canvas = element.querySelector("canvas");

    if (canvas == null) {
        canvas = document.createElement("canvas");
    }

    var ctx_ShapeBdry = canvas.getContext("2d");

    canvas.width = dom.width;
    canvas.height = dom.height;

    ctx_ShapeBdry.font = "18px Times";
    ctx_ShapeBdry.translate(canvas.width / 2, canvas.height / 2);
    ctx_ShapeBdry.scale(0.9, 0.9);

    ctx_ShapeBdry.clearRect(0, 0, canvas.width, canvas.height);
}


function crdtTrft(nodes) {
    for (var inode = 0; inode < nodes.length; inode++) {
        var x_temp = nodes[inode][0], y_temp = nodes[inode][1];
        nodes[inode][0] = +x_temp;
        nodes[inode][1] = -y_temp;
    }
    return nodes;
}

function getPosition(str, m, i) {
   return str.split(m, i).join(m).length;
}

function drawShapeBdry(Shape, nodes, nodes_hole, nodes_ctrl, ctx_ShapeBdry, path_Hole, txtOn) {

    // console.log('inside drawShapeBdry, START: ');
    // console.log('Shape: ' + Shape);
    // console.log('nodes: ');
    // console.log(nodes);
    // console.log('nodes[0]: ');
    // console.log(nodes[0]);

    // console.log('nodes_hole: ');
    // console.log(nodes_hole);

    // console.log('nodes_ctrl: ');
    // console.log(nodes_ctrl);
    // console.log('inside drawShapeBdry, END: ');

    switch (Shape) {
        case 'W':
        case "I":
        case "M":

        case "HP":        
            ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);

/*            for (var inode = 0; inode < nodes.length; inode++) {
                ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
            }*/


            for (var inode = 1; inode < nodes.length; inode++) {
                if(inode==4){
                    var node_ctrl = nodes_ctrl[0];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                     
                }else if(inode==6){
                    var node_ctrl = nodes_ctrl[1];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==12){
                    var node_ctrl = nodes_ctrl[2];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==14){
                    var node_ctrl = nodes_ctrl[3];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else{
                    ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
                }
            }
            ctx_ShapeBdry.closePath();     
            break;

        case 'WT':
        case "MT":
   
            ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);

            for (var inode = 1; inode < nodes.length; inode++) {
                if(inode==2){
                    var node_ctrl = nodes_ctrl[0];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                     
                }else if(inode==8){
                    var node_ctrl = nodes_ctrl[1];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                                      
                }else{
                    ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
                }
            }
            ctx_ShapeBdry.closePath();  
            break;            

        case "S":
            ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);
            for (var inode = 1; inode < nodes.length; inode++) {
                if(inode==2){
                    var node_ctrl = nodes_ctrl[0];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                     
                }else if(inode==4){
                    var node_ctrl = nodes_ctrl[1];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==6){
                    var node_ctrl = nodes_ctrl[2];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==8){
                    var node_ctrl = nodes_ctrl[3];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);    
                }else if(inode==10){
                    var node_ctrl = nodes_ctrl[4];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==12){
                    var node_ctrl = nodes_ctrl[5];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==14){
                    var node_ctrl = nodes_ctrl[6];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]); 
                }else if(inode==16){
                    var node_ctrl = nodes_ctrl[7];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                                                        
                }else{
                    ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
                }
            }
            ctx_ShapeBdry.closePath();

            break;

        case "ST":
            ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);

            for (var inode = 1; inode < nodes.length; inode++) {
                if(inode==2){
                    var node_ctrl = nodes_ctrl[0];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                     
                }else if(inode==4){
                    var node_ctrl = nodes_ctrl[1];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]); 
                }else if(inode==6){
                    var node_ctrl = nodes_ctrl[2];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]); 
                }else if(inode==8){
                    var node_ctrl = nodes_ctrl[3];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                                                                              
                }else{
                    ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
                }
            }
            ctx_ShapeBdry.closePath();      
            break;         
              
        case "L":
        case "L_E":
        case "L_uE":
        case "Single Equal Angle":

        case "2L_SLBB":
        case "2L_LLBB":
        case "2L_E":
            // console.log(nodes);
            //console.log('nodes.length: ' + nodes.length);
            var ipart = 0;

                var txtshift = 5;

                ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);

                var inode = 0;
                var node_ctrl = nodes_ctrl[0];
                ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode + 1][0], nodes[inode + 1][1]);

                if (txtOn == true) {
                    ctx_ShapeBdry.fillText('0', nodes[0][0], nodes[0][1] - txtshift);
                    ctx_ShapeBdry.fillText('1', nodes[1][0], nodes[1][1] - txtshift);
                }

                ctx_ShapeBdry.lineTo(nodes[2][0], nodes[2][1]);
                
                var inode = 2;
                var node_ctrl = nodes_ctrl[1];       
                ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode + 1][0], nodes[inode + 1][1]);

                if (txtOn == true) {
                    ctx_ShapeBdry.fillText('2', nodes[2][0] + txtshift, nodes[2][1]);
                    ctx_ShapeBdry.fillText('3', nodes[3][0], nodes[3][1] - txtshift);
                }

                ctx_ShapeBdry.lineTo(nodes[4][0], nodes[4][1]);

                var inode = 4;
                var node_ctrl = nodes_ctrl[2];
                ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode + 1][0], nodes[inode + 1][1]);

                ctx_ShapeBdry.lineTo(nodes[6][0], nodes[6][1]);
                ctx_ShapeBdry.lineTo(nodes[0][0], nodes[0][1]);
                ctx_ShapeBdry.closePath();

                if (txtOn == true) {
                    ctx_ShapeBdry.fillText('4', nodes[4][0], nodes[4][1] - txtshift);
                    ctx_ShapeBdry.fillText('5', nodes[5][0] + txtshift, nodes[5][1]);

                    ctx_ShapeBdry.fillText('6', nodes[6][0], nodes[6][1] + 2 * txtshift);

                    ctx_ShapeBdry.fillText('7', nodes[7][0], nodes[7][1] + 2 * txtshift);
                    ctx_ShapeBdry.fillText('8', nodes[8][0], nodes[8][1] + 2 * txtshift);
                    ctx_ShapeBdry.fillText('9', nodes[9][0], nodes[9][1] + 2 * txtshift);
                }

            break;
        case 'C':
        case 'MC':
            // ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);
            // for (var inode = 0; inode < nodes.length; inode++) {
            //     ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
            // }
            // break;

            ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);
            for (var inode = 1; inode < nodes.length; inode++) {
                if(inode==2){
                    var node_ctrl = nodes_ctrl[0];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                     
                }else if(inode==4){
                    var node_ctrl = nodes_ctrl[1];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==6){
                    var node_ctrl = nodes_ctrl[2];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==8){
                    var node_ctrl = nodes_ctrl[3];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else{
                    ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
                }
            }
            ctx_ShapeBdry.closePath();
            break;



        case 'HSS_Rect':
        case 'HSS_Sqr':
        case 'HSS(Rect)':
        case 'HSS(Sqr)':        
            // ctx_ShapeBdry.closePath();

            ctx_ShapeBdry.moveTo(nodes[0][0], nodes[0][1]);
            for (var inode = 1; inode < nodes.length; inode++) {
                // ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);

                if(inode==2){
                    var node_ctrl = nodes_ctrl[0];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                     
                }else if(inode==4){
                    var node_ctrl = nodes_ctrl[1];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==6){
                    var node_ctrl = nodes_ctrl[2];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else if(inode==8){
                    var node_ctrl = nodes_ctrl[3];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes[inode][0], nodes[inode][1]);                    
                }else{
                    ctx_ShapeBdry.lineTo(nodes[inode][0], nodes[inode][1]);
                }
            }
            ctx_ShapeBdry.closePath();

            ctx_ShapeBdry.moveTo(nodes_hole[0][0], nodes_hole[0][1]);
            for (var inode = nodes_hole.length-1; inode >= 0; inode--) {
                // ctx_ShapeBdry.lineTo(nodes_hole[inode][0], nodes_hole[inode][1]);

                if(inode==1){
                    var node_ctrl = nodes_ctrl[4];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes_hole[inode][0], nodes_hole[inode][1]);                     
                }else if(inode==3){
                    var node_ctrl = nodes_ctrl[5];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes_hole[inode][0], nodes_hole[inode][1]);                    
                }else if(inode==5){
                    var node_ctrl = nodes_ctrl[6];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes_hole[inode][0], nodes_hole[inode][1]);                    
                }else if(inode==7){
                    var node_ctrl = nodes_ctrl[7];
                    ctx_ShapeBdry.quadraticCurveTo(node_ctrl[0], node_ctrl[1], nodes_hole[inode][0], nodes_hole[inode][1]);                    
                }else{
                    ctx_ShapeBdry.lineTo(nodes_hole[inode][0], nodes_hole[inode][1]);
                }

            }
            ctx_ShapeBdry.closePath();

            break;

        case "PIPE":
        case "pipe":
        case "Pipe":
        case "Pipe(STD)":
        case "Pipe(XS)":
        case "Pipe(XXS)":        
        case "HSS(Rnd)":
        case "HSS_Rnd":        
        case "SR":
            var OD = nodes;
            if (ctx_ShapeBdry instanceof THREE.Shape) {
                ctx_ShapeBdry.absarc(0, 0, OD / 2, 0, 2 * Math.PI, false);
                if (nodes_hole) {
                    var t_nom = nodes_hole || 10;
                    path_Hole.absarc(0, 0, (OD - 2 * t_nom) / 2, 0, Math.PI * 2, true);
                    if (!ctx_ShapeBdry.holes) ctx_ShapeBdry.holes = [];
                    ctx_ShapeBdry.holes.push(path_Hole);
                }
            } else {
                ctx_ShapeBdry.beginPath();
                ctx_ShapeBdry.arc(0, 0, OD / 2, 0, 2 * Math.PI);
                if (nodes_hole) {
                    var t_nom = nodes_hole || 10;
                    path_Hole.arc(0, 0, (OD - 2 * t_nom) / 2, 0, Math.PI * 2, true);
                    if (!ctx_ShapeBdry.holes) ctx_ShapeBdry.holes = [];
                    ctx_ShapeBdry.holes.push(path_Hole);
                }
                ctx_ShapeBdry.closePath();
            }

            break;
    }
    return ctx_ShapeBdry;
}

function drawLabel_2D(Shape, crossSection, zoomscale, nodes) {

    var metric = null;
    var text = '';

    switch (Shape) {
        case "L":
        case "L_E":
        case "L_uE":
        case "Single Equal Angle":

            draw_dim(crossSection, nodes[0][7], nodes[0][0], 't', 2, 10, 30, 10, 20, 0, 'start', 'left');
            draw_dim(crossSection, nodes[0][0], nodes[0][8], 'x', 2, 10, 30, 10, 0, 0, 'middle', 'left');
            draw_dim(crossSection, nodes[0][5], nodes[0][6], 'b', 1, 10, 30, 10, 0, -5, 'middle', 'center');
            draw_dim(crossSection, nodes[0][6], nodes[0][0], 'd', 1, 10, 30, 10, -10, 0, 'middle', 'center');

            draw_dim(crossSection, nodes[0][9], nodes[0][5], 'y', 2, 10, 30, 10, 0, 0, 'middle', 'center');

            break;
        case 'HSS':
        case 'W':
            break;
    }
    return crossSection;
}

function draw_dim(crossSection, node_s, node_e, text, style, extline_offset, extline_lth, dimline_pos_frextlineend, offset_x, offset_y, txtAnchor, txtAlign) {

    crossSection.fillStyle = "black";  // arrow color
    crossSection.strokeStyle = 'black'; // line color

    var xs = node_s[0], zs = node_s[1];
    var xe = node_e[0], ze = node_e[1];

    var delta_x = xe - xs, delta_z = ze - zs;
    var x2 = Math.pow(delta_x, 2), z2 = Math.pow(delta_z, 2);
    var dis_nodes = Math.pow(x2 + z2, 1 / 2);
    var dirx = delta_x / dis_nodes, dirz = delta_z / dis_nodes;

    var rot = Math.PI / 2;
    var dirx_p = +dirx * Math.cos(rot) + dirz * Math.sin(rot);
    var dirz_p = -dirx * Math.sin(rot) + dirz * Math.cos(rot);

    var ext_line_node1_x = xs + dirx_p * extline_offset;
    var ext_line_node1_z = zs + dirz_p * extline_offset;

    var ext_line_node2_x = xs + dirx_p * (extline_offset + extline_lth);
    var ext_line_node2_z = zs + dirz_p * (extline_offset + extline_lth);

    crossSection.beginPath();
    crossSection.moveTo(ext_line_node1_x, ext_line_node1_z);
    crossSection.lineTo(ext_line_node2_x, ext_line_node2_z);
    crossSection.stroke();

// ---
    var ext_line_node3_x = xe + dirx_p * extline_offset;
    var ext_line_node3_z = ze + dirz_p * extline_offset;

    var ext_line_node4_x = xe + dirx_p * (extline_offset + extline_lth);
    var ext_line_node4_z = ze + dirz_p * (extline_offset + extline_lth);

    crossSection.beginPath();
    crossSection.moveTo(ext_line_node3_x, ext_line_node3_z);
    crossSection.lineTo(ext_line_node4_x, ext_line_node4_z);
    crossSection.stroke();

// --- 
    var dim_line_node1_x = xs + dirx_p * (extline_offset + extline_lth - dimline_pos_frextlineend);
    var dim_line_node1_z = zs + dirz_p * (extline_offset + extline_lth - dimline_pos_frextlineend);

    var dim_line_node2_x = xe + dirx_p * (extline_offset + extline_lth - dimline_pos_frextlineend);
    var dim_line_node2_z = ze + dirz_p * (extline_offset + extline_lth - dimline_pos_frextlineend);

    if (style == 1) {
        drawArrow(crossSection, dim_line_node1_x, dim_line_node1_z, dim_line_node2_x, dim_line_node2_z, 1, 3);
    } else if (style == 2) {
        var dim_line_node1_x_offset = dim_line_node1_x + dirx * (-20);
        var dim_line_node1_z_offset = dim_line_node1_z + dirz * (-20);
        drawArrow(crossSection, dim_line_node1_x, dim_line_node1_z, dim_line_node1_x_offset, dim_line_node1_z_offset, 1, 2);

        var dim_line_node2_x_offset = dim_line_node2_x + dirx * 20;
        var dim_line_node2_z_offset = dim_line_node2_z + dirz * 20;
        drawArrow(crossSection, dim_line_node2_x, dim_line_node2_z, dim_line_node2_x_offset, dim_line_node2_z_offset, 1, 2);
    } else {

    }

    text_measure = crossSection.measureText(text);
    switch (txtAnchor) {
        case 'start':
            crossSection.fillText(text, dim_line_node1_x + offset_x, dim_line_node1_z + offset_y);
            break;

        case 'middle':
            crossSection.fillText(text, (dim_line_node1_x + dim_line_node2_x) / 2 + offset_x, (dim_line_node1_z + dim_line_node2_z) / 2 + offset_y);
            break;

        case 'end':
            crossSection.fillText(text, dim_line_node2_x + offset_x, dim_line_node2_z + offset_y);
            break;
    }

    crossSection.textAlign = txtAlign;

}

function pulledCloserNodes(NodeS, NodeE, shrink_p) {

    var newNodeS = [],
        newNodeE = [];

    var shrink = shrink_p || 0.00;

    var dist_x = NodeE[0] - NodeS[0];
    var dist_y = NodeE[1] - NodeS[1];
    var dist_z = NodeE[2] - NodeS[2];

    newNodeS = [NodeS[0] + dist_x*shrink, NodeS[1] + dist_y*shrink, NodeS[2] + dist_z*shrink];
    newNodeE = [NodeE[0] - dist_x*shrink, NodeE[1] - dist_y*shrink, NodeE[2] - dist_z*shrink];

    return {NodeS: newNodeS, NodeE: newNodeE}
}

function distanceVector( v1, v2 ) {
    var dx = v1.x - v2.x;
    var dy = v1.y - v2.y;
    var dz = v1.z - v2.z;

    return Math.sqrt( dx * dx + dy * dy + dz * dz );
}

window.reset2D = reset2D;
window.parent.reset2D = reset2D;
