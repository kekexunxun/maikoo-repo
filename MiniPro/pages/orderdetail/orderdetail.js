const app = getApp();
var util = require('../../utils/util.js');
var action = require('../../utils/action.js');
var check = require('../../utils/check.js');

Page({

  data: {
    orderInfo: [], // 商品详情
  },

  onLoad: function(options) {
    if (!options.ordersn) {
      util.modalPromisified({
        title: '系统提示',
        content: '参数缺失',
        showCancel: false
      }).then(function(res) {
        wx.redirectTo({
          url: '/pages/index/index'
        })
      })
      return;
    }
    this.getOrderInfo(options.ordersn);
  },

  /**
   * 获取订单详情 
   */
  getOrderInfo: function(ordersn) {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    check.checkLoginState().then(res => {
      return util.post('order/getOrderInfo', {
        uid: app.globalData.uid,
        ordersn: ordersn
      }, 300)
    }).then(res => {
      // wx.setStorageSync('data', res);
      that.setData({
        orderInfo: res
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误请稍后再试',
        showCancel: false
      }).then(function(res) {
        wx.redirectTo({
          url: '/pages/index/index'
        })
      })
    }).finally(res => {})
  },

})