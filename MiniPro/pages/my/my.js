const app = getApp();
var util = require('../../utils/util.js');

Page({
  data: {
    userInfo: [], //用户信息
    testInputShow: false, // 输入测试ID进行测试的Input Modal是否隐藏
    verifyCode: '', // 用户输入的核销订单号
    menuList: [{
      icon: '../../images/promote.png',
      text: '我的推介',
      tap: 'myPromotiton',
      isadmin: false
    }, {
      icon: '../../images/activity.png',
      text: '我的活动',
      tap: 'myActivity',
      isadmin: false
    }, {
      icon: '../../images/order.png',
      text: '我的订单',
      tap: 'myOrder',
      isadmin: false
    }, {
      icon: '../../images/cart.png',
      text: '我的购物车',
      tap: 'myCart',
      isadmin: false
    }, {
      icon: '../../images/setting.png',
      text: '个人信息',
      tap: 'myInfo',
      isadmin: false
    }, {
      icon: '../../images/admin.png',
      text: '订单核销',
      tap: 'adminCheck',
      isadmin: true
    }],
    isLogin: false, //用户是否登陆
    rebate: 0.00,
    isAdmin: false, //是否是管理员
  },

  onLoad: function(options) {
    var that = this;
    // 判断用户是否初次登陆 这里的登陆实际是获取用户信息
    if (!app.globalData.userInfo) {
      wx.showToast({
        title: '请用微信授权登陆',
        icon: "none"
      })
      that.setData({
        isLogin: false
      })
    } else if (!app.globalData.isAuth) {
      that.setData({
        isLogin: true
      })
      wx.showModal({
        title: '系统提示',
        content: '您未实名认证，将被限制部分系统功能的使用。请点击确定进行认证',
        success: function(res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../userinfo/userinfo',
            })
          }
        }
      })
    } else {
      that.setData({
        isLogin: true,
        isAdmin: app.globalData.isAdmin,
        isAuth: app.globalData.isAuth,
        userInfo: app.globalData.userInfo,
        rebate: app.globalData.rebate
      })
    }
  },

  /**
   * 获取用户信息
   */
  getUserInfo: function(e) {
    var that = this;
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          wx.showToast({
            title: '授权成功'
          })
          wx.request({
            url: app.globalData.siteroot + 'mini/setUserInfo',
            method: 'POST',
            dataType: 'json',
            data: {
              openid: wx.getStorageSync('openid'),
              userInfo: e.detail.userInfo
            },
            success: function(res) {
              // 系统重新调用获取用户信息接口以更新当前用户信息
              app.globalData.userInfo = e.detail.userInfo;
              that.setData({
                userInfo: e.detail.userInfo,
                isLogin: true
              })
            },
            fail: function() {},
            complete: function() {}
          })
        } else {
          wx.showToast({
            title: '授权失败',
            icon: 'loading'
          })
        }
      }
    })
  },

  /**
   * 查看我的推介
   */
  myPromotiton: function() {
    var that = this;
    if (that.isLogin()) {
      wx.navigateTo({
        url: '../promote/promote',
      })
    }
  },
  /**
   * 查看我参与的活动
   */
  myActivity: function() {
    var that = this;
    if (that.isLogin()) {
      wx.navigateTo({
        url: '../myactivity/myactivity',
      })
    }
  },
  /**
   * 查看我的订单
   */
  myOrder: function() {
    var that = this;
    if (that.isLogin()) {
      wx.navigateTo({
        url: '../order/order',
      })
    }
  },
  /**
   * 查看我的购物车
   */
  myCart: function() {
    var that = this;
    if (that.isLogin()) {
      wx.navigateTo({
        url: '../cart/cart',
      })
    }
  },
  /**
   * 查看我的信息
   */
  myInfo: function() {
    var that = this;
    if (that.isLogin()) {
      wx.navigateTo({
        url: '../userinfo/userinfo',
      })
    }
  },
  /**
   * 管理员进入核销票券界面
   */
  adminCheck: function() {
    var that = this;
    if (that.isLogin()) {
      wx.showActionSheet({
        itemList: ['核销码核销', '二维码核销'],
        success: function(res) {
          if (res.tapIndex == 0) {
            // 打开modal框输入
            that.setData({
              testInputShow: true
            })
          } else if (res.tapIndex == 1) {
            wx.scanCode({
              onlyFromCamera: true,
              success: function(res) {
                if (!res.path) {
                  wx.showModal({
                    title: '系统提示',
                    content: '请扫描正确的二维码',
                    showCancel: false
                  })
                }
                let path = res.path.split('?');
                let scene = decodeURIComponent(path[1]);
                let sceneArr = scene.split('=');
                let orderId = sceneArr[2];
                wx.navigateTo({
                  url: '/pages/verify/verify?orderid=' + orderId,
                })
              },
              fail: function(res) {
                console.log(res)
                wx.showModal({
                  title: '系统提示',
                  content: '系统错误',
                  showCancel: false
                })
              }
            })
          }
        }
      })
    }
  },

  /**
   * 判断用户是否登陆
   */
  isLogin: function() {
    var that = this;
    if (that.data.isLogin) {
      return true;
    } else {
      wx.showToast({
        title: '请用微信授权登陆',
        icon: "none"
      })
      return false;
    }
  },

  /**
   * 判断用户登录态是否过期
   */
  isExpire: function() {
    var isExpire = true;
    var date = Date.now();
    var expire_time = wx.getStorageSync('expire_time');
    if (expire_time && (expire_time * 1000 < date)) {
      isExpire = false;
    }
    return isExpire;
  },

  /**
   * 用户发起提现申请
   */
  getRebate: function() {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '您确定要发起提现申请吗？',
      success: function(res) {
        if (res.confirm) {
          let rebateSub = parseFloat(app.globalData.rebate).toFixed(2) - parseFloat(app.globalData.user_rebate_min).toFixed(2);
          console.log(rebateSub)
          if (rebateSub <= 0) {
            wx.showModal({
              title: '系统提示',
              content: '您的佣金未达到最低提现标准，还差' + rebateSub * -1 + '元才可进行提现申请',
              showCancel: false
            })
          } else {
            wx.showLoading({
              title: '提现发起中',
              mask: true
            })
            setTimeout(function() {
              wx.request({
                url: app.globalData.siteroot + 'fangte/userGetRebate',
                method: 'POST',
                dataType: 'json',
                data: {
                  rebate: app.globalData.rebate,
                  userid: app.globalData.userID
                },
                success: function(res) {
                  wx.hideLoading();
                  if (res.statusCode == 200 && res.data.code == "200") {
                    // 简单的数据处理
                    wx.showModal({
                      title: '系统提示',
                      content: '提现发起成功，系统将在24-72小时内为您处理提现申请，有疑问可联系管理员',
                      showCancel: false,
                      success: function() {
                        // 将用户rebate 重置为 0 
                        app.globalData.rebate = parseFloat(0.00).toFixed(2);
                        that.setData({
                          rebate: parseFloat(0.00).toFixed(2)
                        })
                      }
                    })
                  } else {
                    wx.showModal({
                      title: '系统提示',
                      content: '网络错误，请重试',
                      showCancel: false
                    })
                  }
                },
                fail: function() {
                  console.log(res)
                  wx.hideLoading();
                  wx.showModal({
                    title: '系统提示',
                    content: '网络错误，请重试',
                    showCancel: false
                  })
                }
              })
            }, 400)
          }
        }
      }
    })
  },

  /**
   * 测试 ID 输入 modal 取消
   */
  modalCancel: function() {
    this.setData({
      verifyCode: "",
      testInputShow: false
    })
  },

  // 测试 ID 输入 modal 确认
  modalConfirm: function() {
    var that = this;
    if (!that.data.verifyCode) {
      util.modalPromisified({
        title: '系统提示',
        content: '请输入核销码',
        showCancel: false
      })
    } else {
      // 隐藏输入框 跳转到测试界面
      wx.navigateTo({
        url: '/pages/verify/verify?orderid=' + that.data.verifyCode,
      })
      that.setData({
        testInputShow: false,
        verifyCode: ""
      })
    }
  },

  /**
   * 输入测试ID
   */
  inputTestID: function(evt) {
    let verifyCode = util.stringTrim(evt.detail.value)
    this.setData({
      verifyCode: verifyCode
    })
  },

  /**
   * 阻止modal弹出时 屏幕仍可滑动的操作
   */
  preventTouchMove: function() {},

  // 用户下拉刷新
  onPullDownRefresh: function() {
    var that = this;
    wx.showNavigationBarLoading();
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    setTimeout(function() {
      wx.request({
        url: app.globalData.siteroot + 'mini/getUserAccountState',
        method: 'POST',
        dataType: 'json',
        data: {
          openid: wx.getStorageSync('openid')
        },
        success: function(res) {
          wx.hideLoading();
          let user = res.data.data;
          app.globalData.isAdmin = user.isAdmin;
          that.setData({
            isAdmin: user.isAdmin
          })
        },
        fail: function() {
          wx.hideLoading();
          wx.showToast({
            title: '网络错误',
            icon: 'loading',
            duration: 800
          })
        },
        complete: function() {
          wx.hideNavigationBarLoading();
          wx.stopPullDownRefresh();
        }
      })
    }, 600)
  }
})