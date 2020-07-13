var app = getApp();
var WxParse = require('../../utils/wxParse/wxParse.js');
Page({

  data: {

  },

  onLoad: function(options) {
    var that = this;
    // 数据请求goodsSpec
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'fangte/getGoodsSpecById',
      method: 'POST',
      dataType: 'json',
      data: {
        goodsid: options.goodsid
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          // 设置title
          wx.setNavigationBarTitle({
            title: res.data.data.name,
          })
          // 初始化商品详情
          /**
           * WxParse.wxParse(bindName , type, data, target,imagePadding)
           * 1.bindName绑定的数据名(必填)
           * 2.type可以为html或者md(必填)
           * 3.data为传入的具体数据(必填)
           * 4.target为Page对象,一般为this(必填)
           * 5.imagePadding为当图片自适应是左右的单一padding(默认为0,可选)
           */
          WxParse.wxParse('article', 'html', res.data.data.spec, that, 5);
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
          setTimeout(function() {
            wx.navigateBack({
              delta: 1
            })
          }, 1000);
        }
      },
      fail: function() {
        wx.showToast({
          title: '网络错误',
          icon: 'loading'
        })
        setTimeout(function() {
          wx.navigateBack({
            delta: 1
          })
        }, 1000);
      },
      complete: function(res) {
        wx.hideLoading();
      }
    })
  },

})