var app = getApp();
Page({

  data: {
    pageNum: 1, //当前的分页数
    num: 12, //默认获取的列表数量
    isHaveMore: true, //列表是否含有更多
    showBottomLoading: false, //是否显示底部loading
    isHavePromotion: false, //是否有数据
    promotionList: [], //数据列表
    isCanRefresh: false, //是否可以处理下拉数据
  },

  onLoad: function(options) {
    this.getPromotionList(0, 12);
  },

  /**
   * 获取推介列表
   * @param int pageNum 页码数
   * @param int num 获取的列表条数 
   */
  getPromotionList: function(pageNum, num) {
    var that = this;
    // 后台数据请求
    wx.request({
      url: app.globalData.siteroot + 'fangte/getPromotionList',
      method: 'POST',
      dataType: 'json',
      data: {
        pageNum: pageNum,
        num: num,
        userid: app.globalData.userID,
      },
      success: function(res) {
        wx.hideLoading();
        if (pageNum != 0) {
          wx.showToast({
            title: '数据加载成功',
          })
        }
        if (res.data.code == 0) {
          if (res.data.data) {
            that.setData({
              isHavePromotion: true,
              promotionList: that.data.promotionList.concat(res.data.data),
              showBottomLoading: false,
              pageNum: pageNum,
              userid: app.globalData.userID
            })
          } else {
            wx.showLoading({
              title: '没有更多啦',
              icon: 'loading'
            })
          }
        }
        if (res.data.code == "400") {
          wx.showToast({
            title: '暂无数据',
            icon: 'loading'
          })
        }
        that.setData({
          isCanRefresh: true
        })
        wx.stopPullDownRefresh();
      },
      fail: function() {},
      complete: function() {
        wx.stopPullDownRefresh();
        wx.hideLoading();
      }
    })
  },


  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var that = this;
    if (!that.data.isCanRefresh) {
      return;
    }
    if (!that.data.isHaveMore) {
      that.setData({
        showBottomLoading: true
      })
      // 1500ms后关闭显示
      setTimeout(function() {
        that.setData({
          showBottomLoading: false
        })
      }, 1500);
      return;
    } else {
      that.setData({
        isCanRefresh: false
      })
      wx.showLoading({
        title: '数据加载中',
        mask: true
      })
      setTimeout(function() {
        that.getPromotionList(that.data.pageNum + 1, 12);
      }, 1500)
    }
  },

})