const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gname: "", // 群名称
    modalHeight: "", // 设置遮罩层的高度
    formHeight: "", // 设置form的高度
    uage: "", // 用户年龄段选择
    uageArr: ["50后", "60后", "70后", "80后", "90后", "10后"],
    toastHidden: true,
    pickerHidden: true,
    formModalHidden: true,
    mobile: ""
  },

  onReady: function() {
    // 构造毕业时间
    let graduationArr = [];
    for (let i = 1980; i <= 2019; i++) {
      graduationArr.push(i);
    }
    this.setData({
      graduationArr: graduationArr
    })
  },

  /**
   * 获取传递过来的群名称和群ID
   */
  onLoad: function(options) {
    if (!options.gid && !options.gtype && !options.action) {
      this.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback"
      })
      return;
    }
    var that = this;
    // 设置form高度
    let sysInfo = wx.getSystemInfoSync();
    let modalHeight = sysInfo.windowHeight - 100 * (sysInfo.screenWidth / 750);
    let formHeight = sysInfo.windowHeight - 140 * (sysInfo.screenWidth / 750);
    that.setData({
      gname: options.gname || "",
      gid: options.gid,
      gtype: options.gtype,
      action: options.action,
      clsName: options.clsname || "", // 班级名称
      schName: options.schname || "", // 学校名称
      commName: options.comname || "", // 社区名称
      modalHeight: modalHeight,
      formHeight: formHeight
    })
    if (options.action == 'update') {
      let userInfo = app.globalData.userInfo;
      // 数据处理
      that.setData({
        uname: userInfo.user_name,
        mobile: userInfo.user_mobile,
        gname: userInfo.group_name,
        utype: userInfo.user_type || "",
        ugender: userInfo.user_gender == "FEMALE" ? '男' : '女',
        uage: userInfo.user_tag || ""
      })
      wx.setNavigationBarTitle({
        title: '信息修改'
      })
    }
  },

  // 表单文字输入
  /**
   * 输入用户名称
   */
  inputUName: function(evt) {
    this.setData({
      uname: tool.stringTrim(evt.detail.value)
    })
  },

  /**
   * 选择用户性别
   */
  chooseUGender: function() {
    this.setData({
      formModalHidden: false,
      first: "男",
      second: "女",
      formAction: "ugender"
    })
  },

  /**
   * 选择用户年龄段
   */
  chooseUAge: function() {
    this.setData({
      pickerHidden: false,
      pickerTitle: "选择年龄段",
      pickerAction: "tag",
      pickerData: this.data.uageArr
    })
  },

  /**
   * 选择用户毕业时间
   */
  chooseUGraduate: function() {
    this.setData({
      pickerHidden: false,
      pickerTitle: "选择毕业时间",
      pickerAction: "graduate",
      pickerData: this.data.graduationArr
    })
  },

  /**
   * picker 确认选择 
   */
  pickerConfirm: function(evt) {
    if (this.data.pickerAction == "graduate") {
      this.setData({
        ugraduate: evt.detail.value
      })
    } else if (this.data.pickerAction == "tag") {
      this.setData({
        uage: evt.detail.value
      })
    }
  },

  /**
   * 输入用户所在楼栋号
   */
  inputBuilding: function(evt) {
    this.setData({
      ubuilding: evt.detail.value
    })
  },

  /**
   * 输入用户所在房间号
   */
  inputRoom: function(evt) {
    this.setData({
      uroom: evt.detail.value
    })
  },

  /**
   * 选择用户身份
   */
  chooseUType: function() {
    if (this.data.gtype == 1 || this.data.gtype == 2) {
      this.setData({
        formModalHidden: false,
        first: "学生",
        second: "教师",
        formAction: "utype"
      })
    } else if (this.data.gtype == 3) {
      this.setData({
        formModalHidden: false,
        first: "业主",
        second: "物业",
        formAction: "utype"
      })
    }
  },

  /**
   * 表单 选择用户身份 选择用户性别
   */
  formModalClick: function(evt) {
    var that = this;
    if (that.data.formAction == "utype") {
      if (that.data.gtype == 1 || that.data.gtype == 2) {
        that.setData({
          utype: evt.detail.idx == 1 ? '学生' : '教师'
        })
      } else if (that.data.gtype == 3) {
        that.setData({
          utype: evt.detail.idx == 1 ? '业主' : '物业'
        })
      }
    } else if (that.data.formAction == "ugender") {
      that.setData({
        ugender: evt.detail.idx == 1 ? '男' : '女'
      })
    }
  },

  /**
   *  用户手机号获取
   */
  getUserPhoneNumber: function(evt) {
    var that = this;
    if (evt.detail.errMsg == "getPhoneNumber:ok") {
      util.loginPromisified().then(res => {
        return util.post('/api/user/phone', {
          encryptedData: evt.detail.encryptedData,
          iv: evt.detail.iv,
          code: res.code
        }, that, 100)
      }).then(res => {
        // 获得解密后的手机号码
        that.setData({
          mobile: res.data.mobile
        })
      }).catch(error => {
        console.log(error);
        that.setData({
          toastTitle: "手机号码获取失败，请重试",
          toastHidden: false,
          toastAction: ""
        })
      })
    } else {
      that.setData({
        toastTitle: "需要重新授权以获取手机号码",
        toastHidden: false,
        toastAction: ""
      })
    }
  },

  /**
   * 表单校验
   * 返回序列化后的表单内容
   */
  validateForm: function() {
    var that = this;
    let errMsg = true,
      uname = that.data.uname,
      ugender = that.data.ugender,
      uage = that.data.uage,
      mobile = that.data.mobile,
      gtype = that.data.gtype,
      ugraduate = that.data.ugraduate,
      ubuilding = that.data.ubuilding ? that.data.ubuilding : 0,
      uroom = that.data.uroom ? that.data.uroom : 0,
      utype = that.data.utype;
    if (!uname) {
      errMsg = "请输入姓名";
    } else if (!ugender) {
      errMsg = "请选择性别";
    } else if (gtype == 0 && !uage) {
      errMsg = "请选择年龄段";
    } else if (gtype != 0 && !utype) {
      errMsg = "请选择身份";
    } else if (gtype == 1 && utype == '学生' && !ugraduate) {
      errMsg = "请选择毕业时间";
    } else if (gtype == 3 && utype == '业主' && !ubuilding) {
      errMsg = "请输入楼栋号";
    } else if (gtype == 3 && utype == '业主' && !uroom) {
      errMsg = "请输入房间号";
    } else if (!mobile) {
      errMsg = "请获取手机号码";
    } else {
      ugender = ugender == "男" ? "MALE" : "FEMALE";
      // 信息构造
      let info = {};
      info.name = uname;
      info.gender = ugender;
      info.mobile = mobile;
      if (gtype == 0) {
        info.tag = uage;
      } else if (gtype == 1) {
        utype = utype == "学生" ? "STUDENT" : "TEACHER";
        info.userType = utype;
        if (utype == "STUDENT") {
          info.graduateAt = ugraduate;
        }
      } else if (gtype == 2) {
        utype = utype == "学生" ? "STUDENT" : "TEACHER";
        info.userType = utype;
      } else if (gtype == 3) {
        utype = utype == "业主" ? "OWNER" : "MANAGER";
        info.userType = utype;
        info.building = ubuilding;
        info.room = uroom;
      }
      return info;
    }
    that.setData({
      toastTitle: errMsg,
      toastHidden: false,
      toastAction: ""
    })
    return false;
  },

  /**
   * 跳转到下一个信息填写的界面
   */
  nextInfo: function(evt) {
    tool.storeFormId(evt.detail.formId)
    let info = this.validateForm();
    if (info) {
      app.globalData.pageData = info;
      wx.navigateTo({
        url: '/pages/ginfosec/ginfosec?gtype=' + this.data.gtype + '&gid=' + this.data.gid + '&action=' + this.data.action
      })
    }
  },

  /**
   * Toast 点击
   */
  toastConfirm: function(evt) {
    if (this.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    }
  }

})