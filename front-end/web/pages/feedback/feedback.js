const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    toastHidden: true,
    content: ""
  },

  onLoad: function() {},

  /**
   * 选择图片
   */
  chooseImage: function() {
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
            imgPath: res.tempFilePaths[0]
          })
        }
      },
      fail: function(error) {
        console.log(error);
        that.setData({
          toastHidden: false,
          toastTitle: "系统错误请及时联系管理员",
          toastAction: "",
          toastCancel: 0
        })
      }
    })
  },

  /**
   * 图片预览
   */
  previewImg: function() {
    wx.previewImage({
      urls: [this.data.imgPath]
    })
  },

  /**
   * 输入内容
   */
  inputContent: function(evt) {
    let value = tool.stringTrim(evt.detail.value);
    this.setData({
      content: value
    })
  },

  /**
   * 输入手机号
   */
  inputMobile: function(evt) {
    let value = tool.stringTrim(evt.detail.value);
    this.setData({
      mobile: value
    })
  },

  /**
   * 表单校验
   */
  checkForm: function() {
    var that = this;
    if (!that.data.content) {
      that.setData({
        toastHidden: false,
        toastTitle: "请填写反馈意见",
        toastCancel: 0,
        toastAction: ""
      })
      return false;
    }
    let reg = /^1[3|4|5|7|8][0-9]{9}$/; //验证规则
    let flag = reg.test(that.data.mobile); //true
    if (that.data.mobile && !flag) {
      that.setData({
        toastHidden: false,
        toastTitle: "请填写正确的手机号码",
        toastCancel: 0,
        toastAction: ""
      })
      return false;
    }
    return true;
  },

  /**
   * 提交反馈Modal
   */
  submit: function(evt) {
    var that = this;
    tool.storeFormId(evt.detail.formId);
    if (!that.checkForm()) return;
    that.setData({
      toastHidden: false,
      toastTitle: "确定要提交该意见反馈吗？",
      toastAction: "submit",
      toastCancel: 1
    })
  },

  /**
   * 取消提交反馈
   */
  cancel: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateBack({
      delta: 1
    })
  },

  /**
   * Modal 点击
   */
  toastConfirm: function(evt) {
    var that = this;
    if (evt.detail.cancel) return;
    if (that.data.toastAction == "submit") {
      // 判断是否有图片
      if (that.data.imgPath) {
        util.fileUpload(that.data.imgPath).then(res => {
          // 数据上传
          return util.post('/api/feedback/add', {
            feedback: that.data.content,
            mobile: that.data.mobile,
            img_url: res.data.img_src
          }, that, 200);
        }).then(res => {
          that.setData({
            toastHidden: false,
            toastTitle: "反馈成功",
            toastAction: "navback",
            toastCancel: 0
          })
        }).catch(error => {
          that.setData({
            toastHidden: false,
            toastTitle: "反馈失败",
            toastAction: "",
            toastCancel: 0
          })
        })
      } else {
        util.post('/api/feedback/add', {
          feedback: that.data.content,
          mobile: that.data.mobile
        }, that, 200).then(res => {
          that.setData({
            toastHidden: false,
            toastTitle: "反馈成功",
            toastAction: "navback",
            toastCancel: 0
          })
        }).catch(error => {
          that.setData({
            toastHidden: false,
            toastTitle: "反馈失败",
            toastAction: "",
            toastCancel: 0
          })
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