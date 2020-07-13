const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    columnList: [], // 专栏列表
  },

  onLoad: function(options) {

  },

  /**
   * 获取会员界面的专栏数据
   */
  getMemColumnList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('column/getMemColumn', {
      uid: app.globalData.uid
    }).then(res => {
      that.setData({
        columnList: res
      })
    }).catch(res => {
      wx.showModal({
        title: '网络错误',
        content: '请下拉刷新重试',
        showCancel: false
      })
    }).finally(res => {})
  },

  /**
   * 用户会员状态监测
   */
  onShow: function() {
    if (!app.globalData.isMember) {
      util.modalPromisified({
        title: '系统提示',
        content: '此界面为飞天小猪会员专属购买界面，您还不是会员，点击按钮立即成为会员吧',
        confirmText: '成为会员'
      }).then(res => {
        if (res.confirm) {
          wx.navigateTo({
            url: '/pages/memberpay/memberpay'
          })
        } else {
          wx.switchTab({
            url: '/pages/index/index'
          })
        }
        // wx.switchTab({
        //   url: '/pages/index/index'
        // })
      })
      return;
    }
    if (this.data.columnList.length == 0) {
      this.getMemColumnList();
    }
  },

  /**
   * 跳转到商品详情
   */
  navToGoods: function(evt) {
    wx.navigateTo({
      url: '/pages/goodsdetail/goodsdetail?goodsid=' + evt.currentTarget.dataset.goodsid,
    })
  },

  /**
   * 跳转到商品版块详情
   */
  navToColumn: function(evt) {
    let dataset = evt.currentTarget.dataset;
    wx.navigateTo({
      url: '../goodslist/goodslist?type=1&columnid=' + dataset.columnid + '&colname=' + dataset.colname,
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.getMemColumnList();
  },


  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }

})