const app = getApp();
var util = require('../../utils/util.js');
var check = require('../../utils/check.js');

Page({
  data: {
    userInfo: {}, //用户信息
    addressAuth: true,
    isCanChooseAddress: true, // 避免多次点击
    menuList: [{
      icon: '../../images/icon-cart.png',
      text: '购物车',
      tap: 'myCart'
    }, {
      icon: '../../images/icon-location.png',
      text: '收货地址',
      tap: 'myAddress'
    }, {
      icon: '../../images/icon-coupon.png',
      text: '优惠券',
      tap: 'myCoupon'
    }, {
      icon: '../../images/icon-fav.png',
      text: '我的收藏',
      tap: 'myFav'
    }, {
      icon: '../../images/icon-service-online.png',
      text: '在线客服',
      isCut: true,
      opentype: "contact"
    }, {
      icon: '../../images/icon-service.png',
      text: '客服热线',
      tap: 'serviceCall'
    }, {
      icon: '../../images/icon-question.png',
      text: '疑问解答',
      isCut: true,
      tap: 'question'
    }, {
      icon: '../../images/icon-logout.png',
      text: '退出登录',
      isCut: true,
      tap: 'logout'
    }],
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    }
  },

  onShow: function() {    
    let cartData = this.data.cartData;
    cartData.money = app.globalData.cartPrice;
    this.setData({
      cartData: cartData
    })
  },

  /**
   * 在这个钩子判断用户是否授权
   */
  onShow: function() {
    this.setData({
      userInfo: wx.getStorageSync('userInfo') || {}
    })
  },

  /**
   * 用户退出登录
   */
  logout: function(res) {
    var that = this;
    util.modalPromisified({
      title: '系统提示',
      content: '您确定要退出登录吗？',
    }).then(res => {
      if (res.confirm) {
        wx.removeStorageSync('userInfo');
        that.setData({
          userInfo: {}
        })
      }
    })
  },

  /**
   * 用户拨打客服专线
   */
  serviceCall: function() {
    let servicePhone = app.globalData.setting.service_phone;
    if (!servicePhone) {
      util.modalPromisified({
        title: '系统提示',
        content: '暂未设置客服热线，请联系在线客服',
        showCancel: false
      })
      return;
    }
    wx.makePhoneCall({
      phoneNumber: servicePhone
    })
  },

  /**
   * 跳转到我的收藏
   */
  myFav: function() {
    wx.navigateTo({
      url: '/pages/favourite/favourite'
    })
  },

  /**
   * 查看我的购物车
   */
  myCart: function() {
    wx.navigateTo({
      url: '/pages/cart/cart'
    })
  },

  /**
   * 查看优惠券
   */
  myCoupon: function() {
    wx.navigateTo({
      url: '/pages/coupon/coupon'
    })
  },

  /**
   * 跳转至用户认证界面
   */
  navToAuth: function() {
    wx.navigateTo({
      url: '../userauth/userauth',
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

  /**
   * 查看我的订单
   */
  navToOrder: function(evt) {
    wx.navigateTo({
      url: '../order/order?state=' + evt.currentTarget.dataset.state,
    })
  },


  /**
   * 跳转到我的收获地址界面
   */
  myAddress: function() {
    var that = this;
    if (!that.data.isCanChooseAddress) {
      return;
    } else {
      that.setData({
        isCanChooseAddress: false
      })
    }
    // 先判断系统是否有设置地址
    wx.chooseAddress({
      success: function(res) {},
      fail: function() {
        wx.getSetting({
          success: res => {
            if (!res.authSetting['scope.address']) {
              wx.showModal({
                title: '操作提示',
                content: '需要授权才可获取地址',
                showCancel: false,
                success: function() {
                  that.setData({
                    addressAuth: false
                  })
                }
              })
            }
          }
        })
      },
      complete: function() {
        that.setData({
          isCanChooseAddress: true
        })
      }
    })
  },

  openSetting: function(e) {
    var that = this;
    if (e.detail.authSetting['scope.address']) {
      that.setData({
        addressAuth: true
      })
      wx.showToast({
        title: '授权成功',
        duration: 1000
      })
    } else {
      wx.showToast({
        title: '授权失败',
        icon: 'none',
        duration: 1000
      })
    }
  },

  question: function() {
    wx.navigateTo({
      url: '../question/question'
    })
  }

})