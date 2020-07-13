var app = getApp();
Page({

  data: {
    cartList: [], //购物车列表
    isHaveCart: false, //购物车是否含有商品
    isAllSelect: false, //是否全选
    totalFee: 0.00, //当前购物车中总商品价格
  },

  onShow: function() {
    var that = this;
    that.setData({
      isAllSelect: false,
      totalFee: 0.00,
      cartList: []
    })
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    setTimeout(function() {
      that.getCartList();
    }, 800)
  },

  /**
   * 获取用户购物车列表
   */
  getCartList: function() {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'fangte/getCartList',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        if (res.data.code == "200") {
          // 简单的数据处理
          let cartList = res.data.cartList || [];
          if (cartList.length == 0) {
            that.setData({
              isHaveCart: false,
            })
          } else {
            that.setData({
              cartList: cartList,
              isHaveCart: true
            })
          }
        } else if (res.data.code == "201") {
          wx.showModal({
            title: '系统提示',
            content: '购物车商品已失效，系统已自动移除失效商品',
            showCancel: false
          })
        } else if (res.data.code == "202") {
          wx.showToast({
            title: '暂无商品',
            icon: 'none',
            duration: 1000
          })
        }
      },
      fail: function() {
        wx.hideLoading();
        wx.showModal({
          title: '网络错误',
          content: '请检查网络后重试',
          showCancel: false
        })
      },
      complete: function() {
        wx.hideLoading();
        wx.hideNavigationBarLoading();
        wx.stopPullDownRefresh();
      }
    })
  },

  /**
   * 改变当前购物车选中状态
   */
  changeSelectState: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var cartList = that.data.cartList;
    if (cartList[index].stock == 0) {
      wx.showToast({
        title: '库存不足',
        icon: 'loading'
      })
    } else {
      cartList[index].select = !cartList[index].select;
      that.setData({
        cartList: cartList
      })
      // 判断是否全选
      that.isAllSelect();
      // 更新商品费用
      that.setTotalFee();
    }

  },

  /**
   * 判断是否全选
   */
  isAllSelect: function() {
    var that = this;
    var cartList = that.data.cartList;
    var isAllSelect = true;
    if (cartList.length == 0) {
      isAllSelect = false;
    } else {
      for (var i = 0; i < cartList.length; i++) {
        if (!cartList[i].select) {
          isAllSelect = false;
          break;
        }
      }
    }
    that.setData({
      isAllSelect: isAllSelect
    })
  },
  /**
   * 全选
   */
  selectAll: function() {
    var that = this;
    var cartList = that.data.cartList;
    var isAllSelect = that.data.isAllSelect;
    let isHaveNoStock = false;
    for (var i = 0; i < cartList.length; i++) {
      if (isAllSelect) {
        cartList[i].select = false;
      } else {
        cartList[i].select = cartList[i].stock > 0 ? true : false;
        if (!cartList[i].select) isHaveNoStock = true;
      }
    }
    if (isHaveNoStock) {
      wx.showModal({
        title: '系统提示',
        content: '部分商品库存不足无法选取',
        showCancel: false
      })
    }
    that.setData({
      cartList: cartList,
      isAllSelect: !isAllSelect
    })
    // 更新商品费用
    that.setTotalFee();
  },

  /**
   * 改变当前购物车某件商品的数量
   */
  changeNum: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var value = that.checkValue(e.detail.value);
    // value检测
    var cartList = that.data.cartList;
    cartList[index].quantity = value;
    that.setData({
      cartList: cartList
    })
    that.setTotalFee();
  },

  /**
   * 商品数量增加
   */
  numberPlus: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var cartList = that.data.cartList;
    var value = that.checkValue(cartList[index].quantity + 1);
    cartList[index].quantity = value;
    that.setData({
      cartList: cartList
    })
    that.setTotalFee();
  },

  /**
   * 商品数量减少
   */
  numberMinus: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var cartList = that.data.cartList;
    var value = that.checkValue(cartList[index].quantity - 1);
    cartList[index].quantity = value;
    that.setData({
      cartList: cartList
    })
    that.setTotalFee();
  },
  /**
   * 检测当前数量是否有效
   */
  checkValue: function(value) {
    value = parseInt(value);
    if (value > 999) {
      value = 999;
    }
    if (value < 1) {
      value = 1;
    }
    return value;
  },

  /**
   * 生命周期函数--监听页面卸载
   * 页面卸载时，更新当前用户的购物车
   */
  onUnload: function() {
    var that = this;
    // 构造goodsInfo 直接更新用户购物车数据
    var cartList = that.data.cartList;
    if (!cartList) {
      return;
    }
    var goodsInfo = [];
    for (var i = 0; i < cartList.length; i++) {
      goodsInfo.push({
        goods_id: cartList[i].goods_id,
        quantity: cartList[i].quantity,
        detail_id: cartList[i].detail_id
      })
    }
    wx.request({
      url: app.globalData.siteroot + 'fangte/updateUserCart',
      method: 'POST',
      dataType: 'json',
      data: {
        goodsInfo: goodsInfo,
        openid: wx.getStorageSync('openid')
      },
      success: function() {},
      fail: function() {},
      complete: function() {}
    })
  },

  /**
   * 设置当前选中商品总价
   */
  setTotalFee: function() {
    var that = this;
    var cartList = that.data.cartList;
    var totalFee = 0;
    for (var i = 0; i < cartList.length; i++) {
      // 如果有促销活动
      if (cartList[i].select) {
        if (cartList[i].is_on_promotion) {
          totalFee += cartList[i].quantity * cartList[i].pro_price
        } else {
          totalFee += cartList[i].quantity * cartList[i].shop_price
        }
      }
    }
    totalFee = totalFee.toFixed(2);
    that.setData({
      totalFee: totalFee
    })
  },

  /**
   * 订单结算
   */
  navToPay: function(evt) {
    var that = this;
    // 判断是否有商品选中
    var cartList = that.data.cartList;
    var isHaveSelect = false;
    var selectItems = [];
    for (var i = 0; i < cartList.length; i++) {
      if (cartList[i].select) {
        isHaveSelect = true;
        selectItems.push(cartList[i]);
      }
    }
    if (!isHaveSelect) {
      wx.showToast({
        title: '请选择商品',
        icon: 'loading'
      })
      return;
    }
    // 如果有选择商品
    wx.setStorageSync('goodsItem', selectItems);
    wx.navigateTo({
      url: '../pay/pay?isCart=1&totalFee=' + that.data.totalFee,
    })
  },

  /**
   * 删除购物车中的指定商品
   */
  deleteCartItem: function(evt) {
    console.log(evt)
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '确定要删除该商品吗？',
      success: function(res) {
        if (res.confirm) {
          var idx = evt.currentTarget.dataset.idx;
          var cartList = that.data.cartList;
          cartList.splice(idx, 1)
          that.setData({
            cartList: cartList
          })
          wx.showToast({
            title: '删除成功',
            duration: 800
          })
          that.setTotalFee();
        }
      }
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    var that = this;
    wx.showNavigationBarLoading();
    that.setData({
      cartList: [],
      isHaveCart: true,
      isAllSelect: false
    })
    that.getCartList();
  }

})