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
      if (res.status == 1) {
        that.setCountDown();
      }
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

  /**
   * 跳转到购物车界面
   */
  navToCart: function(evt) {
    wx.navigateTo({
      url: '/pages/cart/cart'
    })
  },

  /**
   * 未支付的倒计时
   */
  setCountDown: function() {
    var that = this;
    let countDownTime = "";
    let leftTime = that.data.orderInfo.left_time;
    let intval = setInterval(res => {
      if (leftTime == 0 || leftTime == -1) {
        clearInterval(intval);
        that.getOrderInfo(that.data.orderInfo.order_sn);
      } else {
        leftTime--;
        let min = Math.floor(leftTime / 60);
        let sec = leftTime % 60;
        if (sec <= 9) {
          sec = '0' + sec
        }
        countDownTime = '0' + min + ':' + sec;
        that.setData({
          countDownTime: countDownTime
        })
      }
    }, 1000)
  },

  /**
   * 微信支付请求
   */
  createWxpay: function(evt) {
    action.createWxpay(evt.currentTarget.dataset.ordersn, 3);
  },

})