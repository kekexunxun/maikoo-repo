var app = getApp();
var util = require('../../utils/util.js');
Page({

  data: {
    questionList: [], // 问题列表
  },

  onLoad: function() {
    this.getQuestionList();
  },

  /**
   * 获取问题列表
   */
  getQuestionList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    util.post('minibase/getQuestion', {
      uid: app.globalData.uid
    }).then(res => {
      that.setData({
        questionList: res || []
      })
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请检查网络后重试',
        showCancel: false
      })
    }).finally(res => {})
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.getQuestionList();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

})