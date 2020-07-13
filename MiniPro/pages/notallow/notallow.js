var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {},

  onLoad: function(options) {
    this.getSysSetting()
  },

  /**
   * 获取系统设置
   */
  getSysSetting: function() {
    var that = this;
    util.post('minibase/getSystemSetting', {
      openid: wx.getStorageSync('openid') || 'no-openid'
    }, 100).then(res => {
      that.setData({
        setting: res
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误',
        confirmText: '重新连接'
      }).then(res => {
        if (res.confirm) that.getSysSetting()
      })
    })
  },

  /**
   * 联系管理员
   */
  callManage: function() {
    var that = this
    util.modalPromisified({
      title: '系统提示',
      content: '您确定要拨打管理员电话吗？'
    }).then(res => {
      if (res.confirm) {
        let mobile = that.data.setting.service_phone;
        if (!mobile) {
          util.modalPromisified({
            title: '系统提示',
            content: '管理员联系方式未设置',
            showCancel: false
          })
        } else {
          wx.makePhoneCall({
            phoneNumber: mobile,
          })
        }
      }
    })
  }
})