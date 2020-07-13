var app = getApp();
var util = require('../../utils/util.js');

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
        if (res.authSetting['scope.userInfo']) {
          wx.showLoading({
            title: '登陆中...',
            mask: true
          })
          util.post('minibase/setUserInfo', {
            openid: wx.getStorageSync('openid'),
            userInfo: e.detail.userInfo,
            uid: app.globalData.uid
          }).then(res => {
            wx.showToast({
              title: '登陆成功',
              duration: 800
            })
            wx.setStorageSync('userInfo', e.detail.userInfo);
            setTimeout(res => {
              wx.navigateBack({
                delta: 1
              })
            }, 900)
          }).catch(res => {
            wx.showModal({
              title: '系统提示',
              content: '网络错误，授权失败，请重新尝试',
              showCancel: false
            })
          }).finally(res => {
          })
        } else {
          wx.showModal({
            title: '系统提示',
            content: '网络错误，授权失败，请重新尝试',
            showCancel: false
          })
        }
      },
      fail: function() {
        wx.showModal({
          title: '系统提示',
          content: '网络错误，授权失败，请重新尝试',
          showCancel: false
        })
      }
    })
  },


})