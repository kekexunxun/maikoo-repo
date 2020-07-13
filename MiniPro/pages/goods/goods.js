//获取应用实例
const app = getApp()

Page({
  data: {
    inputShowed: false,
    inputVal: "",
    isHaveSearchValue: false, // 是否有搜索结果
    goodsList: [], // 商品列表
    pageNum: 0, // 页码
  },

  /**
   * 初始化界面
   * @param goodscat 商品类别ID
   */
  onLoad: function(options) {
    var that = this;
    if (!options.catId) {
      wx.showModal({
        title: '系统提示',
        content: '参数错误',
        showCancel: false,
        success: function(res) {
          wx.navigateBack({
            delta: 1
          })
        }
      })
      return;
    }
    // 数据请求
    that.getGoodsList(options.catId, that.data.pageNum);
    that.setData({
      catId: options.catId
    })
    // 设置顶部标题
    wx.setNavigationBarTitle({
      title: options.title,
      fail: function(res) {
        console.log(res)
      }
    })
  },

  /**
   * 获取商品列表
   * @param page 页码
   * @param catId 商城分类ID
   */
  getGoodsList: function(catId, pageNum) {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'fangte/getGoods',
      method: 'POST',
      dataType: 'json',
      data: {
        catId: catId,
        pageNum: pageNum
      },
      success: function(res) {
        if (res.data.code == "201") {
          wx.showToast({
            title: '暂无商品',
            icon: 'none'
          })
        } else if (res.data.code == "200") {
          // 将新获取的数据 res.data.list，concat到前台显示的showlist中即可。
          that.setData({
            goodsList: that.data.goodsList.concat(res.data.goods),
            pageNum: pageNum + 1
          })
        }
      },
      fail: function() {},
      complete: function() {
        wx.hideLoading();
        wx.hideNavigationBarLoading();
      }
    })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    wx.showNavigationBarLoading();
    // 判断是否还有更多数据
    this.getGoodsList(this.data.catId, this.data.pageNum);
  },

  /**
   * 展示搜索框的取消搜索按钮
   */
  showInput: function() {
    this.setData({
      inputShowed: true
    });
  },

  /**
   * 清空搜索框，取消搜索
   */
  hideInput: function() {
    this.setData({
      inputVal: "",
      inputShowed: false,
      isHaveSearchValue: false
    });
  },
  clearInput: function() {
    this.setData({
      inputVal: "",
      isHaveSearchValue: false,
      searchResult: []
    });
  },

  /**
   * 搜索栏商品搜索
   */
  inputTyping: function(e) {
    var that = this;
    this.setData({
      inputVal: e.detail.value
    });
  },

  /**
  * 用户转发分享
  */
  onShareAppMessage: function () {
    return {
      title: app.globalData.share_text || 'A · Q大玩家！你值得拥有',
      path: '/pages/index/index'
    }
  }

})