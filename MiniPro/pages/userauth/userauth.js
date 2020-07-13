var app = getApp();

Page({

  data: {
    isCanClick: true, // 是否可以点击授权按钮
  },

  onLoad: function(options) {
    // 此页面仅供微信授权使用
    var that = this;
  },

  /**
   * 勾选相关条款按钮
   */
  bindAgreeChange: function() {
    var that = this;
    var isAgree = that.data.isAgree ? !that.data.isAgree : true;
    that.setData({
      isAgree: isAgree
    })
  },

  /**
   * 获取用户信息
   */
  getUserInfo: function(e) {
    var that = this;
    wx.getSetting({
      success: res => {
        // console.log(res)
        if (res.authSetting['scope.userInfo']) {
          wx.showLoading({
            title: '授权中...',
            mask: true
          })
          wx.request({
            url: app.globalData.siteroot + 'minibase/setUserInfo',
            method: 'POST',
            dataType: 'json',
            data: {
              openid: wx.getStorageSync('openid'),
              userInfo: e.detail.userInfo
            },
            success: function(res) {
              // 系统重新调用获取用户信息接口以更新当前用户信息
              if (res.statusCode == 200 && res.data.code == 0) {
                wx.showToast({
                  title: '授权成功',
                  duration: 1000
                })
                app.globalData.userInfo = e.detail.userInfo;
                app.globalData.isAuth = true;
                setTimeout(function() {
                  wx.navigateBack({
                    delta: 1
                  })
                }, 1000)
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              }
            },
            fail: function() {},
            complete: function() {
              wx.hideLoading();
            }
          })
        } else {
          wx.showToast({
            title: '授权失败',
            icon: 'loading'
          })
        }
      }
    })
  },


})