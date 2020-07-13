var app = getApp();
var util = require('../../utils/util.js');

Page({
  data: {
    activeIndex: 0,
    ad: 'https://art.up.maikoo.cn/static/img/banner/default.png',
    catList: [], // 菜单列表
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    }
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

  onShow: function () {
    var that = this;
    // 将购物车的总金额放到globalData
    let cartData = that.data.cartData;
    cartData.money = app.globalData.cartPrice;
    that.setData({
      cartData: cartData
    })
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
      uid: app.globalData.uid
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
  },

  /**
   * 跳转到购物车界面
   */
  navToCart: function (evt) {
    wx.navigateTo({
      url: '/pages/cart/cart'
    })
  },

})