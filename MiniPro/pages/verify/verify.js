var app = getApp();

Page({

  data: {
    verifyCode: "", // 核销码
    orderDetail: [], // 订单详情
    notVerify: true, // 当前订单状态
  },

  /**
   * 可通过扫描二维码或者输入核销码进入该界面
   */
  onLoad: function(options) {
    var that = this;
    // 先等待系统数据加载完
    let timeCount = 0;
    let intval = setInterval(res => {
      if (app.globalData.userID) {
        clearInterval(intval);

        // 如果用户不是管理员 直接提示退出
        if (!app.globalData.isAdmin) {
          wx.showModal({
            title: '系统提示',
            content: '您还不是管理员，请您联系管理员询问相关事项',
            showCancel: false,
            success: function() {
              wx.redirectTo({
                url: '/pages/index/index'
              })
            }
          })
        }

        // 如果当前用户是管理员 则继续判断
        if (options.orderid || options.scene) {
          let orderId = options.orderid;
          if (options.scene) {
            let scene = decodeURIComponent(options.scene);
            let sceneArr = scene.split('=');
            orderId = sceneArr[1];
          }
          that.setData({
            orderId: orderId
          })
          that.getOrderByOrderId();
        } else {
          wx.showModal({
            title: '系统提示',
            content: '参数错误',
            showCancel: false,
            success: function() {
              wx.redirectTo({
                url: '/pages/index/index'
              })
            }
          })
          return;
        }

      } else {
        timeCount++;
        if (timeCount == 30) {
          clearInterval(intval);
          wx.showModal({
            title: '系统提示',
            content: '网络错误，请检查网络是否有效',
            showCancel: false,
            success: function(res) {
              wx.redirectTo({
                url: '/pages/index/index'
              })
            }
          })
        }
      }
    }, 100)
  },

  /**
   * 通过订单号去获取订单详情
   */
  getOrderByOrderId: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'fangte/getVerifyOrder',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        orderid: that.data.orderId
      },
      success: function(res) {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == "200") {
          // 简单的数据处理
          that.setData({
            orderDetail: res.data.data
          })
        } else if (res.data.code == "201") {
          wx.showModal({
            title: '系统提示',
            content: '当前订单已被用户确认完成或已被核销或已到期',
            showCancel: false,
            success: function() {
              wx.navigateBack({
                delta: 1
              })
            }
          })
        } else {
          wx.showModal({
            title: '系统提示',
            content: '网络错误，请尝试下拉刷新重新加载',
            showCancel: false
          })
        }
      },
      fail: function() {
        wx.hideLoading();
        wx.showModal({
          title: '系统提示',
          content: '网络错误，请尝试下拉刷新重新加载',
          showCancel: false
        })
      },
      complete: function() {
        wx.stopPullDownRefresh();
      }
    })
  },

  /**
   * 确认核销
   */
  confirmVerify: function() {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '确认核销当前订单吗？',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '核销中',
            mask: true
          })
          wx.request({
            url: app.globalData.siteroot + 'fangte/confirmVerify',
            method: 'POST',
            dataType: 'json',
            data: {
              openid: wx.getStorageSync('openid'),
              userid: app.globalData.userID,
              orderid: that.data.orderId
            },
            success: function(res) {
              wx.hideLoading();
              if (res.statusCode == 200 && res.data.code == "200") {
                // 简单的数据处理
                wx.showToast({
                  title: '核销成功',
                  duration: 1000
                })
                that.setData({
                  notVerify: false
                })
              }else if(res.data.code == "201"){
                wx.showModal({
                  title: '系统提示',
                  content: '订单状态已改变，请刷新界面后重试',
                  showCancel: false
                })
              } else {
                wx.showModal({
                  title: '系统提示',
                  content: '网络错误，核销失败',
                  showCancel: false
                })
              }
            },
            fail: function() {
              wx.hideLoading();
              wx.showModal({
                title: '系统提示',
                content: '网络错误，核销失败',
                showCancel: false
              })
            },
            complete: function() {}
          })
        }
      }
    })
  },

  onPullDownRefresh: function() {
    this.getOrderByOrderId();
  }

})