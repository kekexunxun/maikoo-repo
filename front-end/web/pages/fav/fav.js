const app = getApp();
const util = require('../../utils/util.js');

Page({

  data: {
    gIndex: 0,
    group: [{
      name: '同乡群',
      pageNum: 1,
      groupType: "COUNTRY"
    }, {
      name: '校友群',
      pageNum: 1,
      groupType: "SCHOOL"
    }, {
      name: '同班群',
      pageNum: 1,
      groupType: "CLASS"
    }, {
      name: '社区群',
      pageNum: 1,
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
    this.getMyFav();
  },

  /**
   * 获取我的收藏列表
   */
  getMyFav: function() {
    var that = this,
      group = this.data.group,
      gIndex = this.data.gIndex,
      groupList = this.data.groupList;
    util.post('/api/group/user/favor', {
      pageNum: group[gIndex].pageNum,
      groupType: group[gIndex].groupType
    }, that, 400).then(res => {
      if (res.data && res.data.length != 0) {
        groupList[gIndex].list = groupList[gIndex].list.concat(res.data || []);
      }
      group[gIndex].pageNum++;
      that.setData({
        group: group,
        groupList: groupList
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "列表渲染失败"
      })
    })
  },

  /**
   * 跳转到用户详情
   */
  navToMemDetail: function(evt) {
    wx.navigateTo({
      url: "/pages/memdetail/memdetail?action=detail&uid=" + evt.currentTarget.dataset.uid + '&gtype=' + this.data.gIndex + '&gid=' + evt.currentTarget.dataset.gid
    })
  },

  /**
   * 变更群类别
   */
  groupChange: function(evt) {
    var that = this;
    that.setData({
      gIndex: evt.currentTarget.dataset.idx
    })
    if (that.data.group[that.data.gIndex].pageNum == 1) {
      that.getMyFav();
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
    that.getMyFav();
  }

})