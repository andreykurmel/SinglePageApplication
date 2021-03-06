//global function to get errors message
window.getErrors = function(errors) {
    let msg = '';
    if (errors) {
        if (errors.response && errors.response.data) {
            //if session in browser is incorrect -> logout
            if (errors.response.status == 419) {
                //window.location = '/logout';
            }

            if (errors.response.data.msg) {
                msg = errors.response.data.msg;
            }
            else
            if (typeof errors.response.data === 'string') {
                msg = errors.response.data;
            }
            else {
                msg = Object.values(errors.response.data).join('<br>');
            }
        }
        else if (errors.message) {
            msg = errors.message;
        }
        else if (errors.statusText) {
            msg = errors.statusText;
        }
        else {
            msg = errors;
        }
    }
    return (typeof msg === 'string' ? msg : '');
};

//global in_array
window.in_array = function (key, array) {
    return array.indexOf(key) > -1;
};

//global png export
window.export_to_png = function(elem, name) {
    if (elem) {
        elem.style.transform = 'scale(2)';
        $.LoadingOverlay('show');
        $(elem).find('._no_png').hide();
        html2canvas(elem).then((canvas) => {
            let saveAs = function(uri, filename) {
                let link = document.createElement('a');
                if (typeof link.download === 'string') {
                    document.body.appendChild(link); // Firefox requires the link to be in the body
                    link.download = filename;
                    link.href = uri;
                    link.click();
                    document.body.removeChild(link); // remove the link when done
                } else {
                    location.replace(uri);
                }
                $.LoadingOverlay('hide');
                $(elem).find('._no_png').show();
            };

            let img = canvas.toDataURL("image/png"),
                uri = img.replace(/^data:image\/[^;]/, 'data:application/octet-stream');

            let exp_name = name.indexOf('.png') > -1
                ? name
                : name + ' ' + moment().format('YYYY-MM-DD HH_mm_ss') + '.png';
            saveAs(uri, exp_name);
            elem.style.transform = 'initial';
        });
    } else {
        Swal('Table not found');
    }
};

//global to float
window.to_float = function (val) {
    let res = parseFloat(val);
    return isNaN(res) ? 0 : res;
};

window.isNull = function (val) {
    return val === null;
};

window.isNumber = function (val) {
    return val !== '' && !isNaN(val) && !isNull(val);
};

window.isValue = function (val) {
    return val !== null && val !== undefined && val !== '';
};

//global to standard val
window.to_standard_val = function (val) {
    if (!isNumber(val)) {
        switch (val) {
            case null: return ''; break;
            case undefined: return ''; break;
            default: return String(val); break;
        }
    } else {
        return window.to_float(val);
    }
};

//Promise timeout
window.sleep = async function (wait, fn, ...args) {
    await new Promise(resolve => setTimeout(resolve, wait || 1000));
    return fn(...args);
};

//Base64 to File class
window.dataURLtoFile = function(dataurl, filename) {
    let arr = dataurl.split(','),
        mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]),
        n = bstr.length,
        u8arr = new Uint8Array(n);
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new File([u8arr], filename, {type:mime});
};

//Local Storage sometimes can make errors
window.readLocalStorage = function (key) {
    try {
        return localStorage.getItem(key)
    } catch (e) {
        return null;
    }
};
window.setLocalStorage = function (key, val) {
    try {
        return localStorage.setItem(key, val)
    } catch (e) {
        return null;
    }
};

//simple hash
window.justHash = function(str) {
    let hash = 0, i, chr;
    if (str.length === 0) return '';
    for (i = 0; i < str.length; i++) {
        chr   = str.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
    }
    return hash;
};