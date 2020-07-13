var app = getApp();
var interval = null;
var util = require('../../utils/util.js');
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
      title: '加载中...',
      mask: true
    })
    this.getActivityList();
  },

  getActivityList: function() {
    var that = this;
    if (!that.data.isHaveMore) {
      wx.showToast({
        title: '没有更多啦',
        icon: 'loading',
        mask: true,
        duration: 1000
      })
      wx.stopPullDownRefresh();
      wx.hideLoading();
      wx.hideNavigationBarLoading();
      return;
    }
    setTimeout(function() { // 加载活动列表
      wx.request({
        url: app.globalData.siteroot + 'fangte/getActivityList',
        method: 'POST',
        dataType: 'json',
        data: {
          openid: wx.getStorageSync('openid'),
          pageNum: that.data.pageNum
        },
        success: function(res) {
          if (res.data.code == "201") {
            wx.showToast({
              title: '活动敬请期待',
              icon: 'loading',
              duration: 800
            })
          } else if (res.data.code == "200") {
            // 对activity做处理
            var activity = res.data.activity;
            var countDown = [];
            for (var i = 0; i < activity.length; i++) {
              countDown.push({
                oriTime: activity[i].countDown,
                time: util.getTimeStr(activity[i].countDown),
                isActive: true
              });
            }
            that.setData({
              activityList: activity,
              countDown: countDown,
              isHaveActivity: true,
              isHaveMore: res.data.isHaveMore,
              pageNum: that.data.pageNum + 1
            })
            // 调用定时刷新函数更新倒计时数据
            that.refreshCountDown(countDown);
          }
        },
        fail: function() {},
        complete: function() {
          wx.stopPullDownRefresh();
          wx.hideLoading();
          wx.hideNavigationBarLoading();
        }
      })
    }, 300)
  },

  /**
   * 刷新活动倒计时
   */
  refreshCountDown: function(countDown) {
    var that = this;
    interval = setInterval(function() {
      var isCanClearIntval = true;
      for (var i = 0; i < countDown.length; i++) {
        countDown[i].oriTime = countDown[i].oriTime - 1;
        countDown[i].isActive = countDown[i].oriTime == 0 ? false : true;
        if (countDown[i].isActive) {
          countDown[i].time = util.getTimeStr(countDown[i].oriTime);
          isCanClearIntval = false;
        }
      }
      that.setData({
        countDown: countDown
      });
      if (isCanClearIntval) {
        clearInterval(interval);
      }
    }.bind(that), 1000);
  },

  onPullDownRefresh: function() {
    wx.showNavigationBarLoading();
    this.setData({
      activityList: [],
      isHaveActivity: false,
      isHaveMore: true,
      pageNum: 0
    })
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    this.getActivityList();
  },

  /**
   * 用户转发分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.share_text || 'A · Q大玩家！你值得拥有',
      path: '/pages/index/index'
    }
  },

  onReachBottom: function() {
    wx.showNavigationBarLoading();
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    this.getActivityList();
  }

})