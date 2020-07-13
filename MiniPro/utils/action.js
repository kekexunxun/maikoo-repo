// util js引入
var util = require('./util.js');
var app = getApp();

function addToCart(goodsid) {
  util.modalPromisified({
    title: '系统提示',
    content: '您确定要将当前商品添加到购物车吗？'
  }).then(res => {
    if (res.confirm) {
      wx.showLoading({
        title: '请稍等...',
        mask: true
      })
      util.post('cart/addToCart', {
        openid: wx.getStorageSync('openid'),
        goodsid: goodsid
      }).then(res => {
        wx.showToast({
          title: '添加成功',
          duration: 1200
        })
      }).catch(res => {
        util.modalPromisified({
          title: '系统提示',
          content: '添加失败，网络错误，请稍后再试',
          showCancel: false
        })
      }).finally(res => {})
    }
  })
}

/**
 * ordersn 订单编号
 * totalfee 订单总额
 * scene 进入场景 1 支付界面 2 订单列表界面 3 订单详情界面
 */
function createWxpay(ordersn, scene) {
  wx.showLoading({
    title: '支付请求中...',
    mask: true
  })
  util.post('wxpay/createWxpay', {
    openid: wx.getStorageSync('openid'),
    ordersn: ordersn,
    uid: app.globalData.uid
    // uid: 883
  }, 200).then(res => {
    // 发起微信支付
    wx.requestPayment({
      timeStamp: res.timeStamp,
      nonceStr: res.nonce_str,
      package: 'prepay_id=' + res.prepay_id,
      signType: 'MD5',
      paySign: res.paySign,
      success: res => {
        console.log(res)
        // 支付成功
        wx.showToast({
          title: '支付成功',
          mask: true,
          duration: 600
        })
        setTimeout(res => {
          util.post('wxpay/checkWxpay', {
            ordersn: ordersn,
            uid: app.globalData.uid
          }, 200).then(res => {
            wx.showToast({
              title: '支付校验成功',
              mask: true,
              duration: 800
            })
            if (scene == 1) {
              setTimeout(res => {
                wx.navigateTo({
                  url: '/pages/order/order'
                })
              }, 800)
            } else if (scene == 3) {
              that.getOrderInfo(ordersn)
            }
          }).catch(res => {
            util.modalPromisified({
              title: '系统提示',
              content: '支付校验失败，请及时联系管理员',
              showCancel: false
            }).then(res => {
              if (scene == 1) {
                wx.navigateTo({
                  url: '/pages/order/order'
                })
              }
            })
          }).finally(res => {})
        }, 600)
      },
      fail: res => {
        console.log(res)
        wx.hideLoading();
        util.modalPromisified({
          title: '系统提示',
          content: '支付失败，请到我的订单中查看',
          showCancel: false
        }).then(res => {
          if (scene == 1) {
            wx.navigateTo({
              url: '/pages/order/order'
            })
          }
        })
      },
      complete: res => {
        // 兼容微信6.5.2版本
        if (res.errMsg == 'requestPayment:cancel') {
          wx.hideLoading();
          // 微信支付失败处理
          util.modalPromisified({
            title: '系统提示',
            content: '支付失败，请到我的订单中查看',
            showCancel: false
          }).then(res => {
            if (scene == 1) {
              wx.navigateTo({
                url: '/pages/order/order'
              })
            }
          })
        }
      }
    })
  }).catch(res => {
    util.modalPromisified({
      title: '系统提示',
      content: '网络错误，请到我的订单中完成下一步操作',
      showCancel: false
    }).then(res => {
      if (scene == 1) {
        wx.navigateTo({
          url: '/pages/order/order'
        })
      }
    })
  }).finally(res => {})
}

function cancelOrder(ordersn) {
  var promise = new Promise((resolve, reject) => {
    util.modalPromisified({
      title: '系统提示',
      content: '您确认要取消订单吗？'
    }).then(res => {
      if (res.confirm) {
        wx.showLoading({
          title: '处理中...',
          mask: true
        })
        util.post('order/cancelOrder', {
          uid: app.globalData.uid,
          ordersn: ordersn
        }, 400).then(res => {
          wx.showToast({
            title: '取消成功'
          })
          setTimeout(res => {
            resolve('success');
          }, 800)
        }).catch(res => {
          util.modalPromisified({
            title: '系统提示',
            content: '网络错误，请稍后再试',
            showCancel: false
          })
          reject('failed');
        }).finally(res => {})
      } else {
        reject('cancel');
      }
    })
  })
  return promise;
}

module.exports = {
  addToCart: addToCart,
  createWxpay: createWxpay,
  cancelOrder: cancelOrder
}