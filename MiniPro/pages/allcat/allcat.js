var app = getApp();
var util = require('../../utils/util.js');
Page({

  data: {
    isHaveCat: true, // 是否有列表可展示
    catList: [], // 分类列表
  },

  onLoad: function(options) {
    this.getCatList();
  },

  /**
   * 获取分类列表
   */
  getCatList: function() {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'catagory/getCatList',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          if (res.data.data.length == 0) {
            that.setData({
              isHaveCat: false
            })
          } else {
            that.setData({
              catList: res.data.data
            })
          }
        } else {
          that.setData({
            isHaveCat: false
          })
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
        }
      }
    })
  },

  /**
   * 跳转到指定的目录
   */
  navToCat: function(evt) {
    var that = this;
    var index = evt.currentTarget.dataset.idx;
    var catId = null;
    if (index != "all") {
      catId = that.data.catList[index].catagory_id;
    } else {
      catId = index;
    }
    // 将当前用户点击事件记录进入log
    util.miniCatagoryCount(catId);
    // 跳转到对应界面
    wx.navigateTo({
      url: '../catagory/catagory?catId=' + catId,
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})