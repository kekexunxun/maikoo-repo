var app = getApp();
var util = require('../../utils/util.js');
var check = require('../../utils/check.js');
var action = require('../../utils/action.js');

Page({

  data: {
    goodsInfo: [], // 商品信息
    goodsId: "", // 商品ID
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    }
  },

  onLoad: function(options) {
    if (!options.goodsid) {
      util.modalPromisified({
        title: '系统提示',
        content: '系统需要获取您的地址',
      }).then(function(res) {
        if (res.confirm) {
          wx.redirectTo({
            url: '/pages/index/index'
          })
        }
      })
    } else {
      this.setData({
        goodsId: options.goodsid
      })
      this.getGoodsInfo();
      // 根据不同用户的手机获取屏幕进行自适应匹配
      this.setScreen();
      this.setData({
        logiFreeFee: app.globalData.setting.logi_free_fee ? "满" + app.globalData.setting.logi_free_fee + '元免运费' : '免运费'
      })
    }
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
   * 根据屏幕长度设置detail-container的高度
   */
  setScreen: function() {
    var that = this;
    wx.getSystemInfo({
      success: function(res) {
        console.log(res)
        that.setData({
          detailHeight: res.windowHeight - parseInt(105 / res.pixelRatio)
        })
      }
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
   * 获取商品详情
   * 需等待系统获取用户信息方法完成之后再进行获取商品详情操作
   */
  getGoodsInfo: function() {
    var that = this;
    wx.showNavigationBarLoading();
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    check.checkLoginState().then(res => {
      return util.post('goods/getGoodsInfo', {
        uid: app.globalData.uid,
        goodsid: that.data.goodsId
      })
    }).then(res => {
      that.setData({
        goodsInfo: res
      })
      // 设置navBarText
      wx.setNavigationBarTitle({
        title: res.goods_name
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '请检查网络连接是否正常',
        showCancel: false
      })
    }).finally(res => {})
  },

  /**
   * 商品详情查看
   */
  showImagePreview: function(e) {
    console.log(e)
    var that = this;
    wx.previewImage({
      urls: that.data.goodsInfo.goods_desc,
      current: e.currentTarget.dataset.img
    })
  },

  /**
   * 将商品添加到购物车
   */
  addToCart: function(evt) {
    action.addToCart(this.data.goodsId);
    // 更新cartprice
    app.globalData.cartPrice = ((app.globalData.cartPrice * 100 + this.data.goodsInfo.shop_price * 100) / 100).toFixed(2);
    let cartData = this.data.cartData;
    cartData.money = app.globalData.cartPrice;
    this.setData({
      cartData: cartData
    })
  },

  /**
   * 将该商品添加到收藏
   */
  addToFav: function() {
    var that = this;
    var goodsInfo = that.data.goodsInfo;
    var favAction = goodsInfo.isFav ? 0 : 1;
    var content = favAction ? '您确定要收藏该商品吗？' : '您确定要取消收藏该商品吗？';
    util.modalPromisified({
      title: '系统提示',
      content: content
    }).then(res => {
      if (res.confirm) {
        wx.showLoading({
          title: '请稍等...',
          mask: true
        })
        util.post('user/updateFav', {
          uid: app.globalData.uid,
          goodsid: goodsInfo.goods_id,
          favaction: favAction
        }).then(res => {
          let title = favAction ? '收藏成功' : '取消收藏成功';
          wx.showToast({
            title: title,
            duration: 1000
          })
          goodsInfo.isFav = favAction ? true : false;
          that.setData({
            goodsInfo: goodsInfo
          })
        }).catch(res => {
          util.modalPromisified({
            title: '系统提示',
            content: '网络错误，收藏失败',
            showCancel: false
          })
        }).finally(res => {})
      }
    }).catch()
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.getGoodsInfo();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    var that = this;
    return {
      title: '这件商品你一定喜欢~',
      path: '/pages/goodsdetail/goodsdetail?goodsid=' + that.data.goodsId
    }
  }
})