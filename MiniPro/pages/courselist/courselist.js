var app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    courseList: [], // 课程列表
    pageNum: 0, // 页码
  },

  onLoad: function(options) {
    this.getCourseList();
  },

  /**
   * 获取课程列表
   */
  getCourseList: function() {
    var that = this;
    wx.showLoading({
      title: '加载中',
      mask: 'true'
    })
    util.post('course/getCourseList', {
      uid: 1,
      pageNum: that.data.pageNum
    }, 100).then(res => {
      that.setData({
        courseList: that.data.courseList.concat(res),
        pageNum: that.data.pageNum + 1
      })
    }).catch(res => {
      if (res.statusCode == 200) {
        if (that.data.pageNum == 0) {
          util.modalPromisified({
            title: '系统提示',
            content: '暂无课程，请及时联系管理员',
            showCancel: false
          }).then(res => {
            wx.navigateBack({
              delta: 1
            })
          })
        } else {
          wx.showToast({
            title: '没有更多啦',
            icon: 'loading',
            duration: 1000
          })
        }
      } else {
        util.modalPromisified({
          title: '系统提示',
          content: '网络错误',
          showCancel: false
        }).then(res => {
          wx.navigateBack({
            delta: 1
          })
        })
      }
    })
  },

  /**
   * 跳转到课程详情
   */
  courseDetail: function(evt) {
    wx.navigateTo({
      url: '/pages/coursedetail/coursedetail?courseid=' + evt.currentTarget.dataset.courseid,
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.setData({
      pageNum: 0,
      courseList: []
    })
    this.getCourseList();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    this.getCourseList();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || '这样子的美术课程你一定很喜欢~',
      path: '/pages/index/index'
    }
  }

})