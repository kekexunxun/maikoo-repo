var app = getApp();
Page({

  data: {
    totalFee: "", //商品总价
    totalFeeOri: '', //商品总价 （不计快递）
    LogiModalHidden: true, //是否展示快递的modal
    isDoPayAction: false, //是否完成了支付操作
    isCanSubmitOrder: true, //防止用户多次点击
    goodsList: [], //商品列表
    itemCount: 1, // 默认商品数量为1
    isCart: false, //是否是从购物车进入
    addressAuth: true, // 用户地址授权是否拥有
    expressFee: 0, // 运费总价
    isCanChooseAddress: true
  },

  onLoad: function(options) {
    console.log(options)
    var that = this;
    // 判断是购物车购买还是直接购买
    let isCart = options.isCart;
    let goodsList = wx.getStorageSync('goodsItem');
    if (!goodsList) {
      wx.showModal({
        title: '系统提示',
        content: '参数错误，请检查系统内存是否充足',
        showCancel: false,
        success: function(res) {
          wx.navigateBack({
            delta: 1
          })
        }
      })
      return;
    }

    // 当前商品总数
    let itemCount = that.getGoodsCount(goodsList);

    // 设置当前界面数据
    that.setData({
      isCart: options.isCart,
      goodsList: goodsList,
      goodsFee: options.totalFee,
      // 在初始化时才会获取一次商品总数量
      itemCount: itemCount
    })

    // 清理商品缓存
    wx.removeStorageSync('goodsItem');

    // 处理邮费和总价
    that.setTotalFee();
  },

  /**
   * 构造订单界面商品详情
   */
  getGoodsCount: function(goodsList) {
    var itemCount = 0;
    for (var i = 0; i < goodsList.length; i++) {
      itemCount += goodsList[i].quantity
    }
    return itemCount;
  },

  /**
   * 选择地址 - 需用户scope.address授权
   */
  chooseAddress: function() {
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
        // console.log(res)
        that.setData({
          userName: res.userName,
          telNumber: res.telNumber,
          address: res.provinceName + res.cityName + res.countyName,
          addressDetail: res.detailInfo,
        })
      },
      fail: function() {
        wx.getSetting({
          success: res => {
            if (!res.authSetting['scope.address']) {
              wx.showModal({
                title: '操作提示',
                content: '需要您授权才可获取地址',
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

  checkUserAuthSetting: function(res) {
    var that = this;
    if (res.detail.authSetting['scope.address']) {
      wx.showToast({
        title: '授权成功',
      })
      that.setData({
        addressAuth: true
      })
    } else {
      wx.showToast({
        title: '授权失败',
      })
    }
  },

  /**
   * 打开快递Modal
   */
  chooseLogi: function() {
    this.setData({
      LogiModalHidden: false
    })
  },

  /**
   * 填写备注
   */
  inputMessage: function(e) {
    var that = this;
    that.setData({
      message: e.detail.value
    })
  },

  /**
   * 提交订单
   */
  prePay: function(orderid) {
    var that = this;
    // 请求支付loading
    wx.showLoading({
      title: '支付请求中',
      mask: true
    })
    // 支付请求
    if (that.data.totalFee == 0) {
      // 写个TimeOut免得顾客误操作
      that.finishPay(orderid);
    } else {
      // 产生预支付订单
      setTimeout(function() {
        wx.request({
          url: app.globalData.siteroot + 'wxpay/createWxPay',
          method: 'POST',
          dataType: 'json',
          data: {
            openid: wx.getStorageSync('openid'),
            orderid: orderid
          },
          success: function(res) {
            if (res.data.code == "200") {
              wx.requestPayment({
                'timeStamp': res.data.timeStamp,
                'nonceStr': res.data.nonce_str,
                'package': 'prepay_id=' + res.data.prepay_id,
                'signType': 'MD5',
                'paySign': res.data.paySign,
                'success': function() {
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
                        wx.hideLoading();
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
                        }else{
                          wx.showModal({
                            title: '系统提示',
                            content: '支付校验失败，请及时联系管理员',
                            showCancel: false,
                            success: function(){
                              wx.navigateTo({
                                url: '../order/order'
                              })
                            }
                          })
                        }
                      },
                      fail: function(res) {
                        wx.hideLoading();
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
                  wx.hideLoading();
                  wx.showModal({
                    title: '系统提示',
                    content: '支付失败，请到我的订单中继续完成支付',
                    showCancel: false,
                    success: function() {
                      wx.navigateTo({
                        url: '../order/order'
                      })
                    }
                  })
                },
                'complete': function(res) {
                  // wx.hideLoading();
                  if (res.errMsg == "requestPayment:cancel") {
                    // 微信6.5.2版本及以下取消支付回调
                    wx.showModal({
                      title: '系统提示',
                      content: '支付失败，请到我的订单中继续完成支付',
                      showCancel: false,
                      success: function() {
                        wx.navigateTo({
                          url: '../order/order'
                        })
                      }
                    })
                  }
                }
              })
            } else {
              wx.hideLoading();
              wx.showModal({
                title: '支付异常',
                content: "请到我的订单中完成后续支付",
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
            wx.hideLoading();
            wx.showModal({
              title: '支付异常',
              content: "请到我的订单中完成后续支付",
              showCancel: false,
              success: function() {
                wx.navigateTo({
                  url: '../order/order'
                })
              }
            })
          },
          complete: function() {}
        })
      }, 300)
    }
  },

  finishPay: function(orderid) {
    var that = this;
    // 支付成功
    setTimeout(function() {
      wx.request({
        url: app.globalData.siteroot + 'fangte/finishPay',
        method: 'POST',
        dataType: 'json',
        data: {
          orderid: orderid,
          openid: wx.getStorageSync('openid')
        },
        success: function(res) {
          if (res.statusCode == 200 && res.data.code == "200") {
            wx.showToast({
              title: '支付成功',
              mask: true,
              duration: 1000
            })
            setTimeout(function() {
              wx.navigateTo({
                url: '../order/order'
              })
            }, 1000)
          }
        },
        fail: function() {
          wx.hideLoading()
        },
        complete: function() {}
      })
    }, 500)
  },

  /**
   * 下单请求 数据构造
   * @param is_pay 是否支付 order_id 订单号
   */
  makeOrder: function(evt) {
    var that = this;
    if (!that.isCanSubmit()) {
      return;
    }
    wx.showLoading({
      title: '下单中...',
      mask: true
    })

    // 构造下单商品详情
    let goodsDetail = [];
    let goodsList = that.data.goodsList;
    for (let i = 0; i < goodsList.length; i++) {
      goodsDetail.push({
        detail_id: goodsList[i].detail_id,
        goods_id: goodsList[i].goods_id,
        market_price: goodsList[i].market_price,
        shop_price: goodsList[i].is_on_promotion ? goodsList[i].pro_price : goodsList[i].shop_price,
        quantity: goodsList[i].quantity,
        is_distri: goodsList[i].is_distri,
        catagory_id: goodsList[i].catagory_id,
        dis_percent: goodsList[i].dis_percent,
        parent_dis_percent: goodsList[i].parent_dis_percent,
        grand_dis_percent: goodsList[i].grand_dis_percent,
        promotion_id: goodsList[i].is_on_promotion ? goodsList[i].promotion_id : 0
      })
    }
    // 下单请求
    setTimeout(function() {
      wx.request({
        url: app.globalData.siteroot + 'fangte/addOrder',
        method: 'POST',
        dataType: 'json',
        data: {
          openid: wx.getStorageSync('openid'),
          userid: app.globalData.userID,
          totalFee: that.data.totalFee, //商品总价
          userName: that.data.userName || app.globalData.userName,
          telNumber: that.data.telNumber || app.globalData.telNum,
          address: that.data.address ? that.data.address + that.data.addressDetail : '',
          message: that.data.message || '',
          formid: evt.detail.formId,
          goodsDetail: goodsDetail,
          expressFee: that.data.expressFee,
          isCart: that.data.isCart
        },
        success: function(res) {
          wx.hideLoading();
          if (res.data.code == "200") {
            wx.showToast({
              title: '下单成功',
              mask: true,
              duration: 600
            })
            setTimeout(function() {
              that.prePay(res.data.order_id);
            }, 600)
          } else if (res.data.code == "403") {
            wx.showModal({
              title: '下单失败',
              content: '部分商品库存不足，请稍后重试',
              showCancel: false
            })
          } else {
            wx.showModal({
              title: '系统提示',
              content: '下单失败，请稍后重试',
              showCancel: false,
              success: function(res) {
                // wx.navigateBack({
                //   delta: 1
                // })
              }
            })
          }
        },
        fail: function() {
          wx.hideLoading();
          wx.showModal({
            title: '系统提示',
            content: '下单失败，请稍后重试',
            showCancel: false,
            success: function(res) {
              // wx.navigateBack({
              //   delta: 1
              // })
            }
          })
        }
      })
    }, 300)
  },

  /**
   * 判断是否能够进行订单提交
   */
  isCanSubmit: function() {
    var that = this;
    let isCanSubmit = true;
    if (!that.data.address) {
      isCanSubmit = false;
      wx.showModal({
        title: '系统提示',
        content: '地址不能为空',
        showCancel: false
      })
    }
    return isCanSubmit;
  },

  /**
   * 改变当前购物车某件商品的数量
   */
  changeNum: function(e) {
    var that = this;
    var value = e.detail.value;
    if (value == null || isNaN(value)) {
      value = 1;
    }
    // value检测
    value = that.checkValue(value);
    var goodsList = that.data.goodsList;
    goodsList[0].quantity = value;
    that.setData({
      goodsList: goodsList,
      itemCount: value
    })
    that.setTotalFee();
  },

  /**
   * 商品数量增加
   */
  numberPlus: function(e) {
    var that = this;
    var value = that.checkValue(that.data.itemCount + 1);
    var goodsList = that.data.goodsList;
    goodsList[0].quantity = value;
    that.setData({
      goodsList: goodsList,
      itemCount: value
    })
    that.setTotalFee();
  },

  /**
   * 商品数量减少
   */
  numberMinus: function(e) {
    var that = this;
    var value = that.checkValue(that.data.itemCount - 1);
    var goodsList = that.data.goodsList;
    goodsList[0].quantity = value;
    that.setData({
      goodsList: goodsList,
      itemCount: value
    })
    that.setTotalFee();
  },
  /**
   * 检测当前数量是否有效
   */
  checkValue: function(value) {
    var that = this;
    value = parseInt(value);
    if (isNaN(value)) {
      value = 1;
      return value;
    }
    if (value > that.data.goodsList[0].stock) {
      wx.showModal({
        title: '系统提示',
        content: '选择的商品数量不能超过库存数量',
        showCancel: false
      })
      value = that.data.goodsList[0].stock;
    }
    if (value < 1) {
      wx.showModal({
        title: '系统提示',
        content: '不能再减少了哦',
        showCancel: false
      })
      value = 1;
    }
    return value;
  },
  /**
   * 设置当前选中商品总价
   * 并判断与当前邮费的关系
   */
  setTotalFee: function() {
    var that = this;

    let totalFee = that.data.isCart == 1 ? that.data.goodsFee * 100 : that.data.goodsFee * 100 * that.data.itemCount;
    let expressFee = app.globalData.logi_fee * 100;
    let expressFreeFee = app.globalData.logi_free_fee * 100;
    let isNeedExpress = expressFee - totalFee;
    if (isNeedExpress > 0) {
      totalFee = parseFloat((totalFee + expressFee) / 100).toFixed(2);
      expressFee = app.globalData.logi_fee;
    } else {
      expressFee = "0.00";
      totalFee = parseFloat(totalFee / 100).toFixed(2);
    }

    that.setData({
      totalFee: totalFee,
      expressFee: expressFee
    })
  },
})