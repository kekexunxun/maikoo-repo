//app.js
App({
  onLaunch: function(options) {
    //调用API从本地缓存中获取数据
    var that = this;

    // 判断是否有更新
    const updateManager = wx.getUpdateManager()
    updateManager.onCheckForUpdate(function(res) {
      // 请求完新版本信息的回调
      // console.log(res.hasUpdate)
    })
    updateManager.onUpdateReady(function() {
      wx.showModal({
        title: '更新提示',
        content: '新版本已经准备好，请点击确认以更新',
        showCancel: false,
        success: function(res) {
          if (res.confirm) {
            // 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
            updateManager.applyUpdate()
          }
        }
      })
    })

    var logs = wx.getStorageSync('logs') || [];
    var currentTime = parseInt(Date.now() / 1000);
    logs.unshift(currentTime);
    wx.setStorageSync('logs', logs);
    // 记录用户登陆信息
    if (!wx.getStorageSync('openid')) {
      //调用登录接口
      wx.login({
        success: function(res) {
          //获取用户openid
          if (res.code) {
            wx.request({
              url: that.globalData.siteroot + 'minibase/getUserOpenid',
              method: 'POST',
              dataType: 'json',
              data: {
                code: res.code
              },
              success: function(res) {
                // 将用户相关信息入库，openid和登录态有效期
                wx.setStorageSync('openid', res.data.openid);
                that.globalData.userInfo = res.data.user['userInfo'];
                that.globalData.userID = res.data.user['userID'];
                that.globalData.isAuth = res.data.user['isAuth'] ? res.data.user['isAuth'] : false;
              }
            })
          }
        }
      })
    } else {
      that.getUserAccountInfo();
    }
  },

  /**
   * 获取用户信息
   */
  getUserAccountInfo: function() {
    var that = this;
    // 获取用户信息
    wx.request({
      url: that.globalData.siteroot + 'minibase/getUserAccountState',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        if (res.data.code == "200") {
          that.globalData.userInfo = res.data.data.userInfo;
          that.globalData.userID = res.data.data.userID;
          that.globalData.isAuth = res.data.data.isAuth ? res.data.data.isAuth : false;
        }
      },
      fail: function() {},
      complete: function() {}
    })
  },

  globalData: {
    userInfo: null,
    // siteroot: "https://app.powerunion.cc/mini/",
    siteroot: "https://minipro.up.maikoo.cn/mini/",
    userID: null,
  },

  /**
   * 当用户不删除小程序时可以每天返回一次，但是用户如果删除就很尴尬
   * 在用户"退出"小程序时即向后台返回当前用户的使用情况
   * 使用情况包括点击小程序次数和当前进入小程序的时间
   */
  onHide: function() {
    var that = this;
    wx.request({
      url: that.globalData.siteroot + 'minibase/setUserLog',
      method: 'POST',
      dataType: 'json',
      data: {
        logs: wx.getStorageSync('logs'),
        columnLogs: wx.getStorageSync('columnLogs'),
        miniLogs: wx.getStorageSync('miniLogs'),
        catLogs: wx.getStorageSync('catLogs'),
        articleLogs: wx.getStorageSync('articleLogs'),
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        wx.clearStorageSync('logs');
        wx.clearStorageSync('columnLogs');
        wx.clearStorageSync('miniLogs');
        wx.clearStorageSync('catLogs');
        wx.clearStorageSync('articleLogs');
      }
    })
  }
})