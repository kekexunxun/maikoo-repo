var siteroot = "http://cjkg.up.maikoo.cn";
var webroot = "http://admin.supermc.vip";
var token = sessionStorage.getItem('token');

// Token 校验 用于datatables渲染时校验
function checkToken() {
    if (!token) {
        layer.alert('请先登录', { icon: 7, title: '系统提示' }, function (index) {
            sessionStorage.clear();
            top.location.href = webroot + "/login.html";
        });
    }
}

function request(url, data, timeout = 600, showLoading = true) {
    // 传入的data为array 或 json 这里需要再将token传入
    if (!(data.assetSetting || data.username)) {
        data.accessToken = token;
    }
    // 如果没有token 并且当前请求界面为登陆时 请求直接返回
    if (!token && !data.username) {
        return;
    }
    // 构造promise对象并返回
    return new Promise(function (resolve, reject) {
        // loading
        if (showLoading) {
            var loading = layer.load(1, {
                shade: [0.6, '#f2f2f2']
            });
        }
        setTimeout(function () {
            $.ajax({
                url: siteroot + url,
                type: 'post',
                dataType: 'json',
                contentType: data.assetSetting ? 'application/json' : 'application/x-www-form-urlencoded',
                data: data.assetSetting ? data.assetSetting : data,
                traditional: true,
                success: function (res) {
                    console.log(res);
                    if (res.code == 200) {
                        // 成功返回
                        resolve(res);
                    } else if (res.code == 1001) {
                        layer.alert('登陆已过期，请重新登录', { icon: 7, title: '系统提示' }, function (index) {
                            sessionStorage.clear();
                            top.location.href = webroot + "/login.html"
                        });
                    } else {
                        layer.msg(res.msg, { icon: 2, time: 1300 });
                    }
                },
                error: function (error) {
                    layer.close(loading);
                    // 请求错误
                    layer.msg('网络错误', { icon: 2, time: 1300 });
                },
                complete: function () {
                    // 停止loading框
                    if (showLoading) {
                        layer.close(loading);
                    }
                }
            });
        }, timeout);
    });
}

function upload(data, timeout = 600) {
    // 拼接url
    url = "/api/image/upload";
    // 如果没有token 当前请求将不处理
    if (!token) {
        return;
    }
    // 传入的data为array这里需要再将token传入
    data.append('accessToken', token);
    // 构造promise对象并返回
    return new Promise(function (resolve, reject) {
        // loading
        var loading = layer.load(1, {
            shade: [0.6, '#f2f2f2']
        });
        setTimeout(function () {
            $.ajax({
                url: siteroot + url,
                type: 'post',
                data: data,
                contentType: false,
                processData: false,
                success: function (res) {
                    console.log(res.code);
                    if (res.code == 200) {
                        // 成功返回
                        resolve(res);
                    } else {
                        reject(res);
                    }
                },
                error: function (error) {
                    // 失败返回
                    reject(error)
                },
                complete: function () {
                    // 停止loading框
                    layer.close(loading);
                }
            });
        }, timeout);
    });
}

function isJSON(str) {
    if (typeof str == 'string') {
        try {
            var obj = JSON.parse(str);
            if (typeof obj == 'object' && obj) {
                return true;
            } else {
                return false;
            }

        } catch (e) {
            console.log('error：' + str + '!!!' + e);
            return false;
        }
    }
    console.log('It is not a string!')
}

// 备注输入时的字数统计
function numCheck(obj) {
    $('.textarea-length-two').text($(obj).val().length);
}

function refreshPage(timeOut = 1300) {
    setTimeout(function () {
        location.replace(location.href);
    }, timeOut);
}