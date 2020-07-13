const app = getApp();
var util = require('../../utils/util.js');
var check = require('../../utils/check.js');
var sliderWidth = 96; // 需要设置slider的宽度，用于计算中间位置
Page({

  data: {
    tabs: ["未使用", "已使用", "已过期"],
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
    way: 1, // 用户进入该界面的方式
    couponList: [{
      status: 0,
      list: [],
      pageNum: 0,
      remark: '未使用'
    }, {
      status: 1,
      list: [],
      pageNum: 0,
      remark: '已使用'
    }, {
      status: 2,
      list: [],
      pageNum: 0,
      remark: '已过期'
    }],
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    }
  },

  /**
   * 用户可以从 "我的 - 优惠券" 进入 此时 way = 1
   * 从下订单界面进入时 way = 2
   * 根据不同的情况提供不同的返回值
   */
  onLoad: function(options) {
    var that = this;
    that.setNavBarInfo();
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    if (options.way == 2) {
      that.setData({
        way: options.way,
        totalFee: options.totalFee
      })
    }
    check.checkLoginState().then(res => {
      that.getCouponList();
    }).catch(res => {})
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
   * 设置顶部导航条
   */
  setNavBarInfo: function() {
    var that = this;
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          sliderLeft: (res.windowWidth / that.data.tabs.length - sliderWidth) / 2,
          sliderOffset: res.windowWidth / that.data.tabs.length * that.data.activeIndex
        });
      }
    });
  },

  /**
   * 顶部导航条点击时间
   */
  tabClick: function(e) {
    var that = this;
    if (that.data.way == 2) {
      return;
    }
    that.setData({
      sliderOffset: e.currentTarget.offsetLeft,
      activeIndex: e.currentTarget.id
    });
    if (that.data.couponList[e.currentTarget.id].pageNum == 0 && that.data.couponList[e.currentTarget.id].list.length == 0) {
      wx.showLoading({
        title: '加载中',
        mask: true
      })
      wx.showNavigationBarLoading();
      that.getCouponList();
    }
  },

  /**
   * 获取卡券列表
   */
  getCouponList: function() {
    var that = this;
    // 下拉刷新遇见多次请求BUG 这里是为了避免多次请求
    if (!that.data.isRequest) {
      that.setData({
        isRequest: true
      })
    } else {
      return;
    }
    let couponList = that.data.couponList;
    let activeIndex = that.data.activeIndex;
    util.post('store/getUserCoupon', {
      uid: app.globalData.uid,
      pageNum: couponList[activeIndex].pageNum,
      status: couponList[activeIndex].status
    }).then(res => {
      if (!res) {
        wx.showToast({
          title: '暂无数据',
          icon: 'loading',
          duration: 1000
        })
        return;
      }
      couponList[activeIndex].list = couponList[activeIndex].list.concat(res);
      couponList[activeIndex].pageNum = couponList[activeIndex].pageNum + 1;
      if (that.data.way == 2) {
        for (var i = 0; i < couponList[activeIndex].list.length; i++) {
          couponList[activeIndex].list[i].isDisable = parseFloat(that.data.totalFee) >= parseFloat(couponList[activeIndex].list[i].condition) ? false : true
        }
      }
      that.setData({
        couponList: couponList
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请稍后再试',
        showCancel: false
      })
    }).finally(res => {
      that.setData({
        isRequest: false
      })
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: '我在这里发现了一个优惠~',
      path: '/pages/coupon/coupon?origin=' + app.globalData.uid
    }
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    var that = this;
    var couponList = that.data.couponList;
    couponList[that.data.activeIndex].list = [];
    couponList[that.data.activeIndex].pageNum = 0;
    that.setData({
      couponList: couponList
    })
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    that.getCouponList();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    that.getCouponList();
  },

  /**
   * 用户选择卡券，根据进入方式返回对应数据
   */
  chooseCoupon: function(e) {
    var that = this;
    if (that.data.way == 1) {
      wx.switchTab({
        url: '/pages/index/index',
      })
    } else if (that.data.way == 2) {
      // 将当前优惠券信息存至本地缓存
      var couponList = that.data.couponList[that.data.activeIndex].list;
      var couponid = e.currentTarget.dataset.couponid;
      for (var i = 0; i < couponList.length; i++) {
        if (couponList[i].coupon_id == couponid) {
          let couponInfo = {};
          couponInfo.coupon_id = couponList[i].coupon_id;
          couponInfo.money = couponList[i].money;
          try {
            wx.setStorageSync('couponInfo', couponInfo);
          } catch (e) {
            console.log(e)
            util.modalPromisified({
              title: '系统提示',
              content: '系统内存不足，请清理缓存后重试',
              showCancel: false
            })
          }
          break;
        }
      }
      wx.navigateBack({
        delta: 1
      })
    }
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