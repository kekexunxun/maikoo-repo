// 定义全局URL
var siteroot = "http://cjkg.up.maikoo.cn";
var accessToken = YDUI.util.sessionStorage.get('token');
// AJAX请求封装
function request(url, data, timeout = 600, showLoading = true) {
    if (!accessToken && !data.username) {
        YDUI.dialog.loading.close();
        return;
    }
    // 传入的data为array这里需要再将token传入
    if (!data.accessToken) {
        data.accessToken = accessToken;
    }
    // 构造promise对象并返回
    return new Promise(function (resolve, reject) {
        // loading
        if (showLoading) {
            YDUI.dialog.loading.open('Loading');
        }
        setTimeout(function () {
            $.ajax({
                url: siteroot + url,
                type: 'post',
                data: data,
                success: function (res) {
                    YDUI.dialog.loading.close();
                    console.log(res);
                    if (res.code == 200) {
                        // 成功返回
                        resolve(res);
                    } else if (res.code == 1001) {
                        YDUI.dialog.alert('登陆已过期，请重新登录', function () {
                            YDUI.util.sessionStorage.clear();
                            location.href = "http://www.supermc.vip" + "/login.html"
                        });
                    } else {
                        YDUI.dialog.toast(res.msg || res.toString(), 'error', 1300);
                    }
                },
                error: function (error) {
                    // 错误提示
                    YDUI.dialog.loading.close();
                    YDUI.dialog.toast(error.toString(), 'error', 1300);
                },
                complete: function () {
                    // 停止loading框
                    if (showLoading) {
                        YDUI.dialog.loading.close();
                    }
                }
            });
        }, timeout);
    });
}

function upload(data, timeout = 600) {
    if (!accessToken) {
        YDUI.dialog.loading.close();
        return;
    }
    // 拼接url
    url = "/api/image/upload";
    // 传入的data为array这里需要再将token传入
    data.append('accessToken', accessToken);
    // 构造promise对象并返回
    return new Promise(function (resolve, reject) {
        // loading
        YDUI.dialog.loading.open('上传中');
        setTimeout(function () {
            $.ajax({
                url: siteroot + url,
                type: 'post',
                data: data,
                contentType: false,
                processData: false,
                success: function (res) {
                    // console.log(res);
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
                    YDUI.dialog.loading.close();
                }
            });
        }, timeout);
    });
}