var app = getApp();
var sliderWidth = 10;
Page({

  data: {
    isHaveOrder: false, //是否有订单数据
    tabs: ["全部", "待付款", "待发货", "待收货"], //TAB列表
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
    tabIndex: [{
      list: [],
      pageNum: 0,
      orderStatus: 0
    }, {
      list: [],
      pageNum: 0,
      orderStatus: 1
    }, {
      list: [],
      pageNum: 0,
      orderStatus: 2
    }, {
      list: [],
      pageNum: 0,
      orderStatus: 3
    }],
  },

  onLoad: function(options) {
    var that = this;
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          sliderLeft: (res.windowWidth / that.data.tabs.length - 80) / 2,
          sliderOffset: res.windowWidth / that.data.tabs.length * that.data.activeIndex
        });
      }
    });
  },

  onShow: function() {
    var that = this;
    let tabIndex = that.data.tabIndex;
    tabIndex[that.data.activeIndex].pageNum = 0;
    tabIndex[that.data.activeIndex].list = [];
    that.setData({
      tabIndex: tabIndex
    })
    that.getOrderList();
  },

  // 用户支付
  prePay: function(e) {
    var that = this;
    wx.showModal({
      title: '操作提醒',
      content: '您确认要支付当前订单吗？',
      success: function(res) {
        if (res.confirm) {
          var orderid = e.detail.target.dataset.orderid;
          wx.request({
            url: app.globalData.siteroot + 'wxpay/createWxPay',
            method: 'POST',
            dataType: 'json',
            data: {
              orderid: orderid,
              openid: wx.getStorageSync('openid')
            },
            success: function(res) {
              if (res.data.code == "200") {
                var prepay = res.data.prepay;
                wx.requestPayment({
                  'timeStamp': prepay.timeStamp,
                  'nonceStr': prepay.nonce_str,
                  'package': 'prepay_id=' + prepay.prepay_id,
                  'signType': 'MD5',
                  'paySign': prepay.paySign,
                  'success': function(res) {
                    // 支付结果检测
                    wx.showLoading({
                      title: '支付确认中',
                      mask: true
                    })
                    setTimeout(function() {
                      wx.request({
                        url: app.globalData.siteroot + 'fangte/checkWxPay',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                          openid: wx.getStorageSync('openid'),
                          orderid: orderid
                        },
                        success: function(res) {
                          if (res.statusCode == 200 && res.data.code == "200") {
                            wx.showToast({
                              title: '支付校验成功',
                              mask: true,
                              duration: 800
                            })
                            setTimeout(function() {
                              wx.navigateTo({
                                url: '../order/order'
                              })
                            }, 800)
                          } else {
                            wx.showModal({
                              title: '系统提示',
                              content: '支付校验失败，请及时联系管理员',
                              showCancel: false,
                              success: function() {
                                wx.navigateTo({
                                  url: '../order/order'
                                })
                              }
                            })
                          }
                        },
                        fail: function(res) {
                          wx.showModal({
                            title: '系统提示',
                            content: '支付校验失败，请及时联系管理员',
                            showCancel: false,
                            success: function() {
                              wx.navigateTo({
                                url: '../order/order'
                              })
                            }
                          })
                        }
                      })
                    }, 600)
                  },
                  'fail': function(res) {
                    wx.showToast({
                      title: '付款失败',
                      icon: 'none'
                    })
                  },
                  'complete': function(res) {
                    // console.log(res);
                    if (res.errMsg == "requestPayment:cancel") {
                      // 微信6.5.2版本及以下取消支付回调
                      wx.showToast({
                        title: '付款失败',
                        icon: 'none'
                      })
                    }
                  }
                })
              } else if (res.data.code == "401") {
                wx.showModal({
                  title: '支付错误',
                  content: '订单已超时!',
                  showCancel: false
                })
                that.getOrderList();
              }
            }
          })
        }
      }
    })
  },

  /**
   * 用户取消订单
   */
  cancelOrder: function(e) {
    var that = this;
    var orderid = e.detail.target.dataset.orderid;
    wx.showModal({
      title: '操作提醒',
      content: '您确认要取消订单吗？',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.siteroot + 'fangte/cancelOrder',
            method: 'POST',
            dataType: 'json',
            data: {
              orderid: orderid,
              openid: wx.getStorageSync('openid')
            },
            success: function(res) {
              if (res.data.code == "200") {
                wx.showToast({
                  title: '订单取消成功',
                  duration: 1200
                })
                let tabIndex = that.data.tabIndex;
                tabIndex[that.data.activeIndex].list = [];
                tabIndex[that.data.activeIndex].pageNum = 0;
                // 列表更新
                that.setData({
                  tabIndex: tabIndex
                })
                that.getOrderList();
              }
            }
          })
        }
      }
    })
  },

  /**
   * 更新订单状态
   */
  updateOrderState: function(orderid) {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'fangte/finishPay',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        orderid: orderid
      },
      success: function(res) {
        if (res.data.code == "200") {
          wx.showToast({
            title: '订单状态更新成功',
          })
          that.getOrderList();
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'none'
          })
        }
      }
    })
  },

  /**
   * 获取用户订单信息
   * pageNum 页码
   * status 订单状态
   * index 订单状态在当前tab中的位置
   */
  getOrderList: function() {
    var that = this;
    var activeIndex = that.data.activeIndex;
    var tabIndex = that.data.tabIndex;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    // 延时0.6s再进行网络数据请求
    setTimeout(function() {
      wx.request({
        url: app.globalData.siteroot + 'fangte/getOrderList',
        method: 'POST',
        dataType: 'json',
        data: {
          openid: wx.getStorageSync('openid'),
          pageNum: tabIndex[activeIndex].pageNum,
          status: tabIndex[activeIndex].orderStatus,
          userid: app.globalData.userID
        },
        success: function(res) {
          if (res.data.code == "200") {
            // 0未付款 1待发货 2已发货待收货 3订单已完成 4订单已取消
            tabIndex[activeIndex].list = tabIndex[activeIndex].list.concat(res.data.order);
            tabIndex[activeIndex].pageNum += 1;
            that.setData({
              isHaveOrder: true,
              tabIndex: tabIndex
            })
          } else {
            if (tabIndex[activeIndex].pageNum != 0) {
              wx.showToast({
                title: '没有更多啦',
                icon: 'loading',
                duration: 800
              })
            } else {
              wx.showToast({
                title: '暂无数据',
                icon: 'none',
                duration: 800
              })
            }
          }
        },
        complete: function() {
          wx.stopPullDownRefresh();
          wx.hideLoading();
        }
      })
    }, 400);
  },

  /**
   * 顶部TAB点击触发事件
   */
  tabClick: function(e) {
    var that = this;
    that.setData({
      sliderOffset: e.currentTarget.offsetLeft,
      activeIndex: e.currentTarget.id
    });
    // 做请求
    if (that.data.tabIndex[e.currentTarget.id].list.length == 0 && that.data.tabIndex[e.currentTarget.id].pageNum == 0) {
      that.getOrderList();
    }
  },

  /**
   * 查看订单详情
   */
  orderDetail: function(e) {
    wx.navigateTo({
      url: '../orderdetail/orderdetail?orderid=' + e.detail.target.dataset.orderid,
    })
  },

  /**
   * 发货提醒
   */
  orderRemind: function() {
    setTimeout(function() {
      wx.showToast({
        title: '提醒成功',
      })
    }, 700);
  },

  /**
   * 确认收货
   */
  orderConfirm: function(e) {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '是否确认收货？',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '系统确认中',
            mask: true
          })
          setTimeout(function() {
            wx.request({
              url: app.globalData.siteroot + 'fangte/userConfirmOrder',
              method: 'POST',
              dataType: 'json',
              data: {
                openid: wx.getStorageSync('openid'),
                orderid: e.detail.target.dataset.orderid,
                userid: app.globalData.userID
              },
              success: function(res) {
                wx.hideLoading();
                if (res.statusCode == 200 && res.data.code == "200") {
                  wx.showToast({
                    title: '确认收货成功',
                    duration: 800,
                    mask: true
                  })
                  setTimeout(function() {
                    that.getOrderList();
                  }, 800)
                } else {
                  wx.showModal({
                    title: '系统提示',
                    content: '网络错误，请稍后再试',
                    showCancel: false
                  })
                }
              },
              fail: function() {
                wx.hideLoading();
                wx.showModal({
                  title: '系统提示',
                  content: '网络错误，请稍后再试',
                  showCancel: false
                })
              }
            })
          }, 600)
        }
      }
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    var that = this;
    let tabIndex = that.data.tabIndex;
    let activeIndex = that.data.activeIndex;
    tabIndex[activeIndex].pageNum = 0;
    tabIndex[activeIndex].list = [];
    that.getOrderList();
  },

  /**
   * 用户上拉触底 - 继续加载用户订单信息
   */
  onReachBottom: function() {
    var that = this;
    that.getOrderList();
  },

  /**
   * 删除订单
   */
  orderDelete: function(e) {
    console.log(e)
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '是否要删除该订单？',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '请稍等',
            mask: true
          })
          wx.request({
            url: app.globalData.siteroot + 'fangte/deleteOrder',
            method: 'POST',
            data: {
              orderid: e.detail.target.dataset.orderid
            },
            success: function(res) {
              wx.hideLoading();
              if (res.statusCode == 200 && res.data.code == "200") {
                wx.showToast({
                  title: '删除成功',
                  duration: 1000
                })
                setTimeout(function() {
                  // 先将当前界面订单删除
                  var tabIndex = that.data.tabIndex;
                  var activeIndex = that.data.activeIndex;
                  tabIndex[activeIndex].pageNum = 0;
                  tabIndex[activeIndex].list = [];
                  that.getOrderList();
                }, 800)
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              }
            },
            fail: function() {
              WX.hideLoading;
              wx.showToast({
                title: '网络错误',
                icon: 'loading'
              })
            }
          })
        }
      }
    })
  },

  /**
   * 用户申请售后
   */
  orderApplyAS: function(e) {
    console.log(e)
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '您确定想要申请售后吗？',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '请稍后',
            mask: true
          })
          setTimeout(function() {
            wx.request({
              url: app.globalData.siteroot + 'fangte/orderApplyAS',
              method: 'POST',
              dataType: 'json',
              data: {
                openid: wx.getStorageSync('openid'),
                orderid: e.detail.target.dataset.orderid,
                userid: app.globalData.userID
              },
              success: function(res) {
                wx.hideLoading();
                if (res.statusCode == 200 && res.data.code == "200") {
                  wx.showToast({
                    title: '申请成功',
                    duration: 800,
                    mask: true
                  })
                  setTimeout(function() {
                    that.getOrderList();
                  }, 600)
                } else {
                  wx.showModal({
                    title: '系统提示',
                    content: '网络错误，请稍后再试',
                    showCancel: false
                  })
                }
              },
              fail: function() {
                wx.hideLoading();
                wx.showModal({
                  title: '系统提示',
                  content: '网络错误，请稍后再试',
                  showCancel: false
                })
              }
            })
          }, 600)
        }
      }
    })
  },

  /**
   * 用户申请退款
   */
  orderRefund: function (e) {
    console.log(e)
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '您确定想要申请退款吗？',
      success: function (res) {
        if (res.confirm) {
          wx.showLoading({
            title: '请稍后',
            mask: true
          })
          setTimeout(function () {
            wx.request({
              url: app.globalData.siteroot + 'fangte/orderRefund',
              method: 'POST',
              dataType: 'json',
              data: {
                openid: wx.getStorageSync('openid'),
                orderid: e.detail.target.dataset.orderid,
                userid: app.globalData.userID
              },
              success: function (res) {
                wx.hideLoading();
                if (res.statusCode == 200 && res.data.code == "200") {
                  wx.showToast({
                    title: '申请成功',
                    duration: 800,
                    mask: true
                  })
                  setTimeout(function () {
                    that.getOrderList();
                  }, 600)
                } else {
                  wx.showModal({
                    title: '系统提示',
                    content: '网络错误，请稍后再试',
                    showCancel: false
                  })
                }
              },
              fail: function () {
                wx.hideLoading();
                wx.showModal({
                  title: '系统提示',
                  content: '网络错误，请稍后再试',
                  showCancel: false
                })
              }
            })
          }, 600)
        }
      }
    })
  }

})