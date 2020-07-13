var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    columnList: [], // 专题列表
    isHaveMore: true, // 是否还有更多的数据
  },

  /**
   * 获取所有专题详情
   */
  onLoad: function() {
    this.getColumnList(1);
  },

  /**
   * 获取指定页码的专题详情
   * @param pageNum 页码
   * 默认每次获取12个专题
   */
  getColumnList: function(pageNum) {
    var that = this;
    if(!that.data.isHaveMore){
      wx.showToast({
        title: '没有数据了',
        icon: 'loading'
      });
      return;
    }
    wx.request({
      url: app.globalData.siteroot + 'column/getColumnList',
      method: 'POST',
      data: {
        pageNum: pageNum
      },
      success: function(res) {
        var isHaveMore = that.data.isHaveMore;
        if(res.data.code == 0){
          if (res.data.data) {
            if (res.data.data.length < 12) {
              isHaveMore = false;
            }
            that.setData({
              columnList: that.data.columnList.concat(res.data.data),
              isHaveMore: isHaveMore
            })
          } else {
            wx.showToast({
              title: '没有数据了',
              icon: 'loading'
            })
            that.setData({
              isHaveMore: false
            })
          }
        }
      },
      fail: function() {},
      complete: function() {}
    })
  },

  /**
   * 进入专题详情界面
   */
  navToColumnDetail: function(e) {

    var columnid = e.currentTarget.dataset.columnid;
    wx.navigateTo({
      url: '/pages/columndetail/columndetail?columnid=' + columnid,
    })
  },


  /**
   * 用户分享
   */
  onShareAppMessage: function () {
    var that = this;
    return {
      title: '快来寻找最符合你的小程序专题吧！',
      path: '/pages/column/column'
    }
  },

})