const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    userInfo: {}, // 会员信息
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群类别
    toastHidden: true, // 是否隐藏modal
  },

  /**
   * 获取请求参数
   */
  onLoad: function(options) {
    if (!options.action) {
      this.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback",
        toastCancel: 0
      })
      return;
    }
    this.setData({
      action: options.action,
      applyId: options.applyid || "", // 申请ID
      gtype: options.gtype || "", // 群类型
      uid: options.uid || "", // 用户ID
      gid: options.gid || "", // 群ID
    })
    this.getMemInfo();
  },

  /**
   * 获取用户详情、申请详情、审核详情
   */
  getMemInfo: function() {
    var that = this,
      action = this.data.action,
      url = "",
      data = {},
      gtype = that.data.gtype;
    if (action == "approve") {
      url = "/api/group/user/review/information";
      data.applyId = that.data.applyId;
      data.groupType = that.data.gTypeArr[gtype];
      wx.setNavigationBarTitle({
        title: '我的审核'
      })
    } else if (action == "apply") {
      url = "/api/group/user/apply/information";
      data.applyId = that.data.applyId;
      data.groupType = that.data.gTypeArr[gtype];
      wx.setNavigationBarTitle({
        title: '我的申请'
      })
    } else {
      url = "/api/group/user/information";
      data.groupType = that.data.gTypeArr[gtype];
      data.groupId = that.data.gid;
      data.userId = that.data.uid;
      wx.setNavigationBarTitle({
        title: '用户详情'
      })
    }
    util.post(url, data, that, 300).then(res => {
      if (gtype == 1 || gtype == 2) {
        res.data.user_type = res.data.user_type == 'TEACHER' ? '教师' : '学生';
      } else if (gtype == 3) {
        res.data.user_type = res.data.user_type == 'OWNER' ? '业主' : '物业';
      }
      if (that.data.action == 'detail' && JSON.stringify(res.data).indexOf('is_fav') == -1) {
        res.data.is_self = true;
      }
      that.setData({
        userInfo: res.data || {}
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "详情获取失败",
        toastAction: "navback",
        toastCancel: 0
      })
    })
  },

  /**
   * 收藏当前用户
   */
  fav: function() {
    this.setData({
      toastHidden: false,
      toastTitle: "确定要收藏该用户吗？",
      toastAction: "fav",
      toastCancel: 1
    })
  },

  /**
   * 取消收藏当前用户 
   */
  unFav: function() {
    this.setData({
      toastHidden: false,
      toastTitle: "确定要取消收藏该用户吗？",
      toastAction: "fav",
      toastCancel: 1
    })
  },

  /**
   * 拨打电话
   */
  makePhoneCall: function() {
    wx.makePhoneCall({
      phoneNumber: this.data.userInfo.user_mobile
    })
  },

  /**
   * 审核通过
   */
  approve: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确定要通过当前用户的申请吗？",
      toastAction: "approve",
      toastCancel: 1,
      result: "APPROVE"
    })
  },

  /**
   * 审核驳回
   */
  reject: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确定要驳回当前用户的申请吗？",
      toastAction: "approve",
      toastCancel: 1,
      result: "REJECT"
    })
  },

  /**
   * Toast 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) return;
    var that = this;
    if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "fav") {
      let isFav = that.data.userInfo.is_fav;
      let url = isFav ? '/api/group/user/favor/delete' : "/api/group/user/favor/insert";
      util.post(url, {
        groupType: that.data.gTypeArr[that.data.gtype],
        groupId: that.data.gid,
        userId: that.data.uid
      }, that, 300).then(res => {
        let userInfo = that.data.userInfo;
        userInfo.is_fav = !isFav;
        that.setData({
          toastHidden: false,
          toastTitle: isFav ? '取消收藏成功' : '收藏成功',
          userInfo: userInfo,
          toastAction: "",
          toastCancel: 0
        })
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: isFav ? '取消收藏失败' : '收藏失败',
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "approve") {
      util.post('/api/group/user/review/update', {
        applyId: that.data.applyId,
        result: that.data.result,
        groupType: that.data.gTypeArr[that.data.gtype]
      }, that, 300).then(res => {
        that.setData({
          toastHidden: false,
          toastTitle: "审核处理成功",
          toastAction: "refresh",
          toastCancel: 0
        })
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: "审核处理失败",
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "refresh") {
      that.getMemInfo();
    }
  },

  /**
   * 跳转到更新用户信息界面
   */
  update: function(evt) {
    tool.storeFormId(evt.detail.formId);
    app.globalData.userInfo = this.data.userInfo;
    // 额外的信息
    let extData = "",
      gtype = this.data.gtype;
    if (gtype == 1) {
      extData = "&schname=" + this.data.userInfo.school_name;
    } else if (gtype == 2) {
      extData = "&schname=" + this.data.userInfo.school_name + '&clsname=' + this.data.userInfo.class_name;
    } else if (gtype == 3) {
      extData = '&comname=' + this.data.userInfo.community_name
    }
    wx.navigateTo({
      url: '/pages/ginfofirst/ginfofirst?gid=' + this.data.gid + '&gtype=' + this.data.gtype + '&action=update' + extData
    })
  },

  /**
   * 下拉刷新
   */
  onPullDownRefresh: function() {
    this.getMemInfo();
  }

})