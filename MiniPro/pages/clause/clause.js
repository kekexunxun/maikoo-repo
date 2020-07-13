var app = getApp();
var WxParse = require('../../utils/wxParse/wxParse.js');
Page({

  data: {
  
  },

  onLoad: function (options) {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'fangte/getCaluse',
      method: 'GET',
      dataType: 'json',
      success: function(res){
        wx.hideLoading();
        if(res.statusCode == 200 && res.data.code == "200"){
          /**
          * WxParse.wxParse(bindName , type, data, target,imagePadding)
          * 1.bindName绑定的数据名(必填)
          * 2.type可以为html或者md(必填)
          * 3.data为传入的具体数据(必填)
          * 4.target为Page对象,一般为this(必填)
          * 5.imagePadding为当图片自适应是左右的单一padding(默认为0,可选)
          */
          WxParse.wxParse('clause', 'html', res.data.clause, that, 5);
        }
      }
    })
  },

})