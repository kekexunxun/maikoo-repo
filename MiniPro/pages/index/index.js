//获取应用实例
const app = getApp();
var util = require('../../utils/util.js');
var check = require('../../utils/check.js');
var action = require('../../utils/action.js');
var QQMapWX = require('../../utils/qqmap-wx-jssdk.js');
var qqmapsdk;

Page({
  data: {
    storeInfo: {
      banner: [{
        img_src: 'https://xnps.up.maikoo.cn/static/img/banner/default.png'
      }]
    }, // 首页列表信息
    userLocation: '我的地址',
    modalShow: false, // 是否展示遮罩层
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    },
    isCanChooseAddress: true, // 避免多次点击
    addressAuth: true
  },

  onLoad: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.showNavigationBarLoading();
    this.getStoreInfo();
    this.getLocationInfo();
    // 实例化微信地图类
    qqmapsdk = new QQMapWX({
      key: 'OYKBZ-4J2C3-DJT3F-Y7ZNF-7C2RH-NSBSV'
    });
  },

  /**
   * 获取地址
   */
  getLocationInfo: function() {
    var that = this;
    // 调用地址授权
    util.locationPromisified({
      type: 'gcj02'
    }).then(res => {
      qqmapsdk.reverseGeocoder({
        location: {
          latitude: res.latitude,
          longitude: res.longitude
        },
        success: res => {
          let userLocation = "";
          if (res.result.address_component.street) {
            userLocation = res.result.address_component.street;
          } else if (res.ad_info.name) {
            let temp = res.ad_info.name.split(',');
            userLocation = temp[-1];
          }
          that.setData({
            userLocation: userLocation
          })
        },
        fail: res => {
          util.modalPromisified({
            title: '系统提示',
            content: '定位失败，请尝试开启系统定位功能',
          })
        }
      })
    }).catch(res => {
      util.Promisified().then(res => {
        if (!res.authSetting['scope.userLocation']) {
          util.modalPromisified({
            title: '系统提示',
            content: '系统需要获取您的地址',
          }).then(function(res) {
            if (res.confirm) {}
          })
        }
      }).catch(res => {
        util.modalPromisified({
          title: '系统提示',
          content: '无法打开系统设置，请重启小程序尝试',
          showCancel: false
        })
      })
    })
  },

  onShow: function() {
    var that = this;
    // 将购物车的总金额放到globalData
    let cartData = that.data.cartData;
    cartData.money = app.globalData.cartPrice;
    that.setData({
      cartData: cartData
    })
  },

  /**
   * 获取整个商店的首页信息
   */
  getStoreInfo: function() {
    var that = this;
    // 等待系统设置和用户信息加载完成
    check.checkLoginState().then(res => {
      // 请求首页数据
      return util.post('store/getStoreIndex', {
        uid: app.globalData.uid,
        openid: wx.getStorageSync('openid')
      }, 100)
    }).then(res => {
      that.setData({
        bannerList: res.banner,
        topColumn: res.top,
        columnList: res.column,
        catList: res.cat
      })
      // 设置系统相关数据
      that.setSetting();
      // 将购物车的总金额放到globalData
      app.globalData.cartPrice = res.cartPrice
      // 设置cartData
      let cartData = that.data.cartData;
      cartData.money = res.cartPrice;
      that.setData({
        cartData: cartData
      })
    }).catch(res => {
      console.log(res.data.toString())
      console.log(res.toString())
      util.modalPromisified({
        title: '系统提示',
        content: res.data,
        showCancel: false
      })
    }).finally(res => {})
  },

  /**
   * 添加商品到购物车
   */
  addToCart: function(evt) {
    action.addToCart(evt.currentTarget.dataset.goodsid);
    // 更新cartprice
    app.globalData.cartPrice = ((app.globalData.cartPrice * 100 + evt.currentTarget.dataset.price * 100) / 100).toFixed(2);
    let cartData = this.data.cartData;
    cartData.money = app.globalData.cartPrice;
    this.setData({
      cartData: cartData
    })
  },

  /**
   * 应用系统相关设置
   */
  setSetting: function() {
    var that = this;
    let setting = app.globalData.setting;
    // 设置navBarTitle
    if (setting.mini_name) {
      wx.setNavigationBarTitle({
        title: setting.mini_name,
      })
    }
    // 设置navBarColor
    // if (setting.mini_color) {
    //   wx.setNavigationBarColor({
    //     frontColor: '#ffffff',
    //     backgroundColor: setting.mini_color,
    //     animation: {
    //       duration: 800,
    //       timingFunc: 'linear'
    //     }
    //   })
    // }
    // 判断是否展示首页弹窗
    // 将弹窗写入本地缓存 12小时内登陆不会重复显示
    if (setting.is_layer_show == 1) {
      let layerShowTime = wx.getStorageSync('layerInfo');
      if (!layerShowTime || (layerShowTime && parseInt(Date.now() / 1000) - layerShowTime > 43200)) {
        let cartData = that.data.cartData;
        cartData.isShow = false;
        that.setData({
          modalShow: true,
          layerImg: setting.layer_img,
          cartData: cartData
        })
      }
      layerShowTime = parseInt(Date.now() / 1000);
      wx.setStorageSync('layerInfo', layerShowTime);
    }
  },

  /**
   * Banner跳转事件
   * 0不跳转1跳转指定商品2跳转到文章3跳转到分类
   */
  bannerNav: function(evt) {
    var that = this;
    let curBanner = that.data.bannerList[evt.currentTarget.dataset.idx];
    let url = "";
    if (curBanner.nav_type == 0) return;
    if (curBanner.nav_type == 1) {
      url = '/pages/goodsdetail/goodsdetail?goodsid=' + curBanner.nav_id;
    } else if (curBanner.nav_type == 2) {
      url = '/pages/article/article?articleid=' + curBanner.nav_id;
    } else if (curBanner.nav_type == 3) {
      url = '/pages/goodslist/goodslist?type=2&catid=' + curBanner.nav_id
    }
    wx.navigateTo({
      url: url
    })
  },

  /**
   * 弹窗跳转
   * 0不跳转1跳转商品2跳转文章3跳转优惠券
   */
  layerNav: function(evt) {
    var that = this;
    // 关闭弹窗
    that.setData({
      modalShow: false
    })
    let setting = app.globalData.setting;
    let url = "";
    if (setting.layer_nav_type == 0) return;
    if (setting.layer_nav_type == 1) {
      url = '../goodsdetail/goodsdetail?goodsid=' + setting.layer_nav_id
    } else if (setting.layer_nav_type == 2) {
      url = '/pages/article/article?articleid=' + setting.layer_nav_id
    } else if (setting.layer_nav_type == 3) {
      url = '/pages/coupon/coupon';
    }
    wx.navigateTo({
      url: url
    })
  },

  /**
   * 关闭首页弹窗
   */
  closeLayer: function() {
    let cartData = this.data.cartData;
    cartData.isShow = true;
    this.setData({
      modalShow: false,
      cartData: cartData
    })
  },

  /**
   * 跳转到商品详情
   */
  navToGoods: function(evt) {
    wx.navigateTo({
      url: '../goodsdetail/goodsdetail?goodsid=' + evt.currentTarget.dataset.goodsid
    })
  },

  /**
   * 跳转到分类详情界面
   */
  navToCat: function(evt) {
    wx.navigateTo({
      url: '../goodslist/goodslist?type=2&catid=' + evt.currentTarget.dataset.catid
    })
  },

  /**
   * 跳转到商品专栏
   */
  navToColumn: function(evt) {
    wx.navigateTo({
      url: '../goodslist/goodslist?type=1&colid=' + evt.currentTarget.dataset.colid
    })
  },

  /**
   * 跳转到搜索界面
   */
  navToSearch: function(evt) {
    wx.switchTab({
      url: '../search/search',
    })
  },

  /**
   * 跳转到购物车界面
   */
  navToCart: function(evt) {
    wx.navigateTo({
      url: '/pages/cart/cart'
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
      success: function(res) {
        if (!res.detailInfo) {
          util.modalPromisified({
            title: '操作提示',
            content: '您为填写详细地址，无法设定位置',
            showCancel: false
          }).then(res => {
            that.setData({
              addressAuth: false
            })
          })
        } else {
          that.setData({
            userLocation: res.detailInfo
          })
        }
      },
      fail: function() {
        wx.getSetting({
          success: res => {
            if (!res.authSetting['scope.address']) {
              util.modalPromisified({
                title: '操作提示',
                content: '需要授权才可获取地址',
                showCancel: false
              }).then(res => {
                that.setData({
                  addressAuth: false
                })
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

  /**
   * 遮罩层放置手指乱移动
   */
  preventTouchMove: function() {},

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || '你想要的商品我这里全都有~',
      path: '/pages/index/index'
    }
  },

  onPullDownRefresh: function() {
    this.getStoreInfo();
  }

})