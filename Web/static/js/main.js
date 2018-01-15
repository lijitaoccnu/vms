/**
 * 管理后台JS
 */

$(document).ready(function () {
    $(document).click(function () {
        $('.dropdown-menu').hide();
    });
    window.onpopstate = function () {
        initPage();
    };
    initNavigation();
    initDom($('body'));
    initPage();
});

// 初始化导航
function initNavigation() {
    $('.nav-menu-1>li').click(function () {
        var $li = $(this);
        var $opened = $li.siblings('.nav-menu-open');
        $opened.removeClass('nav-menu-open').find('ul').slideUp('fast');
        if ($li.find('.nav-menu-2').length) {
            $li.find('.nav-menu-2').slideToggle('fast');
            if ($li.hasClass('nav-menu-open')) {
                $li.removeClass('nav-menu-open');
            } else {
                $li.addClass('nav-menu-open');
            }
        }
    });
    $('.nav-menu-2>li').click(function (e) {
        e.stopPropagation();
    });
    $('.nav-body li').click(function () {
        var uri = $(this).attr('data-uri');
        if (uri) loadPage(uri);
    });
}

// 激活菜单
// 根据当前显示页面uri匹配
function navActivate(uri) {
    var _uri = uri.split('?')[0];
    var $menu_li_2 = $('.nav-body li[data-uri="' + _uri + '"]');
    var $menu_li_1 = $menu_li_2.parents('.nav-menu-1 li');
    if (!$menu_li_2.hasClass('nav-menu-active')) {
        $('.nav-menu-active').removeClass('nav-menu-active');
        $menu_li_2.addClass('nav-menu-active');
    }
    if (!$menu_li_1.hasClass('nav-menu-open')) {
        $('.nav-menu-open').find('.nav-menu-2').hide();
        $('.nav-menu-open').removeClass('nav-menu-open');
        $menu_li_1.addClass('nav-menu-open');
        $menu_li_2.parents('.nav-menu-2').slideDown('fast');
    }
}

// 页面内容初始化
function initPage() {
    var hash = location.hash || '#/project/index';
    var uri = hash.substr(1);
    if (CURRENT_URI !== uri) {
        loadPage(uri);
    }
}

// 页面元素初始化
function initDom($root) {
    $root.find('select').each(function (i, el) {
        if ($(el).attr('data-value')) {
            $(el).val($(el).attr('data-value'));
            $(el).attr('data-value', '');
        }
    });
    $root.find('.dropdown').click(function (e) {
        $(this).find('.dropdown-menu').toggle();
        e.stopPropagation();
    });
    $root.find('.dropdown-menu *').click(function () {
        $(this).parents('.dropdown-menu').hide();
        return false;
    });
    $root.find('a').click(function () {
        var url = $(this).attr('href');
        if (!url || url.indexOf('javascript') === 0) {
            return false;
        }
        loadPage(url);
        return false;
    });
    $root.find('table[data-editable="true"]').each(function (i, table) {
        require(JS_URL + '/editable-table.js', function () {
            EditableTable.init($(table));
        });
    });
    $root.find('table').find('.btn-delete').each(function (i, btn) {
        var $btn = $(btn);
        $btn.click(function () {
            var id = $btn.parents('tr').attr('data-id');
            var url = $btn.parents('table').attr('data-delete-url');
            if (!id || !url) return;
            showConfirm('确定删除该数据吗？删除后关联数据也会同步删除。', function () {
                ajaxRequest(url, {id: id}, function () {
                    $btn.parents('tr').remove();
                });
            });
        });
    });
    $root.find('.search-box').find('form').find('.btn[type=submit]').click(function () {
        var $search_box = $(this).parents('.search-box');
        var params = $search_box.find('form').serialize();
        var url = CURRENT_URI;
        if (url.indexOf('?') > 0) {
            var urls = url.split('?');
            params = query_parse(urls[1] + '&' + params);
            delete params['t'];
            delete params['page'];
            delete params['limit'];
            url = urls[0] + '?' + http_build_query(params);
        } else {
            url += '?' + params;
        }
        loadPage(url);
    });
    $root.find('.btn-clear').click(function () {
        $(this).parents('form').find('input,select').val('');
    });
    $root.find('.btn[data-toggle=dialog]').click(function () {
        var $btn = $(this);
        var title = $btn.attr('data-title') || $btn.text();
        var target = $btn.attr('data-target');
        if (!target) return;
        if (target.substr(0, 1) === '#') {
            showDialog(title, $(target).clone());
        } else {
            showDialog(title, null, {url: urlCheck(target)});
        }
    });
    $root.find('input').click(function (e) {
        e.stopPropagation();
    });
    $root.find('.radio,.checkbox').click(function () {
        $(this).find('input').get(0).click();
    });
    $root.find('input,textarea,select').blur(function () {
        inputValidate($(this))
    });
    if ($root.find('.uploader').length) {
        require(JS_URL + '/uploader.js', function () {
            $root.find('.uploader').each(function () {
                $(this).uploader();
            });
        });
    }
}

