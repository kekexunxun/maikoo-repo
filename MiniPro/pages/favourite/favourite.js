var app = getApp();
var util = require('../../utils/util.js');
var action = require('../../utils/action.js');

Page({


  data: {
    goodsList: [], // 商品列表
    pageNum: 0, // 请求的页码数
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    }
  },

  onLoad: function(options) {
    this.getUserFav();
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
   * 数据请求
   */
  getUserFav: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('user/getUserFav', {
      uid: app.globalData.uid,
      pageNum: that.data.pageNum
    }).then(res => {
      if (res) {
        that.setData({
          goodsList: that.data.goodsList.concat(res),
          pageNum: that.data.pageNum + 1
        })
      } else {
        wx.showToast({
          title: '没有更多啦',
          icon: 'loading',
          duration: 1000
        })
      }
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请稍后再试',
        showCancel: false
      })
    }).finally(res => {})
  },

  /**
   * 跳转到商品详情页
   */
  navToGoods: function(evt) {
    wx.navigateTo({
      url: '/pages/goodsdetail/goodsdetail?goodsid=' + evt.currentTarget.dataset.goodsid
    })
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
   * 用户删除收藏
   */
  delFav: function(evt) {
    var that = this;
    util.modalPromisified({
      title: '系统提示',
      content: '您确定要取消收藏当前商品吗？'
    }).then(res => {
      if (res.confirm) {
        wx.showLoading({
          title: '删除中',
          mask: true
        })
        util.post('user/updateFav', {
          uid: app.globalData.uid,
          goodsid: evt.currentTarget.dataset.goodsid,
          favaction: 0
        }).then(res => {
          wx.showToast({
            title: '取消收藏成功',
            duration: 1000
          })
          // 更新当前列表
          let goodsList = that.data.goodsList;
          goodsList.splice(evt.currentTarget.dataset.idx, 1);
          that.setData({
            goodsList: goodsList
          })
        }).catch(res => {
          util.modalPromisified({
            title: '系统提示',
            content: '网络错误，请重新尝试',
            showCancel: false
          })
        }).finally(res => {})
      }
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.setData({
      pageNum: 0,
      goodsList: []
    })
    this.getUserFav();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    this.getUserFav();
  },


})