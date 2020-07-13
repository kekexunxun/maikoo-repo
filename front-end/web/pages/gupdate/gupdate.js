const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群组类别
    avatarDefault: "/images/icon_addpic.png", // 默认添加群头像图片
    avatarSet: "", // 用户上传的群头像图片地址
    pickerHidden: true, // pickerHidden
    toastHidden: true, // toastHidden
    addrCode: [0, 0, 0], // 地区码
  },

  /**
   * 获取传递过来的群类型
   */
  onLoad: function(options) {
    if (!app.globalData.groupInfo) {
      this.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback",
        toastCancel: 0
      })
      return;
    }
    let groupInfo = app.globalData.groupInfo;
    let addrCode = tool.code2Index(groupInfo.group_addr_code);
    // 构造addr
    let addr = addrCode.name.join(' - ');
    this.setData({
      gtype: options.gtype,
      gid: options.gid,
      addr: addr,
      addrCode: addrCode.code.join('_') || [0, 0, 0],
      addrDetail: groupInfo.group_address,
      avatarAlready: groupInfo.group_avatar_url,
      groupName: groupInfo.group_name,
      communityName: groupInfo.group_coummunity_name || "",
      schoolName: groupInfo.group_school_name || "",
      className: groupInfo.group_class_name || "",
      gBrief: groupInfo.group_brief,
      siteroot: app.globalData.siteroot
    })
  },

  /**
   * 变更群头像
   */
  changeGAvatar: function() {
    var that = this;
    wx.chooseImage({
      count: 1,
      success: function(res) {
        if (res.tempFiles[0].size > 1024 * 1024 * 4) {
          that.setData({
            toastHidden: false,
            toastTitle: "图片大小不能超过4MB",
            toastAction: "",
            toastCancel: 0
          })
        } else {
          that.setData({
            avatarSet: res.tempFilePaths[0]
          })
        }
      },
      fail: function(error) {}
    })
  },

  /**
   * 预览选择的群头像图片
   */
  previewImg: function(evt) {
    let imgArr = evt.currentTarget.dataset.idx == 1 ? [this.data.avatarSet] : [this.data.siteroot + this.data.avatarAlready];
    wx.previewImage({
      urls: imgArr
    })
  },

  /**
   * 选择地址
   */
  chooseAddress: function() {
    this.setData({
      pickerHidden: false
    })
  },

  /**
   *  确认选择地址
   */
  confirmAddress: function(evt) {
    this.setData({
      addr: evt.detail.name.join(' - '),
      addrCode: evt.detail.code.join('_'),
      pickerHidden: true
    })
  },

  /**
   * 取消选择地址
   */
  cancelAddress: function() {
    this.setData({
      pickerHidden: true
    })
  },

  /**
   * 输入群名称
   */
  inputGName: function(evt) {
    this.setData({
      groupName: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 输入社区名称
   */
  inputCoumName: function(evt) {
    this.setData({
      communityName: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 输入学校名称
   */
  inputSchName: function(evt) {
    this.setData({
      schoolName: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 输入班级名称
   */
  inputClsName: function(evt) {
    this.setData({
      className: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 输入群简介
   */
  inputGBrief: function(evt) {
    this.setData({
      gBrief: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 输入群详细地址
   */
  inputAddrDetail: function(evt) {
    this.setData({
      addrDetail: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 取消更新群组
   */
  cancel: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateBack({
      delta: 1
    })
  },

  /**
   * 更新群组信息
   */
  update: function(evt) {
    tool.storeFormId(evt.detail.formId);
    // formCheck
    if (!this.checkForm()) return;
    this.setData({
      toastHidden: false,
      toastTitle: "确定要更新群组信息吗？",
      toastAction: "update",
      toastCancel: 0
    })
  },

  /**
   * 表单检查
   */
  checkForm: function() {
    var that = this,
      errMsg = "",
      gtype = this.data.gtype;
    // 通用检查
    if (!that.data.groupName) {
      errMsg = "请填写群名称";
    } else if (!that.data.addrCode) {
      errMsg = "请选择地区";
    } else if (!that.data.gBrief) {
      errMsg = "请填写群简介";
    } else if ((gtype == 1 || gtype == 2) && !that.data.schoolName) {
      errMsg = "请填写学校名称";
    } else if (gtype == 2 && !that.data.className) {
      errMsg = "请填写班级名称";
    } else if (gtype == 3 && !that.data.communityName) {
      errMsg = "请填写社区名称";
    }
    if (errMsg != "") {
      that.setData({
        toastHidden: false,
        toastTitle: errMsg,
        toastAction: "",
        toastCancel: 0
      })
      return false;
    }
    return true;
  },

  /**
   * Toast 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) {
      this.setData({
        toastHidden: true
      })
    }
    var that = this,
      gtype = this.data.gtype;
    if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "update") {
      var that = this,
        url = "/api/group/country/update";
      if (evt.detail.cancel) return;
      // 根据群组类型判断需要传递哪些参数
      let generalData = {};
      generalData.groupId = that.data.gid;
      generalData.name = that.data.groupName;
      generalData.addrCode = that.data.addrCode;
      generalData.addrDetail = that.data.addrDetail;
      generalData.brief = that.data.gBrief;
      if (gtype == 1) {
        url = "/api/group/school/update";
        generalData.schoolName = that.data.schoolName
      } else if (gtype == 2) {
        url = "/api/group/class/update";
        generalData.schoolName = that.data.schoolName;
        generalData.className = that.data.className;
      } else if (gtype == 3) {
        url = "/api/group/community/update";
        generalData.communityName = that.data.communityName;
      }
      // 判断图片是否有改变
      if (that.data.avatarSet) {
        // 先进行图片上传
        util.fileUpload(that.data.avatarSet, that).then(res => {
          generalData.avatarUrl = res.data.img_src;
          // 数据上传
          return util.post(url, generalData, that, 300);
        }).then(res => {
          that.setData({
            toastHidden: false,
            toastTitle: "群信息更新成功",
            toastAction: "success",
            toastCancel: 0
          })
        }).catch(error => {
          that.setData({
            toastHidden: false,
            toastTitle: "群信息更新失败",
            toastAction: "",
            toastCancel: 0
          })
        })
      } else {
        generalData.avatarUrl = that.data.avatarAlready;
        util.post(url, generalData, that, 300).then(res => {
          that.setData({
            toastHidden: false,
            toastTitle: "群信息更新成功",
            toastAction: "success",
            toastCancel: 0
          })
        }).catch(error => {
          that.setData({
            toastHidden: false,
            toastTitle: "群信息更新失败",
            toastAction: "",
            toastCancel: 0
          })
        })
      }
    } else if (that.data.toastAction == "success") {
      wx.reLaunch({
        url: '/pages/group/group'
      })
    } else {
      that.setData({
        toastHidden: true
      })
    }
  }

})