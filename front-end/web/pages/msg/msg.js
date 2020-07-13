const app = getApp();
const util = require('../../utils/util.js');

Page({

  data: {
    pageNum: 1, // 页码
    toastHidden: true, // 是否隐藏审核结束的modal
    msgList: [], // 消息列表
  },

  onLoad: function() {
    this.getMsgList();
  },

  /**
   * 获取消息列表
   */
  getMsgList: function() {
    var that = this;
    util.post('/api/message/user', {
      pageNum: that.data.pageNum
    }, that, 400).then(res => {
      that.setData({
        pageNum: that.data.pageNum + 1,
        msgList: that.data.msgList.concat(res.data || [])
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "消息列表获取失败"
      })
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      pageNum: 1,
      msgList: []
    })
    this.getMsgList();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getMsgList();
  }

})