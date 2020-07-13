var app = getApp();
var interval = null;
Page({

  data: {
    pageNum: 0, // 当前页码数量
    // count: 6,     //默认获取12条活动
    isHaveActivity: false, //当前是否有活动
    activityList: [], //活动列表
    isHaveMore: true
  },

  onLoad: function(options) {
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    this.getUserActivity();
  },

  getUserActivity: function() {
    var that = this;
    if (!that.data.isHaveMore) {
      wx.showToast({
        title: '没有更多啦',
        duration: 1000,
        mask: true,
        icon: 'loading'
      })
      wx.hideLoading();
      wx.hideNavigationBarLoading();
      wx.stopPullDownRefresh();
      return;
    }
    // 加载活动列表
    setTimeout(function() {
      wx.request({
        url: app.globalData.siteroot + 'fangte/getUserActivity',
        method: 'POST',
        dataType: 'json',
        data: {
          userid: app.globalData.userID,
          pageNum: that.data.pageNum
        },
        success: function(res) {
          if (res.statusCode == 200 && res.data.code == "200") {
            that.setData({
              activityList: res.data.activity,
              isHaveActivity: true,
              isHaveMore: res.data.isHaveMore,
              pageNum: that.data.pageNum + 1
            })
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
          wx.hideNavigationBarLoading();
          wx.stopPullDownRefresh();
        }
      })
    }, 600)
  },

  onPullDownRefresh: function() {
    this.setData({
      pageNum: 0,
      isHaveActivity: false,
      activityList: [],
      isHaveMore: true
    })
    this.getUserActivity();
  },

  onReachBottom: function() {
    this.getUserActivity();
  },
})