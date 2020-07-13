const app = getApp();
const util = require('../../utils/util.js');

Page({

  data: {
    gIndex: 0,
    group: [{
      name: '同乡群',
      pageNum: 1,
      spot: false,
      groupType: "COUNTRY"
    }, {
      name: '校友群',
      pageNum: 1,
      spot: false,
      groupType: "SCHOOL"
    }, {
      name: '同班群',
      pageNum: 1,
      spot: false,
      groupType: "CLASS"
    }, {
      name: '社区群',
      pageNum: 1,
      spot: false,
      groupType: "COMMUNITY"
    }],
    groupList: [{
      list: []
    }, {
      list: []
    }, {
      list: []
    }, {
      list: []
    }],
    toastHidden: true
  },

  onLoad: function() {
    this.setData({
      siteroot: app.globalData.siteroot
    })
  },

  onShow: function() {
    this.onPullDownRefresh();
  },

  /**
   * 获取我的申请消息FLAG
   */
  getMyApplyFlag: function() {
    var that = this,
      group = this.data.group;
    util.post('/api/flag/apply', {}, that, 100).then(res => {
      group[0].spot = res.data.tx_has_unread;
      group[1].spot = res.data.xy_has_unread;
      group[2].spot = res.data.tb_has_unread;
      group[3].spot = res.data.sq_has_unread;
      that.setData({
        group: group
      })
      that.getMyApply();
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "FLAG获取失败"
      })
    })
  },

  /**
   * 获取我的申请列表
   */
  getMyApply: function() {
    var that = this,
      group = this.data.group,
      gIndex = this.data.gIndex,
      groupList = this.data.groupList;
    util.post('/api/group/user/apply', {
      pageNum: group[gIndex].pageNum,
      groupType: group[gIndex].groupType
    }, that, 400).then(res => {
      if (res.data && res.data.length != 0) {
        groupList[gIndex].list = groupList[gIndex].list.concat(res.data || []);
      }
      group[gIndex].pageNum++;
      that.setData({
        groupList: groupList,
        group: group
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "列表获取失败"
      })
    })
  },

  /**
   * 跳转到用户详情
   */
  navToMemDetail: function(evt) {
    wx.navigateTo({
      url: "/pages/memdetail/memdetail?action=apply&applyid=" + evt.currentTarget.dataset.applyid + '&gtype=' + this.data.gIndex
    })
  },

  /**
   * 变更群类别
   */
  groupChange: function(evt) {
    var that = this,
      group = this.data.group,
      gIndex = evt.currentTarget.dataset.idx;
    group[gIndex].spot = false;
    that.setData({
      gIndex: gIndex,
      group: group
    })
    if (that.data.group[that.data.gIndex].pageNum == 1) {
      that.getMyApply();
    }
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    var that = this,
      groupList = this.data.groupList,
      group = this.data.group,
      gIndex = this.data.gIndex;
    group[gIndex].pageNum = 1;
    groupList[gIndex].list = [];
    that.setData({
      groupList: groupList
    })
    that.getMyApplyFlag();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getMyApply();
  }

})