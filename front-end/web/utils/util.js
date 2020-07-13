var siteroot = 'https://minglu.maikoo.cn';

//添加finally：因为还有一个参数里面还有一个complete方法
Promise.prototype.finally = function(callback) {
  let P = this.constructor;
  return this.then(
    value => P.resolve(callback()).then(() => value),
    reason => P.resolve(callback()).then(() => {
      throw r
    })
  );
};

//封装异步api
const wxPromisify = fn => {
  return function(obj = {}) {
    return new Promise((resolve, reject) => {
      obj.success = function(res) {
        resolve(res)
      }
      obj.fail = function(res) {
        reject(res)
      }
      fn(obj)
    })
  }
}

const locationPromisified = wxPromisify(wx.getLocation); // 获取经纬度
const modalPromisified = wxPromisify(wx.showModal); // 弹窗
const loginPromisified = wxPromisify(wx.login); // 登陆
const settingPromisified = wxPromisify(wx.getSetting); // 获取设置

const logger = wx.getLogManager({
  level: 1
});

// 封装文件上传请求
const fileUpload = (filePath, context, formData = [], timeout = 600) => {
  url = siteroot + '/api/image/upload';
  var token = wx.getStorageSync('token') || "";
  var promise = new Promise((resolve, reject) => {
    // Loading
    showMiniLoading();
    // 请求
    setTimeout(function() {
      wx.uploadFile({
        url: url,
        filePath: filePath,
        name: 'file',
        header: {
          'authentication': token
        },
        formData: formData,
        success: function(res) {
          logger.log(res.data);
          hideMiniLoading();
          var data = JSON.parse(res.data);
          if (res.statusCode == 200 && data.code == 200) {
            resolve(data);
          } else {
            context.setData({
              toastHidden: false,
              toastTitle: "文件上传失败",
              toastAction: "",
              toastCancel: 0
            })
          }
        },
        fail: function(error) {
          hideMiniLoading();
          logger.log(error);
          context.setData({
            toastHidden: false,
            toastTitle: "请求失败，请重试",
            toastAction: "",
            toastCancel: 0
          })
        },
        complete: function() {}
      })
    }, timeout)
  });
  return promise;
}

// 封装post请求
const post = (url, data, context, timeout = 600, loading = true) => {
  url = siteroot + url;
  var token = wx.getStorageSync('token') || "";
  var promise = new Promise((resolve, reject) => {
    //网络请求
    if (loading) {
      showMiniLoading();
    }
    setTimeout(function() {
      wx.request({
        url: url,
        data: data,
        header: {
          'content-type': (url.indexOf('form-id') != -1) ? 'application/json' : 'application/x-www-form-urlencoded', // 默认值
          'authentication': token
        },
        method: 'POST',
        success: function(res) {
          logger.log(res.data);
          hideMiniLoading();
          // 成功返回
          if (res.statusCode == 200 && res.data.code == 200) {
            resolve(res.data);
          } else if (res.statusCode != 200) {
            context.setData({
              toastHidden: false,
              toastTitle: "请求失败，请重试",
              toastAction: "",
              toastCancel: 0
            })
          } else {
            reject(res.data);
          }
        },
        fail: function(error) {
          logger.log(error);
          hideMiniLoading();
          context.setData({
            toastHidden: false,
            toastTitle: "请求失败，请重试",
            toastAction: "",
            toastCancel: 0
          })
        },
        complete: function() {}
      })
    }, timeout)
  });
  return promise;
}

const showMiniLoading = () => {
  wx.showLoading({
    title: 'Loading...',
    mask: true
  })
  wx.showNavigationBarLoading();
}

const hideMiniLoading = () => {
  wx.hideLoading();
  wx.hideNavigationBarLoading();
  wx.stopPullDownRefresh();
}

module.exports = {
  post,
  modalPromisified,
  loginPromisified,
  settingPromisified,
  fileUpload,
  logger
}