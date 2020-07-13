const app = getApp();
const util = require('../../utils/util.js');

Page({

  data: {
    auth: [{
      func: 'writePhotosAlbum',
      name: '保存到相册'
    }],
    toastHidden: true
  },

  onLoad: function(options) {
    var that = this;
    if (options.authfunc) {
      let auth = that.data.auth;
      for (let i = 0; i < auth.length; i++) {
        if (auth[i].func == options.authfunc) {
          this.setData({
            authFunc: auth[i].func,
            authName: auth[i].name
          })
          break;
        }
      }
    }
  },

  /**
   * 打开系统设置
   */
  showSetting: function(evt) {
    var that = this;
    wx.openSetting({
      success: res => {
        if (res.authSetting['scope.' + that.data.authFunc]) {
          that.setData({
            toastHidden: false,
            toastTitle: "授权成功",
            toastAction: "navback"
          })
        } else {
          that.setData({
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
   * Toast 点击
   */
  toastConfirm: function() {
    if (this.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    }
  }

})