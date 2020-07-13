var siteroot = 'https://xnps.up.maikoo.cn/mini/';

//添加finally：因为还有一个参数里面还有一个complete方法
Promise.prototype.finally = function(callback) {
  let P = this.constructor;
  return this.then(
    value => P.resolve(callback()).then(() => value),
    reason => P.resolve(callback()).then(() => {
      throw reason
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

const locationPromisified = wxPromisify(wx.getLocation); //获取经纬度
const modalPromisified = wxPromisify(wx.showModal); //弹窗
const loginPromisified = wxPromisify(wx.login); //登陆
const addressPromisified = wxPromisify(wx.chooseAddress); //获取地址
const settingPromisified = wxPromisify(wx.getSetting); //获取设置

// 封装post请求
const post = (url, data, timeout = 600) => {
  // url = app.globalData.siteroot + url;
  url = siteroot + url;
  var promise = new Promise((resolve, reject) => {
    //网络请求
    wx.showNavigationBarLoading();
    setTimeout(function() {
      wx.request({
        url: url,
        data: data,
        method: 'POST',
        success: function(res) { //服务器返回数据
          wx.hideLoading();
          if (res.statusCode == 200 && res.data.code == 0) {
            resolve(res.data.data);
          } else { //返回错误提示信息
            console.log(res)
            reject(res);
          }
        },
        fail: function(res) {
          wx.hideLoading();
          reject(res);
        },
        complete: function() {
          wx.hideNavigationBarLoading();
          wx.stopPullDownRefresh();
        }
      })
    }, timeout)
  });
  return promise;
}

// 封装get请求
const get = (url, data) => {
  url = app.globalData.siteroot + url;
  var promise = new Promise((resolve, reject) => {
    //网络请求
    wx.request({
      url: url,
      data: data,
      success: function(res) { //服务器返回数据
        if (res.statusCode == 200 && res.data.code == 0) {
          resolve(res.data.data);
        } else { //返回错误提示信息
          reject(res.data);
        }
      },
      error: function(e) {
        reject('网络出错');
      }
    })
  });
  return promise;
}

module.exports = {
  post,
  get,
  modalPromisified,
  loginPromisified,
  locationPromisified,
  addressPromisified,
  settingPromisified
}