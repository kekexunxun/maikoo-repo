var app = getApp();
var WxParse = require('../../utils/wxParse/wxParse.js');
Page({

  data: {
    articleInfo: [],  // 文章详情
  },

  /**
   * 请求对应的文章详情
   */
  onLoad: function(options) {
    var that = this;
    var articleId = options.articleId;
    if (!articleId) {
      wx.showToast({
        title: '参数错误',
        icon: 'loading'
      })
      setTimeout(function() {
        wx.navigateBack({
          delta: 1
        })
      }, 1200)
    } else {
      wx.showLoading({
        title: '加载中...',
        mask: true
      })
      wx.request({
        url: app.globalData.siteroot + 'article/getArticleSpec',
        method: 'POST',
        dataType: 'json',
        data: {
          articleId: articleId,
          openid: wx.getStorageSync('openid')
        },
        success: function(res) {
          if (res.statusCode == 200 && res.data.code == 0) {
            /**
             * WxParse.wxParse(bindName , type, data, target,imagePadding)
             * 1.bindName绑定的数据名(必填)
             * 2.type可以为html或者md(必填)
             * 3.data为传入的具体数据(必填)
             * 4.target为Page对象,一般为this(必填)
             * 5.imagePadding为当图片自适应是左右的单一padding(默认为0,可选)
             */
            that.setData({
              articleInfo: res.data.data
            })
            // 解析 content brief
            // WxParse.wxParse('article', 'html', res.data.data.title, that, 5);
            // WxParse.wxParse('brief', 'html', res.data.data.brief, that, 5);
            WxParse.wxParse('content', 'html', res.data.data.content, that, 5);
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
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})