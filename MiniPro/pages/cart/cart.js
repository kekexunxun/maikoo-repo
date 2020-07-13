var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    cartList: [], // 购物车列表
    iconSelect: "/images/icon-select.png",
    iconNotSelect: "/images/icon-unselect.png",
    isAllSelect: false, // 是否全选
    totalFee: 0.00, // 商品总价
  },

  onLoad: function(options) {
    this.getCartList();
    // 设置包邮金额和面单金额
    this.setData({
      logiFee: parseFloat(app.globalData.setting.logi_fee).toFixed(2),
      logiFreeFee: parseFloat(app.globalData.setting.logi_free_fee).toFixed(2),
      logiFreeFeeOff: parseFloat(app.globalData.setting.logi_free_fee).toFixed(2) // 距离免配送费金额的差值
    })
  },

  /**
   * 获取用户购物车列表
   */
  getCartList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('cart/getCart', {
      openid: wx.getStorageSync('openid')
    }).then(res => {
      that.setData({
        cartList: res.cartList || []
      })
      if (res.isHaveGoodsChange) {
        util.modalPromisified({
          title: '系统提示',
          content: '部分商品状态已改变',
          showCancel: false,
        })
      } else {
        wx.showToast({
          title: '获取成功',
          duration: 1000
        })
      }
    }).catch(res => {
      if (res.code == 401) {
        util.modalPromisified({
          title: '系统提示',
          content: '参数错误，请检查系统内存是否不足',
          showCancel: false,
        })
      } else if (res.code == 501) {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误请稍后重试',
          showCancel: false,
        })
      }
    }).finally(res => {})
  },

  /**
   * 跳转到首页
   */
  navToIndex: function() {
    wx.switchTab({
      url: '/pages/index/index',
    })
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
   * 选中商品
   */
  chooseGoods: function(evt) {
    var that = this;
    var cartList = that.data.cartList;
    cartList[evt.currentTarget.dataset.idx].select = !cartList[evt.currentTarget.dataset.idx].select;
    that.setData({
      cartList: cartList
    })
    // 判断是否全选
    that.isAllSelect();
    // 商品总价更新
    that.setTotalFee();
  },

  /**
   * 商品全选判定
   */
  isAllSelect: function() {
    var that = this;
    var cartList = that.data.cartList;
    var isAllSelect = true;
    for (var i = 0; i < cartList.length; i++) {
      if (!cartList[i].select) {
        isAllSelect = false;
        break;
      }
    }
    that.setData({
      isAllSelect: isAllSelect
    })
  },

  /**
   * 全选按钮
   */
  selectAll: function() {
    var that = this;
    var cartList = that.data.cartList;
    var isAllSelect = !that.data.isAllSelect;
    for (var i = 0; i < cartList.length; i++) {
      cartList[i].select = isAllSelect;
    }
    that.setData({
      isAllSelect: isAllSelect,
      cartList: cartList
    })
    that.setTotalFee();
  },

  /**
   * 商品总价设置
   */
  setTotalFee: function() {
    var that = this;
    var totalFee = 0;
    var cartList = that.data.cartList;
    for (var i = 0; i < cartList.length; i++) {
      if (cartList[i].select) {
        totalFee += cartList[i].shop_price * 100 * parseInt(cartList[i].quantity);
      }
    }
    totalFee = (totalFee / 100).toFixed(2);
    that.setData({
      totalFee: totalFee,
      logiFreeFeeOff: parseFloat(that.data.logiFreeFee - totalFee).toFixed(2) || 0
    })
  },

  /**
   * 购物车商品数量改变四个方法
   */
  // 数量增加
  quantityPlus: function(evt) {
    var that = this;
    var cartList = that.data.cartList;
    var idx = evt.currentTarget.dataset.idx;
    var quantity = that.checkChangedQuantity(parseInt(cartList[idx].quantity) + 1);
    cartList[idx].quantity = quantity;
    that.setData({
      cartList: cartList
    })
    that.setTotalFee();
  },
  // 数量减少
  quantitySub: function(evt) {
    var that = this;
    var cartList = that.data.cartList;
    var idx = evt.currentTarget.dataset.idx;
    var quantity = that.checkChangedQuantity(parseInt(cartList[idx].quantity) - 1);
    if (quantity == 0) {
      util.modalPromisified({
        title: '系统提示',
        content: '您确定要删除当前商品吗？',
      }).then(res => {
        if (res.cancel) return;
        let cartList = that.data.cartList;
        // 更新全局购物车价格
        let cartPrice = app.globalData.cartPrice;
        cartPrice = (cartPrice - cartList[idx].quantity * cartList[idx].shop_price).toFixed(2);
        app.globalData.cartPrice = cartPrice;
        cartList.splice(idx, 1);
        that.setData({
          cartList: cartList
        })
      })
    } else {
      cartList[idx].quantity = quantity;
      that.setData({
        cartList: cartList
      })
      that.setTotalFee();
    }
  },

  // 数量修改
  quantityChange: function(evt) {
    var that = this;
    var cartList = that.data.cartList;
    var idx = evt.currentTarget.dataset.idx;
    var quantity = that.checkChangedQuantity(evt.detail.value);
    cartList[idx].quantity = quantity;
    that.setData({
      cartList: cartList
    })
    that.setTotalFee();
  },

  // 数量检测
  checkChangedQuantity: function(quantity) {
    // 1 单个商品不得超过库存
    if (quantity > 99) {
      util.modalPromisified({
        title: '系统提示',
        content: '所输入数量不能超过限购数量哦！',
        showCancel: false,
      })
      quantity = 99;
    }
    return quantity;
  },

  /**
   * 界面卸载时，更新购物车数据
   */
  onUnload: function() {
    var that = this;
    // 如果用户是跳转到下单界面则不更新
    if (wx.getStorageSync('goodsInfo') || that.data.cartList.length == 0) return;
    // 发起更新购物车数据请求
    // 构造购物车数据
    let cartList = that.data.cartList;
    let goodsInfo = [];
    for (let i = 0; i < cartList.length; i++) {
      goodsInfo.push({
        goodsid: cartList[i].goods_id,
        quantity: cartList[i].quantity,
        update_at: parseInt(Date.now() / 1000)
      })
    }
    util.post('cart/updateUserCart', {
      openid: wx.getStorageSync('openid'),
      goodsInfo: goodsInfo
    }).then(res => {
      console.log('update cart success')
    }).catch(res => {}).finally(res => {})
  },

  /**
   * 跳转到下单界面
   */
  navToBuy: function() {
    var that = this;
    // 简单数据判断
    var cartList = that.data.cartList;
    var goodsInfo = [];
    var totalNum = 0;
    for (var i = 0; i < cartList.length; i++) {
      if (cartList[i].select) {
        goodsInfo.push({
          goodsid: cartList[i].goodsid,
          quantity: cartList[i].quantity,
          goods_name: cartList[i].goods_name,
          goods_img: cartList[i].goods_img,
          market_price: cartList[i].market_price,
          shop_price: cartList[i].shop_price,
        })
        totalNum += cartList[i].quantity;
      }
    }
    if (goodsInfo.length == 0) {
      util.modalPromisified({
        title: '系统提示',
        content: '请至少选择一件商品',
        showCancel: false,
      })
      return;
    }
    wx.setStorageSync('goodsInfo', goodsInfo);
    wx.navigateTo({
      url: '../pay/pay?isCart=1&goodsFee=' + that.data.totalFee + '&totalNum=' + totalNum
    })
  },

})