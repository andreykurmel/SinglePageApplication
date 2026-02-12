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

//global PNG export
window.export_to_png = function(elem, name, extra) {
    window.dom_as_png(elem, name, extra, window.download_png);
};
window.download_png = function (elem, name, uri) {
    uri = uri.replace(/^data:image\/[^;]/, 'data:application/octet-stream');

    let exp_name = name.indexOf('.png') > -1
        ? name
        : name + ' ' + moment().format('YYYY-MM-DD HH_mm_ss') + '.png';

    let link = document.createElement('a');
    if (typeof link.download === 'string') {
        document.body.appendChild(link); // Firefox requires the link to be in the body
        link.download = exp_name;
        link.href = uri;
        link.click();
        document.body.removeChild(link); // remove the link when done
    } else {
        location.replace(uri);
    }
};
window.dom_as_png = function(elem, name, extra, thenFunction) {
    extra = extra || {};
    if (elem) {
        if (!extra.noscale) {
            elem.style.transform = 'scale(2)';
        }
        if (!extra.nooverlay) {
            $.LoadingOverlay('show');
        }
        $(elem).find('._no_png').hide();
        html2canvas(elem).then((canvas) => {
            elem.style.transform = 'initial';
            if (!extra.nooverlay) {
                $.LoadingOverlay('hide');
            }
            $(elem).find('._no_png').show();

            thenFunction(elem, name, canvas.toDataURL("image/png"));
        });
    } else {
        Swal('Info','Table not found!');
    }
};
//global PDF export
window.export_to_pdf = function(elem, name) {
    if (elem) {
        $.LoadingOverlay('show');
        html2canvas(elem).then((canvas) => {
            let imgData = canvas.toDataURL("image/jpeg", 1.0);
            let pdf = new jsPDF("p", "mm", "a4");

            let pdfWi = pdf.internal.pageSize.getWidth();
            let pdfHe = pdf.internal.pageSize.getHeight();
            let wRatio = elem.clientWidth / pdfWi;
            let hRatio = elem.clientHeight / pdfHe;
            let wi = wRatio > hRatio ? pdfWi :(elem.clientWidth / hRatio) ;
            let he = wRatio > hRatio ? (elem.clientHeight / wRatio) : pdfHe;
            pdf.addImage(imgData, 'JPEG', 0, 0, wi, he);

            let exp_name = name.indexOf('.pdf') > -1
                ? name
                : name + ' ' + moment().format('YYYY-MM-DD HH_mm_ss') + '.pdf';
            pdf.save(exp_name);

            $.LoadingOverlay('hide');
        });
    } else {
        Swal('Info','Html not found!');
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
window.to_standard_val = function (val, as_string) {
    if (!isNumber(val)) {
        switch (val) {
            case null: return ''; break;
            case undefined: return ''; break;
            default: return String(val); break;
        }
    } else {
        return as_string ? String(window.to_float(val)) : window.to_float(val);
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

//-----------------
//FORMULA FUNCTIONS

window.Isempty = function ($field, $val1 = null, $val2 = null)
{
    return !fnVal($field)
        ? ($val1 === null ? true : $val1)
        : ($val2 === null ? false : $val2);
};

window.Sqrt = function ($number)
{
    return Math.sqrt($number);
};

window.Pow = function ($number, $pow)
{
    return Math.pow($number, $pow);
};

window.Year = function ($date)
{
    return fnDate($date).format('YYYY');
};

window.Month = function ($date)
{
    return fnDate($date).format('MM');
};

window.Week = function ($date)
{
    return fnDate($date).format('w');
};

window.WkDay = function ($date, $argument)
{
    return fnDate($date).format( String($argument).toLowerCase() === 'name' ? 'dddd' : 'd' );
};

window.MoDay = function ($date)
{
    return fnDate($date).format('D');
};

window.YrDay = function ($date)
{
    return fnDate($date).format('DDD');
};

window.Today = function ($tz, $format)
{
    return moment().format($format || 'YYYY-MM-DD');
};

window.If = function ($condition, $if_true, $if_false = null)
{
    //Calc condition
    let $avail_operators = ['==', '!=', '<=', '>=', '<>', '<', '>'];
    _.each($avail_operators, ($operator) => {
        if (String($condition).indexOf($operator) > -1) {
            let $arr = String($condition).split($operator);
            _.each($arr, ($val, $i) => {

                switch ($operator) {
                    case '<>':
                    case '!=':
                        $condition = to_float($arr[0]) !== to_float($arr[1]);
                        break;
                    case '<=':
                        $condition = to_float($arr[0]) <= to_float($arr[1]);
                        break;
                    case '>=':
                        $condition = to_float($arr[0]) >= to_float($arr[1]);
                        break;
                    case '==':
                        $condition = to_float($arr[0]) === to_float($arr[1]);
                        break;
                    case '<':
                        $condition = to_float($arr[0]) < to_float($arr[1]);
                        break;
                    case '>':
                        $condition = to_float($arr[0]) > to_float($arr[1]);
                        break;
                }
            });
        }
    });

    return !!fnVal($condition) ? $if_true : $if_false;
};

window.Switch = function ($condition, $cases = [], $results = [], $default = '')
{
    let $key = array_keys($cases, $condition)[0] || null;
    let $var = $results[$key] || '';
    if ($var === '' && String($default).length) {
        $var = $default;
    }
    if ($var === '' && ($results.length) > ($cases.length)) {
        $var = _.last($results);
    }
    return $var;
}

window.ANDX = function ($conditions = [])
{
    let $result = true;
    _.each($conditions, ($el) => {
        $result = $result && !!fnVal($el);
    });
    return $result;
}

window.ORX = function ($conditions = [])
{
    let $result = true;
    _.each($conditions, ($el) => {
        $result = $result || !!fnVal($el);
    });
    return $result;
}

window.ASUM = function ($array = [])
{
    return _.reduce($array, (res, e) => { return res + to_float(e); }, 0);
}

window.AMIN = function ($array = [])
{
    let $res = _.first($array);
    _.each($array, (el) => {
        if (to_float(el) < $res) $res = to_float(el);
    });
    return $res;
}

window.AMAX = function ($array = [])
{
    let $res = _.first($array);
    _.each($array, (el) => {
        if (to_float(el) > $res) $res = to_float(el);
    });
    return $res;
}

window.AAVG = function ($array = [])
{
    return ASUM($array) / $array.length;
}

window.AMEAN = function ($array = [])
{
    return ( AMIN($array) + AMAX($array) ) / 2;
}

window.AVAR = function ($array = [])
{
    let $avg = AAVG($array);
    let $res = 0;
    _.each($array, (el) => {
        $res += Math.pow($avg - to_float(el), 2);
    });
    return $res / $array.length;
}

window.ASTD = function ($array = [])
{
    return Math.sqrt(AVAR($array));
}

window.DDLOption = function ($opt)
{
    return fnVal($opt);
}

window.Duration = function ($timefrom, $timeto)
{
    $timefrom = fnDate($timefrom);
    $timeto = fnDate($timeto);
    return $timefrom && $timeto
        ? $timefrom.diff($timeto)
        : null;
}

window.TimeChange = function ($date, $type, $duration)
{
    $date = fnDate($date);
    if (!$date) {
        return '';
    }

    if (String($type || 'substract').toLowerCase() === 'substract') {
        return $date.subtract(parseInt($duration || 0), 'seconds').format($date._f);
    } else {
        return $date.add(parseInt($duration || 0), 'seconds').format($date._f);
    }
}

//Protected helpers
window.fnVal = function ($value)
{
    //remove '0000-00-00'
    if ($value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
        $value = '';
    }
    return $value;
}

window.fnDate = function ($date = null)
{
    try {
        if (!$date) {
            return '';
        }
        return moment($date);
    } catch ($e) {
        return '';
    }
}

window.unicodeToChar = function(text) {
    return text.replace(/\\u[\dA-F]{4}/gi,
        function (match) {
            return String.fromCharCode(parseInt(match.replace(/\\u/g, ''), 16));
        });
}

window.br2nl = function (text) {
    return String(text).replaceAll(/<br[\/]?>/gi, '\n');
}

window.nl2br = function (text) {
    return String(text).replaceAll('\n', '<br>');
}

window.newRegexp = function (regexp) {
    regexp = regexp.replace('\\p{L}', '\\u00A0-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF\\w');
    return new RegExp(regexp, 'gi');
}

window.ucFirst = function (str) {
    if (!str) return str; // Handle empty or null strings
    return str.charAt(0).toUpperCase() + str.slice(1);
}