/**
 * 通用JS代码
 */

if (!Object.keys) {
    Object.keys = function (obj) {
        var result = [];
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                result.push(key);
            }
        }
        return result;
    };
}

function require(file, onLoad) {
    window._required = window._required || {};
    if (window._required[file]) {
        return invokeFunc(onLoad);
    }
    file = urlCheck(file);
    $.getScript(file, function () {
        window._required[file] = true;
        invokeFunc(onLoad);
    });
}

function int(value) {
    return parseInt(value || 0);
}

function json_decode(str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return null;
    }
}

function date(format, time) {
    var date = new Date();
    if (time > 0) date.setTime(time);
    if (time < 0) date.setTime(date.getTime() + time);
    var Y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    var H = date.getHours();
    var i = date.getMinutes();
    var s = date.getSeconds();
    var replaces = {'Y': Y, 'm': m, 'd': d, 'H': H, 'i': i, 's': s};
    format = format || 'Y-m-d H:i:s';
    for (var k in replaces) {
        if (replaces[k] < 10) replaces[k] = '0' + replaces[k];
        format = format.replace(new RegExp(k, 'g'), replaces[k]);
    }
    return format;
}

/**
 * 解析配置选项
 * 格式：类json字符串，可不加首尾花括号，单双引号均支持
 * @param target
 * @returns {{}}
 */
function parseOptions(target) {
    var t = $(target);
    var options = {};
    var s = $.trim(t.attr('data-options'));
    if (!s) return options;
    if (s.substring(0, 1) !== '{') {
        s = '{' + s + '}';
    }
    try {
        options = (new Function('return ' + s))();
    } catch (e) {
        console.log('格式错误: ' + s);
    }
    return options;
}

function query_parse(str) {
    var query = {};
    var ss = str.split('&');
    var sss;
    for (var i = 0; i < ss.length; i++) {
        if (ss[i] === '') continue;
        if (ss[i].indexOf('=') <= 0) continue;
        sss = ss[i].split('=');
        query[sss[0]] = sss[1];
    }
    return query;
}

function http_build_query(data) {
    var query = [];
    for (var key in data) {
        if (data.hasOwnProperty(key) && data[key]) {
            if (data[key] !== '' && data[key] !== null) {
                query.push(key + '=' + encodeURIComponent(data[key]));
            }
        }
    }
    return query.join('&');
}

//补全、校正url
function urlCheck(url) {
    if (url.indexOf('http://') === 0) return url;
    var _baseUrl = (typeof(BASE_URL) === 'undefined' || !BASE_URL) ? ('http://' + location.hostname) : BASE_URL;
    _baseUrl = _baseUrl.substr(-1) === '/' ? _baseUrl.substr(0, _baseUrl.length - 1) : _baseUrl;//去除末尾斜杠
    url = url.substr(0, 1) === '/' ? url : ('/' + url);//开头加斜杠
    return _baseUrl + url;
}

// 表单验证
function formValidate($form) {
    var valid = true;
    $form.find('input,textarea,select').each(function () {
        if (!inputValidate($(this))) valid = false;
    });
    return valid;
}

//以ajax方式异步提交表单
function ajaxSubmit(form, onSuccess, onError) {
    if (typeof(form) === 'function') {
        onError = onSuccess;
        onSuccess = form;
        form = null;
    }
    var $form = form || $("form");
    if (!formValidate($form)) return alertError('请检查输入内容');
    var action = $form.attr('action') || '/';
    var method = ($form.attr('method') || 'get').toUpperCase();
    var data = $form.serialize();
    ajaxRequest(action, data, onSuccess, onError, method);
}

//发起ajax请求
function ajaxRequest(url, data, onSuccess, onError, method, format) {
    if (typeof data === 'object') {
        for (var k in data) {
            if (data.hasOwnProperty(k) && data[k] === null) {
                data[k] = '';
            }
        }
    }
    url = urlCheck(url);
    method = method || 'POST';
    format = format || 'json';
    $.ajax({
        url: url,
        data: data,
        type: method.toUpperCase(),
        dataType: format,
        success: function (r) {
            invokeFunc(ajaxCallback, r, onSuccess, onError);
        },
        error: function (xhr) {
            invokeFunc(onError, xhr.status, xhr.statusText);
        }
    });
}

//完成ajax请求后的回调
function ajaxCallback(r, onSuccess, onError) {
    if (!r) {
        var errMsg = '请求没有返回数据';
        alertError(errMsg);
        invokeFunc(onError, -9999, errMsg);
        return;
    }
    if (typeof(r) === 'string' || typeof(r.code) === 'undefined') {
        r = {code: 0, message: '', data: r};
    }
    if (r.code) {
        if (onError) {
            invokeFunc(onError, r.code, r.message);
        } else {
            alertError(r.message);
        }
        return;
    }
    var msg = r.message || (r.data && r.data.message);
    if (msg) {
        alertSuccess(msg);
    }
    if (onSuccess) {
        invokeFunc(onSuccess, r.data);
    }
    if (r.data && r.data.reload && !r.data.redirect) {
        r.data.redirect = CURRENT_URI || location.href;
    }
    if (r.data && r.data.redirect) {
        redirect(r.data.redirect);
    }
}

//call_user_func
function invokeFunc(func) {
    if (!!func && typeof func === 'function') {
        func.apply(null, Array.prototype.slice.call(arguments, 1));
    }
}

//页面跳转
window.redirect = window.redirect || function (href) {
        //$('input[type=password]').val('').prop('type', 'text');
        //$('input[type=password]').val('');
        location.href = urlCheck(href);
    };

//显示提示框
window.showAlert = window.showAlert || function (msg) {
        alert(msg);
    };


//成功提示
function alertSuccess(msg) {
    showAlert(msg, 'success');
}

//错误提示
function alertError(msg) {
    showAlert(msg, 'error');
}

// 输入框验证
function inputValidate($input) {
    if ($input.attr('type') === 'file') return true;
    var value = $.trim($input.val());
    $input.val(value);
    var type = $input.attr('data-type') || 'text';
    var required = $input.prop('required');
    var minLength = parseInt($input.attr('minlength') || 0);
    if (minLength > 0) required = true;
    var regulars = {
        email: /^\w+(\.\w+)*@\w+(\.\w+)*\.[a-z]{2,3}$/,
        mobile: /^(1[3589][0-9])\d{8}$/,
        number: /^[+-]?(0|([1-9]\d*))(\.\d+)?$/,
        integer: /^[+-]?(0|([1-9]\d*))$/,
        chinese: /^[\u4e00-\u9fa5]+$/
    };
    var format = eval($input.attr('data-format')) || regulars[type];
    var valid = true;
    if (valid && required && value === '') valid = false;
    if (valid && minLength > 0 && value.length < minLength) valid = false;
    if (valid && format && value !== '' && !format.test(value)) valid = false;
    valid ? $input.removeClass('error') : $input.addClass('error');
    return valid;
}