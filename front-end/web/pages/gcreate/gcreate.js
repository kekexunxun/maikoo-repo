const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gIdx: 0,
    group: ['同乡群', '校友群', '同班群', '社区群'],
    avatarDefault: "/images/icon_addpic.png", // 默认添加群头像图片
    avatarSet: "", // 用户上传的群头像图片地址
    pickerHidden: true, // pickerHidden
    addrCode: [0, 0, 0], // 地区码
    toastHidden: true
  },

  /**
   * 获取传递过来的群类型
   */
  onLoad: function(options) {
    if (!options.gIdx) {
      that.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback",
        toastCancel: 0
      })
      return;
    } else {
      this.setData({
        gIdx: options.gIdx
      })
    }
  },

  /**
   * 用户认证校验
   */
  onShow: function() {
    if (!app.globalData.isAuth) {
      this.setData({
        toastHidden: false,
        toastTitle: "请登陆后再进行后续操作",
        toastAction: "auth",
        toastCancel: 1
      })
    }
  },

  /**
   * 变更群类别
   */
  groupChange: function(evt) {
    var that = this;
    that.setData({
      gIdx: evt.currentTarget.dataset.idx,
      toastHidden: true
    })
    // 重置表单
    that.resetForm();
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
            toastTitle: "图片大小不能超过4MB",
            toastHidden: false,
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
  previewImg: function() {
    wx.previewImage({
      urls: [this.data.avatarSet]
    })
  },

  /**
   * 当群类别切换时，重置已填写数据
   */
  resetForm: function() {
    this.setData({
      groupName: "",
      schoolName: "",
      className: "",
      avatarSet: "",
      addr: "",
      addrDetail: "",
      gBrief: "",
      communityName: "",
      addrCode: ""
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
   * 取消创建群组
   */
  cancel: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确定要创建群组吗？",
      toastAction: "cancel",
      toastCancel: 1
    })
  },

  /**
   * 确定创建群组
   */
  create: function(evt) {
    tool.storeFormId(evt.detail.formId);
    // formCheck
    if (!this.checkForm()) return;
    this.setData({
      toastHidden: false,
      toastTitle: "确定要创建群组吗？",
      toastAction: "create",
      toastCancel: 1
    })
  },

  /**
   * 表单检查
   */
  checkForm: function() {
    var that = this,
      errMsg = "",
      gIdx = this.data.gIdx;
    // 通用检查
    if (!that.data.groupName) {
      errMsg = "请填写群名称";
    } else if (!that.data.avatarSet) {
      errMsg = "请选择群头像";
    } else if (!that.data.addrCode) {
      errMsg = "请选择地区";
    } else if (!that.data.gBrief) {
      errMsg = "请填写群简介";
    } else if ((gIdx == 1 || gIdx == 2) && !that.data.schoolName) {
      errMsg = "请填写学校名称";
    } else if (gIdx == 2 && !that.data.className) {
      errMsg = "请填写班级名称";
    } else if (gIdx == 3 && !that.data.communityName) {
      errMsg = "请填写社区名称";
    }
    if (errMsg != "") {
      that.setData({
        toastTitle: errMsg,
        toastHidden: false,
        toastAction: "",
        toastCancel: 0
      })
      return false;
    }
    return true;
  },

  /**
   * modal 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) {
      if (this.data.toastAction == "auth") {
        wx.navigateBack({
          delta: 1
        })
      } else {
        this.setData({
          toastHidden: true
        })
        return;
      }
    }
    var that = this,
      gtype = this.data.gIdx
    if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "create") {
      var url = "/api/group/country/add";
      // 根据群组类型判断需要传递哪些参数
      var generalData = {};
      generalData.name = that.data.groupName;
      generalData.addrCode = that.data.addrCode;
      generalData.addrDetail = that.data.addrDetail;
      generalData.brief = that.data.gBrief;
      if (gtype == 1) {
        url = "/api/group/school/add";
        generalData.schoolName = that.data.schoolName
      } else if (gtype == 2) {
        url = "/api/group/class/add";
        generalData.schoolName = that.data.schoolName;
        generalData.className = that.data.className;
      } else if (gtype == 3) {
        url = "/api/group/community/add";
        generalData.communityName = that.data.communityName;
      }
      // 先进行图片上传
      util.fileUpload(that.data.avatarSet, that).then(res => {
        generalData.avatarUrl = res.data.img_src;
        // 数据上传
        return util.post(url, generalData, that, 300);
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: "图片上传失败",
          toastAction: "",
          toastCancel: 0
        })
      }).then(res => {
        // 创建群成功 传递群ID到下面的信息完善界面
        that.setData({
          toastHidden: false,
          toastTitle: "群组创建成功！请在10分钟内填写并完善个人信息，超时则该群自动注销",
          toastAction: "fill",
          toastCancel: 0,
          gid: res.data.group_id
        })
        app.uploadFormId();
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: "群组创建失败",
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "fill") {
      var info = "&gid=" + this.data.gid + '&gname=' + this.data.groupName;
      if (gtype == 1) {
        info += '&schname=' + this.data.schoolName;
      } else if (gtype == 2) {
        info += '&schname=' + this.data.schoolName + '&clsname=' + this.data.className;
      } else if (gtype == 3) {
        info += '&comname=' + this.data.communityName;
      }
      wx.navigateTo({
        url: '/pages/ginfofirst/ginfofirst?gtype=' + gtype + '&action=fill' + info
      })
    } else if (that.data.toastAction == "cancel") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "auth") {
      wx.navigateTo({
        url: '/pages/auth/auth'
      })
    } else {
      that.setData({
        toastHidden: true
      })
    }
  },

})