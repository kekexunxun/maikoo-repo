var app = getApp();
var util = require('../../utils/util.js');
var action = require('../../utils/action.js');

Page({

  data: {
    searchList: [], // 搜索默认展示小程序列表
    searchResult: [], // 搜索的小程序结果列表
    inputShowed: false,
    inputVal: "",
    showSearchResult: false, // 是否展示搜索结果
    searchHistroy: [], // 用户搜索记录
    cartData: {
      isShow: true, // 当首页modal展示时，购物车悬浮图标不显示
      money: 0.00 // 购物车需要显示的金额
    }
  },

  onLoad: function(options) {
    var that = this;
    that.getSearchList();
    that.setSearchHistory();
  },

  onShow: function() {
    var that = this;
    // 将购物车的总金额放到globalData
    let cartData = that.data.cartData;
    cartData.money = app.globalData.cartPrice;
    that.setData({
      cartData: cartData
    })
  },

  /**
   * 获取并展示存储在本地的用户搜索记录
   */
  setSearchHistory: function() {
    this.setData({
      searchHistroy: wx.getStorageSync('searchHistroy') || []
    })
  },

  /**
   * 获取搜索列表/直接展示
   */
  getSearchList: function() {
    var that = this;
    util.post('search/getList', {
      uid: app.globalData.uid
    }).then(res => {
      that.setData({
        searchList: res || []
      })
    }).catch(res => {
      console.log('get SearchList failed code' + res.code)
      console.log(res)
    });
  },

  /**
   * 搜索框相关操作
   */
  showInput: function() {
    this.setData({
      inputShowed: true
    });
  },

  clearInput: function() {
    this.setData({
      inputVal: ""
    });
  },

  hideInput: function() {
    this.setData({
      inputVal: "",
      inputShowed: false,
      showSearchResult: false,
      searchResult: []
    });
  },

  inputTyping: function(e) {
    this.setData({
      inputVal: e.detail.value
    });
  },

  /**
   * 热门搜索关键词点击事件
   */
  searchKeyword: function(e) {
    var that = this;
    var idx = e.currentTarget.dataset.idx;
    var item = that.data.searchList[idx];
    // 三种跳转情况
    if (item.nav_type == 1) {
      // 1 不跳转 当做关键词进行搜索
      that.setData({
        inputVal: item.value,
        inputShowed: true
      })
      that.search();
    } else if (item.nav_type == 2) {
      // 2 跳转指定商品
      wx.navigateTo({
        url: '../goodsdetail/goodsdetail?goodsid=' + item.nav_id
      })
    } else if (item.nav_type == 3) {
      // 3 跳转到指定文章
      wx.navigateTo({
        url: '../article/article?articleid=' + item.nav_id
      })
    }
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
   * 搜索历史点击记录
   */
  searchHistroy: function(e) {
    var that = this;
    var item = that.data.searchHistroy[e.currentTarget.dataset.idx];
    that.setData({
      inputVal: item.value,
      inputShowed: true
    })
    that.search();
  },

  search: function() {
    var that = this;
    var value = that.data.inputVal;
    // 向后台发送查询请求
    if (value != null || value != "") {
      wx.showLoading({
        title: '搜索中...',
        mask: true
      })
      that.sendSearchRequest(value);
      that.addSearchHistory(value);
    } else {
      wx.showToast({
        title: '搜索字段为空',
        icon: 'none'
      })
    }
  },

  /**
   * 记录用户搜索的关键词
   * 1 重复搜索不记录
   * 2 最多记录十条
   */
  addSearchHistory: function(value) {
    var that = this;
    var searchHistroy = wx.getStorageSync('searchHistroy') || [];
    var isCanAdd = true;
    var search = {
      value: value,
      time: parseInt(Date.now() / 1000)
    };
    for (let i = 0; i < searchHistroy.length; i++) {
      if (searchHistroy[i].value == value) {
        isCanAdd = false;
        break;
      }
    }
    if (isCanAdd) {
      if (searchHistroy.length == 10) {
        searchHistroy.pop();
      }
      searchHistroy.push(search);
    }
    wx.setStorageSync('searchHistroy', searchHistroy);
    that.setSearchHistory();
  },

  /**
   * 向后台发送查询小程序的关键词
   */
  sendSearchRequest: function(value) {
    var that = this;
    util.post('search/getSearchReasult', {
      uid: app.globalData.uid,
      value: value
    }).then(res => {
      that.setData({
        searchResult: res || []
      })
      wx.showToast({
        title: '搜索成功',
        duration: 800
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        showCancel: false
      })
    }).finally(res => {
      that.setData({
        showSearchResult: true
      })
    })
  },

  /**
   * 删除用户搜索历史
   */
  delUserHistory: function() {
    var that = this;
    wx.showModal({
      title: '系统提示',
      content: '您确定要删除搜索历史吗？',
      success: function(res) {
        if (res.confirm) {
          wx.removeStorageSync('searchHistroy');
          that.setData({
            searchHistroy: []
          })
          wx.showToast({
            title: '删除成功',
          })
        }
      }
    })
  },

  /**
   * 添加商品到购物车
   */
  addToCart: function(evt) {
    var goodsid = evt.currentTarget.dataset.goodsid;
    action.addToCart(goodsid);
  },

  /**
   * 跳转到购物车界面
   */
  navToCart: function(evt) {
    wx.navigateTo({
      url: '/pages/cart/cart'
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || '你想要的商品我这里全都有~',
      path: '/pages/index/index'
    }
  }
})