//index.js
//获取应用实例
const app = getApp();
var util = require('../../utils/util.js');

Page({

  data: {
    banner: [{
      img: '/images/banner.png'
    }], // 默认banner
    isTeacher: false, // 用户身份是否为教师
    list: [{
      text: '课程列表',
      tap: 'navToCourseList',
      img: '/images/icon-course-list.png',
      isAll: true
    }, {
      text: '我的班级',
      tap: 'navToCourse',
      img: '/images/icon-course.png',
      isTeacher: false
    }, {
      text: '我的打卡',
      tap: 'navToMyClock',
      img: '/images/icon-clock.png',
      isTeacher: false
    }, {
      text: '我的消息',
      tap: 'navToMyMsg',
      img: '/images/icon-msg.png',
      isTeacher: false
    }, {
      text: '开始打卡',
      tap: 'startClock',
      img: '/images/icon-startclock.png',
      isTeacher: true
    }, {
      text: '发送消息',
      tap: 'sendMsg',
      img: '/images/icon-sendmsg.png',
      isTeacher: true
    }, {
      text: '班级管理',
      tap: 'navToClassManage',
      img: '/images/icon-class-manage.png',
      isTeacher: true
    }, {
      text: '我的课表',
      tap: 'navToTeacherSchedule',
      img: '/images/icon-teacher-course.png',
      isTeacher: true
    }, {
      text: '个人信息',
      img: '/images/icon-info.png',
      tap: 'myInfo',
      isAll: true
    }, {
      text: '系统公告',
      tap: 'navToMyMsg',
      img: '/images/icon-msg.png',
      isTeacher: true
    }, {
      text: '我要反馈',
      tap: 'sendFeedback',
      img: '/images/icon-feedback.png',
      isAll: true
    }, {
      text: '合作协议',
      tap: 'navToClause',
      img: '/images/icon-clause.png',
      isAll: true
    }],
    notice: '',
    marqueePace: 1, //滚动速度
    marqueeDistance: 0, //初始滚动距离
    marqueeDistance2: 0,
    marquee2copy_status: false,
    marquee2_margin: 60,
    size: 28,
    orientation: 'left', //滚动方向
    interval: 20 // 时间间隔
  },

  onLoad: function() {
    this.getIndex();
  },

  /**
   * 设置公告marquee
   */
  showTextMarquee: function() {
    var that = this;
    var length = that.data.notice.length * that.data.size / wx.getSystemInfoSync().pixelRatio; //文字长度
    var windowWidth = wx.getSystemInfoSync().windowWidth; // 屏幕宽度
    that.setData({
      length: length,
      windowWidth: windowWidth,
      marquee2_margin: length < windowWidth ? windowWidth - length : that.data.marquee2_margin //当文字长度小于屏幕长度时，需要增加补白
    });
    that.run1(); // 第一个字消失后立即从右边出现
  },

  run1: function() {
    var that = this;
    var interval = setInterval(function() {
      if (-that.data.marqueeDistance < that.data.length) {
        that.setData({
          marqueeDistance: that.data.marqueeDistance - that.data.marqueePace,
        });
      } else {
        clearInterval(interval);
        that.setData({
          marqueeDistance: that.data.windowWidth
        });
        that.run1();
      }
    }, that.data.interval);
  },

  /**
   * 获取首页界面详情 主要是获取banner
   */
  getIndex: function() {
    var that = this;
    // 先进行登陆
    wx.showLoading({
      title: '请稍等',
      mask: true
    })
    app.loadInfo().then(res => {
      // 先校验身份
      // 检测用户是否被允许进入当前界面
      if (!app.globalData.allow) {
        util.modalPromisified({
          title: '系统提示',
          content: '您已被禁止进入该小程序，请及时联系管理员',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/notallow/notallow',
          })
        })
      } else if (!app.globalData.isAuth) {
        util.modalPromisified({
          title: '系统提示',
          content: '您需要先绑定用户身份才可进行后续操作',
          confirmText: '进行绑定',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/userauth/userauth'
          })
        })
      } else {
        return util.post('minibase/getIndex', {
          uid: app.globalData.uid
        }, 100)
      }
    }).catch(res => {
      if (res.data.code == 403) {
        // 避免用户在redirect界面直接返回首页仍然可以使用的情况
        app.globalData.allow = false
        util.modalPromisified({
          title: '系统提示',
          content: '您已被禁止进入该小程序，请及时联系管理员',
          showCancel: false
        }).then(res => {
          wx.redirectTo({
            url: '/pages/notallow/notallow',
          })
        })
      }
    }).then(res => {
      if (!res) return;
      let banner = that.data.banner;
      that.setData({
        banner: res.banner || banner,
        // isTeacher: app.globalData.userType == 1 ? true : false
        isTeacher: true
      })
      // 根据系统设置 修改小程序名称
      if (app.globalData.setting) {
        wx.setNavigationBarTitle({
          title: app.globalData.setting.mini_name || '吸铁石兄弟美术'
        })
        if (app.globalData.setting.notice) {
          that.setData({
            notice: app.globalData.setting.notice
          })
          that.showTextMarquee();
        }

      }
    }).catch(res => {
      console.log(res)
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请尝试检查网络后重试',
        confirmText: '重新连接'
      }).then(res => {
        if (res.confirm) {
          that.getIndex();
        }
      })
    })
  },

  /**
   * 跳转到课程列表
   */
  navToCourseList: function() {
    wx.navigateTo({
      url: '/pages/courselist/courselist',
    })
  },

  /**
   * 跳转到我的打卡界面
   */
  navToMyClock: function() {
    wx.navigateTo({
      url: '/pages/myclock/myclock',
    })
  },

  /**
   * 跳转到我的消息界面
   */
  navToMyMsg: function() {
    wx.navigateTo({
      url: '/pages/message/message',
    })
  },

  /**
   * 跳转到我的消息界面
   */
  navToClause: function() {
    wx.navigateTo({
      url: '/pages/clause/clause',
    })
  },

  /**
   * 跳转到课程详情
   */
  navToCourse: function() {
    wx.navigateTo({
      url: '/pages/course/course',
    })
  },

  /**
   * 教师开始课程打卡
   */
  startClock: function() {
    wx.navigateTo({
      url: '/pages/startclock/startclock',
    })
  },

  /**
   * 教师发送消息
   */
  sendMsg: function() {
    wx.navigateTo({
      url: '/pages/sendmsg/sendmsg'
    })
  },

  navToClassManage: function() {
    wx.navigateTo({
      url: '/pages/classmanage/classmanage',
    })
  },

  /**
   * 用户发送反馈
   */
  sendFeedback: function() {
    wx.navigateTo({
      url: '/pages/feedback/feedback'
    })
  },

  /**
   * 跳转到教师课表
   */
  navToTeacherSchedule: function() {
    wx.navigateTo({
      url: '/pages/tschedule/tschedule',
    })
  },

  /**
   * 用户个人信息查看与修改
   */
  myInfo: function() {
    wx.navigateTo({
      url: '/pages/userinfo/userinfo',
    })
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.setting.share_text || '这样子的美术课程你一定很喜欢~',
      path: '/pages/index/index'
    }
  }

})