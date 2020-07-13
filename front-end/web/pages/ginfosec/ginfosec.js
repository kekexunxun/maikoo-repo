const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gname: "名录群", // 群名称
    modalHeight: "", // 设置遮罩层的高度
    formHeight: "", // 设置form的高度
    toastHidden: true,
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群组类别
  },

  /**
   * 获取传递过来的群名称和群ID
   */
  onLoad: function(options) {
    if (!options.gid && !options.gtype && !options.action && !app.globalData.pageData) {
      that.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback",
        toastCancel: 0
      })
      return;
    }
    var that = this;
    // 设置form高度
    let sysInfo = wx.getSystemInfoSync();
    let modalHeight = sysInfo.windowHeight - 100 * (sysInfo.screenWidth / 750);
    let formHeight = sysInfo.windowHeight - 140 * (sysInfo.screenWidth / 750);
    that.setData({
      gtype: options.gtype,
      gid: options.gid,
      action: options.action,
      modalHeight: modalHeight,
      formHeight: formHeight
    })
    if (options.action == 'update') {
      let userInfo = app.globalData.userInfo;
      that.setData({
        ucomp: userInfo.user_company,
        upos: userInfo.user_position,
        ubrief: userInfo.user_brief
      })
      wx.setNavigationBarTitle({
        title: '信息修改'
      })
    }
    // 先将一部分FORMID写入数据库
    app.uploadFormId();
  },

  // 表单文字输入
  /**
   * 输入公司名称
   */
  inputCo: function(evt) {
    this.setData({
      ucomp: tool.stringTrim(evt.detail.value)
    })
  },
  /**
   * 输入用户职位
   */
  inputPos: function(evt) {
    this.setData({
      upos: tool.stringTrim(evt.detail.value)
    })
  },
  /**
   * 输入用户个人简介
   */
  inputUBrief: function(evt) {
    this.setData({
      ubrief: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 表单校验
   */
  validateForm: function() {
    var that = this;
    let toastTitle = true,
      ucomp = that.data.ucomp,
      upos = that.data.upos,
      ubrief = that.data.ubrief;
    if (!ucomp) {
      toastTitle = "请输入公司名称";
    } else if (!upos) {
      toastTitle = "请输入您的职位";
    } else if (!ubrief) {
      toastTitle = "请输入个人简介";
    } else {
      return true;
    }
    that.setData({
      toastHidden: false,
      toastTitle: toastTitle,
      toastAction: "",
      toastCancel: 0
    })
    return false;
  },


  /**
   * 确认所填写的信息
   */
  confirmInfo: function(evt) {
    var that = this;
    tool.storeFormId(evt.detail.formId);
    if (!that.validateForm()) return;
    that.setData({
      toastHidden: false,
      toastTitle: "确定要提交当前信息吗？",
      toastAction: "submit",
      toastCancel: 0
    })
  },

  /**
   * Toast 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) return;
    var that = this;
    if (that.data.toastAction == "submit") {
      var data = app.globalData.pageData;
      data.company = that.data.ucomp;
      data.position = that.data.upos;
      data.brief = that.data.ubrief;
      data.groupType = that.data.gTypeArr[that.data.gtype];
      data.groupId = that.data.gid;
      data.action = that.data.action.toUpperCase();
      util.post('/api/group/user/save', data, that, 300).then(res => {
        that.setData({
          toastHidden: false,
          toastTitle: that.data.action == 'update' ? '信息修改完成' : '信息登记完成',
          toastAction: "finish",
          toastCancel: 0
        })
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: "信息保存失败",
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "finish") {
      if (that.data.action == "update") {
        wx.navigateBack({
          delta: 3
        })
      } else {
        wx.reLaunch({
          url: '/pages/group/group'
        })
      }
    } else if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else {
      that.setData({
        toastHidden: true
      })
    }
  }

})