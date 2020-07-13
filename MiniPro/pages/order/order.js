const app = getApp();
var util = require('../../utils/util.js');
var action = require('../../utils/action.js');

Page({

  data: {
    activeIndex: 0,
    orderList: [{
      state: 0, // 全部订单
      pageNum: 0,
      list: []
    }, {
      state: 1, // 待付款
      pageNum: 0,
      list: []
    }, {
      state: 2, // 待收货
      pageNum: 0,
      list: []
    }, {
      state: 3, // 待评价
      pageNum: 0,
      list: []
    }],
    tabs: ['全部订单', '待付款', '待收货', '待评价'], // 菜单列表
  },

  /**
   * 会传递一个参数 那就是state
   * 这里的state == activeIndex
   */
  onLoad: function(options) {
    // 页面显示
    var that = this;
    let activeIndex = options.state;
    wx.getSystemInfo({
      success: function(res) {
        console.log(res)
        that.setData({
          sliderLeft: 0,
          sliderOffset: res.windowWidth / that.data.tabs.length * activeIndex,
          sliderWidth: res.windowWidth / that.data.tabs.length,
          activeIndex: activeIndex
        });
      }
    });
    let orderList = that.data.orderList[activeIndex];
    that.getOrderList(orderList.state, orderList.pageNum);
  },

  /**
   * 获取订单列表
   */
  getOrderList: function(state, pageNum) {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('order/getOrderList', {
      state: state,
      pageNum: pageNum,
      uid: 883
    }).then(res => {
      if (!res) {
        wx.showToast({
          title: '没有更多了',
          icon: 'loading',
          duration: 1000
        })
        return;
      }
      let orderList = that.data.orderList;
      orderList[state].list = orderList[state].list.concat(res);
      orderList[state].pageNum = pageNum + 1;
      that.setData({
        orderList: orderList
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
   * 在这个界面进行数据重渲染
   */
  onShow: function() {

  },

  /**
   * TAB切换
   */
  tabClick: function(evt) {
    var that = this;
    that.setData({
      sliderOffset: evt.currentTarget.offsetLeft,
      activeIndex: evt.currentTarget.id
    });
    // tab切换的时候 pagenum如果不为0 则不进行数据渲染
    let orderList = that.data.orderList[evt.currentTarget.id];
    if (orderList.pageNum == 0 && orderList.list == 0) {
      that.getOrderList(orderList.state, orderList.pageNum);
    }
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    var that = this;
    let orderList = that.data.orderList;
    let activeIndex = that.data.activeIndex;
    orderList[activeIndex].pageNum = 0;
    orderList[activeIndex].list = [];
    that.setData({
      orderList: orderList
    })
    that.getOrderList(orderList[activeIndex].state, orderList[activeIndex].pageNum);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var that = this;
    let orderList = that.data.orderList[that.data.activeIndex];
    that.getOrderList(orderList.state, orderList.pageNum);
  },

  /**
   * 微信支付请求
   */
  createWxpay: function(evt) {
    console.log(evt)
    action.createWxpay(evt.currentTarget.dataset.ordersn, 2);
  },

  /**
   * 跳转到订单详情界面
   */
  orderDetail: function(evt) {
    wx.navigateTo({
      url: '/pages/orderdetail/orderdetail?ordersn=' + evt.currentTarget.dataset.ordersn
    })
  },

  /**
   * 跳转到订单评价界面
   * 构造本地商品缓存传递过去
   */
  navToComment: function(evt) {
    var that = this;
    let curOrder = that.data.orderList[that.data.activeIndex].list[evt.currentTarget.dataset.idx];
    let curOrderDetail = curOrder.detail;
    // 跳转到评价界面
    try {
      wx.setStorageSync('orderGoods', curOrderDetail);
    } catch (e) {
      util.modalPromisified({
        title: '系统提示',
        content: '内存不足，暂时无法操作',
        showCancel: false
      })
      return;
    }
    wx.navigateTo({
      url: '/pages/comment/comment?ordersn=' + curOrder.order_sn
    })
  },

  /**
   * 订单取消操作
   */
  cancelOrder: function(evt) {
    var that = this;
    action.cancelOrder(evt.currentTarget.dataset.ordersn).then(res => {
      let orderList = that.data.orderList;
      let activeIndex = that.data.activeIndex;
      orderList[activeIndex].pageNum = 0;
      orderList[activeIndex].list = [];
      that.setData({
        orderList: orderList
      })
      that.getOrderList(orderList[activeIndex].state, orderList[activeIndex].pageNum);
    }).catch(res => {
      console.log(res)
    })
  },

  /**
   * 订单评价操作
   */
  orderRemark: function(evt) {
    var that = this;

  }

})