// 加载页面
function loadPage(uri, params) {
    clearTimers();
    navActivate(uri);
    $('.dialog,.dialog-mask').remove();
    var $container = $('#body');
    var onLoad = function (html) {
        var r = json_decode(html);
        if (r) html = r.message;
        $container.html(html);
        initDom($container);
    };
    var onError = function (code, error) {
        $container.html(code + ' ' + error);
    };
    ajaxRequest(uri, params, onLoad, onError, 'GET', 'html');
    CURRENT_URI = uri;
    location.hash = '#' + uri;
}

// 退出登录
function logout() {
    ajaxRequest('/account/logout', {}, function () {
        location.href = urlCheck('/account/login');
    });
}

// 显示确认框
function showConfirm(message, onConfirm, onCancel) {
    var content = '<div style="padding: 20px;"><p>' + message + '</p></div>';
    showDialog('提示', content, {isConfirm: true, onSuccess: onConfirm, onCancel: onCancel});
}

// 显示对话框（模态）
function showDialog(title, content, options) {
    options = options || {};
    var $mask = $('<div class="dialog-mask"></div>');
    var dialog = '<div class="dialog">';
    dialog += '<div class="dialog-header">' + title + '<a class="btn btn-close">×</a></div>';
    dialog += '<div class="dialog-body"></div>';
    dialog += '</div>';
    var $dialog = $(dialog);
    if (content) {
        var $content = $(content);
        $content.removeAttr('id').css({display: 'block'});
        $dialog.find('.dialog-body').append($content);
    }
    $dialog.css({'top': (50 + $('.dialog').length * 40) + 'px'});
    $dialog.appendTo($mask);
    $mask.appendTo($('body'));
    var addFooter = function () {
        if ($dialog.find('form').length || options['isConfirm']) {
            var confirmText = options['isConfirm'] ? '确 定' : (options['confirmText'] || '保 存');
            var $footer = $('<div class="dialog-footer"></div>');
            $footer.append('<a class="btn btn-cancel">取 消</a>');
            $footer.append('<a class="btn btn-primary" type="submit">' + confirmText + '</a>');
            $footer.appendTo($dialog);
            $footer.find('.btn[type=submit]').click(function () {
                if (options['isConfirm']) {
                    removeDialog($dialog);
                    return invokeFunc(options['onSuccess']);
                }
                var $form = $dialog.find('form');
                ajaxSubmit($form, function () {
                    removeDialog($dialog);
                    invokeFunc(options['onSuccess']);
                }, options['onError']);
            });
            $dialog.find('.btn-cancel').click(function () {
                removeDialog($dialog);
                invokeFunc(options['onCancel']);
            });
        }
    };
    $dialog.find('.btn-close').click(function () {
        removeDialog($dialog);
        invokeFunc(options['onCancel']);
    });
    if (!content && options['url']) {
        var url = urlCheck(options['url']);
        $dialog.find('.dialog-body').load(url, function () {
            initDom($dialog);
            addFooter();
        });
    } else {
        initDom($dialog);
        addFooter();
    }
    return $dialog;
}

// 移除对话框
function removeDialog($dialog) {
    $dialog.parent().remove();
}

// 显示提示框
function showAlert(msg, style) {
    style = style || 'success';
    $('.alert').remove();
    var $tips = $('<div class="alert alert-' + style + '">' + msg + '</div>');
    $tips.appendTo($('body'));
    $tips.animate({top: 0}, 1000);
    $tips.fadeOut(1000);
}

// 页面重定向
function redirect(url) {
    loadPage(url);
}

// setTimeout加强版
function _setTimeout(func, delay) {
    _setInterval(func, delay, 1);
}

// setInterval加强版
function _setInterval(func, delay, limit) {
    limit = Math.max(0, parseInt(limit || 0));
    window._timers = window._timers || {};
    var timer = setInterval(function () {
        if (!window._timers[timer]) return;
        window._timers[timer]['times']++;
        var limit = window._timers[timer]['limit'];
        if (limit === 0) return;
        if (window._timers[timer]['times'] === limit) {
            delete window._timers[timer];
            clearInterval(timer);
        }
        invokeFunc(func);
    }, delay);
    window._timers[timer] = {limit: limit, times: 0};
}

// 清除所有定时器
function clearTimers() {
    if (!window._timers) return;
    for (var timer in window._timers) {
        clearInterval(parseInt(timer));
    }
    window._timers = null;
}

// 清除单个定时器
function clearTimer(timer) {
    if (!window._timers) return;
    delete window._timers[timer];
    clearInterval(timer);
}