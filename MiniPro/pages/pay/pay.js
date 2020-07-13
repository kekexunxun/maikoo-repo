var app = getApp();
var util = require('../../utils/util.js');
var action = require('../../utils/action.js');
var QQMapWX = require('../../utils/qqmap-wx-jssdk.js');
var qqmapsdk;

Page({

  data: {
    totalFee: "", // 商品总价
    couponFee: '0.00', // 优惠券优惠金额
    isCanSubmitOrder: true, // 防止用户下单多次点击
    isCanNavToCoupon: true, // 防止用户跳转卡券界面多次点击
    isShowGoodsDetail: false, // 是否展示商品详情
    isCart: false, //是否是从购物车进入
    addressAuth: true, // 用户地址授权是否拥有
    isCanChooseAddress: true,
    multiIndex: [0, 0], // 送达时间选择
  },

  onLoad: function(options) {
    var that = this;
    // 从缓存中提取商品信息
    var goodsInfo = wx.getStorageSync('goodsInfo') || "";
    if (!goodsInfo) {
      util.modalPromisified({
        title: '系统提示',
        content: '数据错误，请检查系统内存是否充足',
        showCancel: false
      }).then(res => {
        wx.navigateBack({
          delta: 1
        })
      })
      return;
    }

    // 实例化QQMAP类
    qqmapsdk = new QQMapWX({
      key: 'OYKBZ-4J2C3-DJT3F-Y7ZNF-7C2RH-NSBSV'
    });


    // 获取传递过来的商品总数及总价
    let isNeedLogiFee = app.globalData.setting.logi_free_fee * 100 - options.goodsFee * 100;
    let totalFee = isNeedLogiFee > 0 ? (options.goodsFee * 100 + app.globalData.setting.logi_fee * 100) : options.goodsFee * 100;

    // let totalFee = 2200;
    // let isNeedLogiFee = 0;
    that.setData({
      totalFee: parseFloat(totalFee / 100).toFixed(2),
      totalNum: options.totalNum,
      goodsInfo: goodsInfo,
      isCart: options.isCart, // 是否是从购物车界面进入
      logiFee: isNeedLogiFee > 0 ? app.globalData.setting.logi_fee : '0.00',
      goodsFee: options.goodsFee
    })
    // 删除商品缓存
    wx.removeStorageSync('goodsInfo');
    // 构造配送
    that.setDeliveryTime();
    // that.getMchAround();
  },

  /**
   * 这里主要是获取用户选择的优惠券
   */
  onShow: function() {
    var that = this;
    let couponInfo = wx.getStorageSync('couponInfo') || "";
    if (couponInfo) {
      let totalFee = that.data.totalFee * 100;
      couponFee = couponInfo.money * 100;
      // 更新当前商品总价
      totalFee = totalFee - couponFee > 0 ? totalFee - couponFee : 0;
      that.setData({
        totalFee: parseFloat(totalFee / 100).toFixed(2),
        couponFee: parseFloat(couponFee / 100).toFixed(2),
        couponId: couponInfo.coupon_id
      })
      wx.removeStorageSync('couponInfo');
    }
    that.setData({
      isCanNavToCoupon: true
    })
  },

  /**
   * 配送时间构造
   */
  setDeliveryTime: function() {
    var that = this;
    var d = new Date();
    let hour = d.getHours();
    let minute = d.getMinutes();

    var deliveryArr = [];
    var temp = [];
    var todayTimeArr = [];
    var tomorrowTimeArr = [];
    if (hour < 22) {
      temp = ['今日配送', '明日配送'];
      todayTimeArr = that.genTimeSection(hour, minute);
      tomorrowTimeArr = that.genTimeSection(7, 30);
    } else {
      util.modalPromisified({
        title: '系统提示',
        content: '已过今日配送时间，最快配送时间为明早7:30',
        showCancel: false
      })
      temp = ['明日配送'];
      todayTimeArr = that.genTimeSection(7, 30);
    }
    deliveryArr.push(temp);
    deliveryArr.push(todayTimeArr);
    that.setData({
      multiArray: deliveryArr,
      tomorrowTimeArr: tomorrowTimeArr,
      todayTimeArr: todayTimeArr
    })
  },

  genTimeSection: function(hour, minute) {
    var todayTimeArr = [];
    // 假设时间段为7:30 - 22:00
    if (hour < 22) {
      if (minute > 30) {
        hour = hour + 1;
        minute = "00";
      } else {
        minute = 30;
      }
      hour = hour > 7 ? hour : 7;
      minute = hour > 7 ? minute : 30;
      for (var i = hour; i < 22; i++) {
        hourTemp = i;
        let curTimeStr = hourTemp + ':' + minute;
        if (minute == 30) {
          minute = "00";
          hourTemp = hourTemp + 1;
        } else {
          minute = 30;
        }
        let nextTimeStr = hourTemp + ':' + minute;
        if (minute == 30) {
          minute = "00";
          hourTemp = hourTemp + 1;
        } else {
          minute = 30;
        }
        todayTimeArr.push(curTimeStr + ' - ' + nextTimeStr);
        if (hourTemp == 22) {
          break;
        } else {
          nextNextTimeStr = hourTemp + ':' + minute;
          todayTimeArr.push(nextTimeStr + ' - ' + nextNextTimeStr)
        }
      }
    }
    return todayTimeArr;
  },

  /**
   * 是否展示商品详情
   */
  showGoodsDetail: function() {
    var that = this;
    that.setData({
      isShowGoodsDetail: !that.data.isShowGoodsDetail
    })
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
    util.addressPromisified().then(res => {
      that.setData({
        userName: res.userName,
        telNumber: res.telNumber,
        address: res.provinceName + res.cityName + res.countyName,
        addressDetail: res.detailInfo,
      })
      that.getMchAround(res.provinceName + res.cityName + res.countyName + res.detailInfo);
    }).catch(res => {
      util.modalPromisified({
        title: '操作提示',
        content: '需要您授权才可获取地址',
        showCancel: false
      }).then(res => {
        that.setData({
          addressAuth: false
        })
      })
    }).finally(res => {
      that.setData({
        isCanChooseAddress: true
      })
    })
  },

  /**
   * 获取用户地址选择的指定商家
   * 
   * @param string locaiton 用户所选取的地址
   */
  getMchAround: function(address) {
    var that = this;
    // 先用qqmap解码位置信息再将该经纬度发送至后台进行数据判断
    // qqmapsdk.geocoder({
    //   address: address,
    //   success: res => {
    //     console.log(res);
    //     wx.showLoading({
    //       title: '商家获取中...',
    //       mask: true
    //     })
    //     util.post('minibase/getMchAround', {
    //       lat: res.result.location.lat,
    //       lng: res.result.location.lng,
    //       uid: 883
    //     }, 300).then(res => {
    //       console.log(res)
    //     }).catch(res => {
    //       console.log(res)
    //     }).finally(res => {
    //       wx.hideLoading();
    //       wx.hideNavigationBarLoading()
    //     })
    //   },
    //   fail: res => {
    //     console.log(res)
    //   }
    // })
  },

  /**
   * 检测用户地址授权
   */
  checkUserAuthSetting: function(res) {
    var that = this;
    if (res.detail.authSetting['scope.address']) {
      wx.showToast({
        title: '授权成功'
      })
      that.setData({
        addressAuth: true
      })
    } else {
      wx.showToast({
        title: '授权失败',
        icon: 'none'
      })
    }
  },

  /**
   * 填写备注
   */
  inputMessage: function(e) {
    // var that = this;
    // 要做一个简单的去空格处理
    this.setData({
      message: e.detail.value
    })
  },

  /**
   * picker中配送时间切换
   */
  bindLogiTimeChange: function(evt) {
    var that = this;
    let multiArray = that.data.multiArray;
    if (evt.detail.column == 0) {
      multiArray[1] = evt.detail.value == 0 ? that.data.todayTimeArr : that.data.tomorrowTimeArr;
    }
    that.setData({
      multiArray: multiArray
    })
  },

  /**
   * 用户确认选择配送时间
   */
  bindLogiTimeSelect: function(evt) {
    var that = this;
    let multiIndex = [];
    multiIndex[0] = evt.detail.value[0];
    multiIndex[1] = evt.detail.value[1];
    that.setData({
      multiIndex: multiIndex
    })
  },

  /**
   * 跳转到用户选择卡券界面
   */
  navToCoupon: function(e) {
    var that = this;
    if (!that.data.isCanNavToCoupon) {
      return;
    } else {
      that.setData({
        isCanNavToCoupon: false
      })
      wx.navigateTo({
        url: '/pages/coupon/coupon?way=2&totalFee=' + that.data.totalFee
      })
    }
  },

  /**
   * 用户下单
   */
  makeOrder: function(e) {
    var that = this;
    if (!that.data.isCanSubmitOrder) {
      return;
    } else {
      that.setData({
        isCanSubmitOrder: false
      })
    }
    if (!that.data.address) {
      util.modalPromisified({
        title: '操作提示',
        content: '提交失败，请选择配送地址',
        showCancel: false
      }).then(res => {
        that.setData({
          isCanSubmitOrder: true
        })
      })
      return;
    }
    wx.showLoading({
      title: '下单中...',
      mask: true
    })
    // 构造商品详情
    let goodsInfo = that.data.goodsInfo;
    let goodsList = [];
    for (let i = 0; i < goodsInfo.length; i++) {
      goodsList.push({
        goods_id: goodsInfo[i].goodsid,
        quantity: goodsInfo[i].quantity,
        fee: goodsInfo[i].shop_price
      })
    }
    // 构造配送参数进行下单
    util.post('order/makeOrder', {
      uid: app.globalData.uid,
      totalFee: that.data.totalFee,
      couponId: that.data.couponId || "",
      couponFee: that.data.couponFee,
      logiFee: that.data.logiFee,
      userName: that.data.userName,
      phone: that.data.telNumber,
      address: that.data.address + that.data.addressDetail,
      message: that.data.message || "",
      timestamp: parseInt(Date.now() / 1000),
      delivery: that.data.multiArray[0][that.data.multiIndex[0]] + that.data.multiArray[1][that.data.multiIndex[1]],
      detail: goodsList
    }).then(res => {
      wx.showToast({
        title: '下单成功',
        duration: 800,
        mask: true
      })
      // 跳转至微信支付
      setTimeout(function() {
        if (that.data.totalFee == 0) {
          that.confirmOrder(res.order_sn);
        } else {
          that.createWxpay(res.order_sn);
        }
      }, 800)
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '部分商品库存不足，下单失败',
        showCancel: false
      })
    }).finally(res => {
      that.setData({
        isCanSubmitOrder: true
      })
    })
  },

  /**
   * 微信支付请求
   */
  createWxpay: function(ordersn) {
    action.createWxpay(ordersn, 1);
  },

  /**
   * 当订单价格为零的时候直接更新订单状态
   */
  confirmOrder: function(ordersn) {
    var that = this;
    wx.showLoading({
      title: '订单确认中',
      mask: true
    })
    util.post('order/checkOrderStatus', {
      uid: app.globalData.uid,
      ordersn: ordersn
    }).then(res => {
      wx.showToast({
        title: '订单确认成功',
        mask: true
      })
      setTimeout(res => {
        wx.navigateTo({
          url: '/pages/order/order',
        })
      }, 1200)
    }).catch(res => {
      wx.showToast({
        title: '订单确认失败',
        icon: 'loading',
        mask: true
      })
    });
  }

})