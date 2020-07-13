const app = getApp();
const util = require('../../utils/util.js');

Page({

  data: {
    authFlag: true,
    toastHidden: true
  },

  onLoad: function() {},

  /**
   * 获取会员信息
   */
  getUserInfo: function(evt) {
    if (evt.detail.errMsg != "getUserInfo:ok") {
      // 授权失败
      this.setData({
        authFlag: false,
        toastHidden: false,
        toastTitle: "授权失败，请重试",
        toastAction: ""
      })
    } else {
      // 授权成功
      this.auth(evt.detail.userInfo);
    }
  },


  /**
   * 打开系统设置
   */
  showSetting: function(evt) {
    var that = this;
    wx.openSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          this.setData({
            authFlag: true,
            toastHidden: false,
            toastTitle: "授权成功",
            toastAction: ""
          })
        } else {
          this.setData({
            authFlag: false,
            toastHidden: false,
            toastTitle: "授权失败",
            toastAction: ""
          })
        }
      },
      fail: function() {
        that.setData({
          toastHidden: false,
          toastTitle: "系统错误请及时联系管理员",
          toastAction: ""
        })
      }
    })
  },

  /**
   * 用户认证请求
   */
  auth: function(userInfo) {
    var that = this;
    util.post('/api/user/authentication', {
      nickName: userInfo.nickName,
      avatarUrl: userInfo.avatarUrl
    }, that, 200).then(res => {
      that.setData({
        toastHidden: false,
        toastTitle: "登陆成功",
        toastAction: "login"
      })
      // 更新globalData
      app.globalData.isAuth = true;
      app.globalData.avatarUrl = userInfo.avatarUrl;
      app.globalData.nickname = userInfo.nickName;
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "登陆失败",
        toastAction: ""
      })
    })
  },

  /**
   * modal 点击
   */
  toastConfirm: function(evt) {
    if (this.data.toastAction == "login") {
      wx.switchTab({
        url: '/pages/index/index'
      })
    }
  }

})