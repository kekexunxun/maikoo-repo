const app = getApp()
var util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    rankList: [], // 排行榜列表
    pageNum: 0, // 当前页码
    isHaveMore: true, // 是否有更多的排行榜数据
    isHaveRank: true, // 是否有rank信息
  },

  onLoad: function(options) {
    this.getRankList(this.data.pageNum);
  },

  /**
   * 获取排行榜
   */
  getRankList: function(pageNum) {
    var that = this;
    var isHaveMore = that.data.isHaveMore;
    if (!isHaveMore) {
      wx.showToast({
        title: '没有更多啦',
        icon: 'loading'
      })
      wx.stopPullDownRefresh();
      return;
    }
    wx.showNavigationBarLoading();
    wx.showLoading({
      title: '加载中...',
      mask: true,
    })
    wx.request({
      url: app.globalData.siteroot + 'rank/getRankList',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        pageNum: pageNum
      },
      success: function(res) {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 0) {
          if (res.data.data) {
            if (res.data.data.length < 10) {
              isHaveMore = false;
            }
            that.setData({
              rankList: that.data.rankList.concat(res.data.data),
              pageNum: pageNum + 1
            })
          } else {
            isHaveMore = false;
            if (pageNum == 0) {
              that.setData({
                isHaveRank: false
              })
            }
            wx.showToast({
              title: '没有更多啦',
              icon: 'loading',
              duration: 1200
            })
          }
        }
      },
      fail: function() {
        wx.hideLoading();
      },
      complete: function() {
        wx.hideNavigationBarLoading();
        wx.hideLoading();
        wx.stopPullDownRefresh();
        that.setData({
          isHaveMore: isHaveMore
        })
      }
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    var that = this;
    // 初始化rankList
    that.setData({
      rankList: []
    })
    // 数据请求
    that.getRankList(0);
  },

  /**
   * 跳转到小程序详情界面
   */
  navToMiniDetail: function(evt){
    var that = this;
    var miniId = that.data.rankList[evt.currentTarget.dataset.idx].mini_id;
    wx.navigateTo({
      url: '../minidetail/minidetail?miniId=' + miniId,
    })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    this.getRankList(this.data.pageNum);
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: '今日小程序热搜~一榜搞定~',
      path: '/pages/rank/rank'
    }
  }
})