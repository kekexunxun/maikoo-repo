const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    groupMem: [], // 用户列表
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群类别
    pageNum: 1,
    toastHidden: true
  },

  onLoad: function(options) {
    if (!options.gid && !options.gtype) {
      that.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback",
        toastCancel: 0
      })
      return;
    }
    this.setData({
      gtype: options.gtype,
      gid: options.gid
    })
    this.getGroupUser();
  },

  /**
   * 获取群组成员列表
   */
  getGroupUser: function() {
    var that = this;
    util.post('/api/group/user', {
      groupType: that.data.gTypeArr[that.data.gtype],
      groupId: that.data.gid,
      hasDetail: false,
      pageNum: that.data.pageNum
    }, that, 200).then(res => {
      that.setData({
        groupMem: that.data.groupMem.concat(res.data || []),
        pageNum: that.data.pageNum + 1
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "群组成员获取失败",
        toastCancel: 0,
        toastAction: ""
      })
    })
  },

  /**
   * 任命管理员
   */
  assignMem: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确定要将该用户设置为管理员吗？您将失去对该群的管理权限",
      toastCancel: 1,
      toastAction: "assign",
      uid: evt.currentTarget.dataset.uid
    })
  },

  /**
   * 删除会员
   */
  delMem: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确定要删除该群成员吗？",
      toastCancel: 1,
      toastAction: "delete",
      uid: evt.currentTarget.dataset.uid,
      listIdx: evt.currentTarget.dataset.idx
    })
  },

  /**
   * modal 点击
   */
  toastConfirm: function(evt) {
    var that = this;
    if (evt.detail.cancel) return;
    if (that.data.toastAction == "assign") {
      util.post('/api/group/owner/change', {
        userId: that.data.uid,
        groupId: that.data.gid,
        groupType: that.data.gTypeArr[that.data.gtype]
      }, that, 200).then(res => {
        this.setData({
          toastHidden: false,
          toastTitle: "管理员变更成功",
          toastAction: "update_success",
          toastCancel: 0
        })
      }).catch(error => {
        this.setData({
          toastHidden: false,
          toastTitle: "管理员变更失败",
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "delete") {
      util.post('/api/group/user/remove', {
        userId: that.data.uid,
        groupId: that.data.gid,
        groupType: that.data.gTypeArr[that.data.gtype]
      }, that, 200).then(res => {
        that.setData({
          toastHidden: false,
          toastTitle: "群成员删除成功",
          toastAction: "delete_success",
          toastCancel: 0
        })
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: "群成员删除失败",
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "delete_success") {
      let groupMem = that.data.groupMem,
        listIdx = that.data.listIdx;
      groupMem.splice(listIdx, 1);
      that.setData({
        groupMem: groupMem
      })
    } else if (that.data.toastAction == "update_success") {
      wx.reLaunch({
        url: '/pages/group/group'
      })
    } else if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    }
  },

  /**
   * 下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      groupMem: [],
      pageNum: 1
    })
    this.getGroupUser();
  },

  /**
   * 下拉加载
   */
  onReachBottom: function() {
    this.getGroupUser();
  }

})