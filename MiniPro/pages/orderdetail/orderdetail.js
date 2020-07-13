var app = getApp();
Page({

  data: {
    orderInfo: [], // 订单列表
  },

  onLoad: function(options) {
    var that = this;
    var orderid = options.orderid;
    // 非空判断
    if (!orderid) {
      wx.showModal({
        title: '系统提示',
        content: '参数错误',
        showCancel: false,
        success: function(res) {
          wx.redirectTo({
            url: '../index/index',
          })
        }
      })
      return;
    }
    that.setData({
      orderId: options.orderid
    })
    that.getOrderByOrderId();
  },

  /**
   * 通过订单号去获取订单详情
   */
  getOrderByOrderId: function() {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'fangte/getOrderById',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        orderid: that.data.orderId
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == "200") {
          // 简单的数据处理
          that.setData({
            orderInfo: res.data.data
          })
        } else if (res.data.code == "201") {
          wx.showToast({
            title: '订单已被删除',
            icon: 'loading',
            mask: true
          })
          setTimeout(function() {
            wx.navigateBack({
              delta: 1
            })
          }, 800)
        }
      },
      fail: function() {},
      complete: function() {}
    })
  },

  /**
   * 确认收货
   */
  confirmOrder: function(e) {
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
                orderid: that.data.orderId,
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
                } else if (res.data.code == "201") {
                  wx.showModal({
                    title: '系统提示',
                    content: '订单状态已改变，请刷新界面重试',
                    showCancel: false
                  })
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
   * 用户申请生成核销码
   */
  genVerifyCode: function(e) {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '您确定要生成核销码吗？',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '核销码生成中',
          })
          wx.request({
            url: app.globalData.siteroot + 'fangte/genVerify',
            method: 'POST',
            dataType: 'json',
            data: {
              userid: app.globalData.userID,
              orderid: e.currentTarget.dataset.orderid
            },
            success: function(res) {
              wx.hideLoading();
              if (res.statusCode == 200 && res.data.code == "200") {
                wx.showToast({
                  title: '生成成功',
                  duration: 1000
                })
                let orderInfo = that.data.orderInfo;
                orderInfo.verify_url = res.data.verifyUrl;
                that.setData({
                  orderInfo: orderInfo
                })
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
        }
      }
    })
  },

  /**
   * 用户取消订单
   */
  cancelOrder: function(e) {
    var that = this;
    let orderInfo = that.data.orderInfo;
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
              orderid: orderInfo.order_id,
              openid: wx.getStorageSync('openid'),
              formId: e.detail.formId
            },
            success: function(res) {
              if (res.statusCode == 200 && res.data.code == "200") {
                wx.showToast({
                  title: '订单取消成功',
                  mask: true,
                  duration: 800
                })
                // 列表更新
                orderInfo.status = 5;
                orderInfo.status_conv = '已取消';
                that.setData({
                  orderInfo: orderInfo
                })
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              }
            },
            fail: function() {
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

  // 用户支付
  prePay: function(e) {
    var that = this;
    wx.showModal({
      title: '操作提醒',
      content: '您确认要支付当前订单吗？',
      success: function(res) {
        if (res.confirm) {
          var orderid = that.data.orderInfo.order_id;
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
                    // 支付成功
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
                              that.getOrderByOrderId();
                            }, 600)
                          } else {
                            wx.showModal({
                              title: '系统提示',
                              content: '支付校验失败，请及时联系管理员',
                              showCancel: false
                            })
                          }
                        },
                        fail: function(res) {
                          wx.showModal({
                            title: '系统提示',
                            content: '支付校验失败，请及时联系管理员',
                            showCancel: false
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
              }
            }
          })
        }
      }
    })
  },

  /**
   * 用户申请售后
   */
  orderApplyAS: function() {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '是否要申请售后？',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.siteroot + 'fangte/orderApplyAS',
            method: 'POST',
            data: {
              orderid: that.data.orderInfo.order_id,
              userid: app.globalData.userID
            },
            success: function(res) {
              if (res.statusCode == 200 && res.data.code == "200") {
                wx.showToast({
                  title: '申请成功',
                  duration: 800
                })
                let orderInfo = that.data.orderInfo;
                orderInfo.status = 6;
                orderInfo.status_conv = '申请售后中';
                that.setData({
                  orderInfo: orderInfo
                })
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              }
            }
          })
        }
      }
    })
  },

  /**
   * 删除订单
   */
  deleteOrder: function(e) {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '是否要删除该订单？',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.siteroot + 'fangte/deleteOrder',
            method: 'POST',
            data: {
              orderid: that.data.orderInfo.order_id
            },
            success: function(res) {
              if (res.statusCode == 200) {
                wx.showLoading({
                  title: '删除成功',
                  mask: true
                })
                setTimeout(function() {
                  wx.navigateBack({
                    delta: 1,
                  })
                }, 800)
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              }
            }
          })
        }
      }
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.getOrderByOrderId();
  },

  /**
   * 用户点击复制订单编号按钮
   */
  copyOrderID: function(e) {
    var that = this;
    // 获取formid
    wx.setClipboardData({
      data: that.data.orderInfo.order_id,
      success: function() {
        wx.showToast({
          title: '复制成功',
        })
      },
      fail: function(res) {
        console.log(res);
      }
    })
  },

  /**
   * 用户申请退款
   */
  orderRefund: function() {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '是否要申请退款？',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.siteroot + 'fangte/orderRefund',
            method: 'POST',
            data: {
              orderid: that.data.orderInfo.order_id,
              userid: app.globalData.userID
            },
            success: function(res) {
              if (res.statusCode == 200 && res.data.code == "200") {
                wx.showToast({
                  title: '申请成功',
                  duration: 800
                })
                let orderInfo = that.data.orderInfo;
                orderInfo.status = 8;
                orderInfo.status_conv = '申请退款中';
                that.setData({
                  orderInfo: orderInfo
                })
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
              }
            }
          })
        }
      }
    })
  },

})