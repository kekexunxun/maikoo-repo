var app = getApp();
var util = require('../../utils/util.js');
var action = require('../../utils/action.js');

Page({


  data: {
    goodsList: [], // 商品列表
    pageNum: 0, // 请求的页码数
  },

  /**
   * 专栏 和 分类 会使用到这里
   * 规定其跳转时传type 分别为 1 和 2
   */
  onLoad: function(options) {
    var that = this;
    // 请求的url
    var url = "";
    // 请求的data
    var data = null;
    // 如果type = 1 表明是今日特价
    if (options.type == 1) {
      url = 'column/getColumnGoods';
      data = {
        uid: 123,
        columnid: options.colid,
        pageNum: that.data.pageNum
      }
    } else if (options.type == 2) {
      url = "catagory/getCatagorySpec";
      data = {
        uid: 123,
        catid: options.catid,
        pageNum: that.data.pageNum
      }
    }
    that.setData({
      reqUrl: url,
      reqData: data
    })
    // 数据请求
    that.goodsRequest();
  },

  /**
   * 数据请求
   */
  goodsRequest: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    let reqData = that.data.reqData;
    util.post(that.data.reqUrl, reqData).then(res => {
      if (res) {
        reqData.pageNum = reqData.pageNum + 1;
        that.setData({
          goodsList: that.data.goodsList.concat(res.goodsList),
          reqData: reqData
        })
        // 如果是专栏
        if (res.info.column_name) {
          // 动态设置navBarTitle
          wx.setNavigationBarTitle({
            title: res.info.column_name
          });
          wx.setNavigationBarColor({
            frontColor: '#ffffff',
            backgroundColor: res.info.column_color,
            animation: {
              duration: 800,
              timingFunc: 'linear'
            }
          })
          that.setData({
            columnInfo: res.info
          })
        } else {
          // 如果是分类
          wx.setNavigationBarTitle({
            title: res.info
          });
        }
      } else {
        if (that.data.pageNum != 0 || !res.goodsList || res.goodsList.length == 0) {
          wx.showToast({
            title: '没有更多啦',
            icon: 'loading',
            duration: 1000
          })
        }
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
   * 添加商品到购物车
   */
  addToCart: function(evt) {
    var goodsid = evt.currentTarget.dataset.goodsid;
    action.addToCart(goodsid);
  },

  /**
   * 跳转到商品详情
   */
  navToGoods: function(evt) {
    wx.navigateTo({
      url: '../goodsdetail/goodsdetail?goodsid=' + evt.currentTarget.dataset.goodsid
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    var that = this;
    let reqData = that.data.reqData;
    reqData.pageNum = 0;
    that.setData({
      reqData: reqData
    })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    this.goodsRequest();
  },


})