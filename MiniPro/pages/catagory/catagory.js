var app = getApp();
var util = require('../../utils/util.js');

Page({
  data: {
    activeIndex: 0,
    ad: 'https://art.up.maikoo.cn/static/img/banner/default.png',
    catList: [], // 菜单列表
  },

  onLoad: function(options) {
    var that = this;
    wx.getSystemInfo({
      success: (res) => {
        that.setData({
          deviceWidth: res.windowWidth,
          deviceHeight: res.windowHeight
        });
      }
    });
    that.getCatagoryList();
  },

  /**
   * 获取分类列表
   */
  getCatagoryList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('catagory/getCatagoryList', {
      uid: 12
    }).then(res => {
      that.setData({
        catList: res || []
      })
    }).catch(res => {
      wx.showModal({
        title: '系统提示',
        content: '网络错误，请下拉刷新界面重试',
        success: res => {
          if (res.confirm) {
            wx.startPullDownRefresh();
            wx.showNavigationBarLoading();
            wx.showLoading({
              title: '加载中...',
              mask: true
            })
            that.getCatagoryList();
          }
        }
      })
    }).finally(res => {})
  },

  onShow: function() {
    // 页面显示
  },

  changeTab: function(e) {
    this.setData({
      activeIndex: e.currentTarget.dataset.index
    })
  },

  /**
   * 跳转到分类界面
   */
  navToCat: function(e) {
    wx.navigateTo({
      url: '../goodslist/goodslist?type=2&catid=' + e.currentTarget.dataset.catid + '&catname=' + e.currentTarget.dataset.catname
    })
  }

})