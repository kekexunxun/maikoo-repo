const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {

  },

  onLoad: function(options) {
    // 隐藏转发按钮
    wx.hideShareMenu({
      success: res => {
        console.log(res)
      },
      fail: res => {
        console.log(res)
      }
    });
  },

  /**
   * 用户进行会员支付
   */
  memberPay: function(){
    // 先下单
  }

})