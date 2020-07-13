Component({
  /**
   * 组件的属性列表
   */
  properties: {
    hidden: {
      type: Boolean,
      value: true,
      observer(newVal, oldVal, changedPath) {
        this.setData({
          hidden: newVal
        })
      }
    },
    confirm: {
      type: String,
      value: '确定'
    },
    cancel: {
      type: String,
      value: '取消'
    },
    showCancel: {
      type: Number,
      value: 1
    },
    title: String
  },

  /**
   * 组件的初始数据
   */
  data: {

  },

  pageLifetimes: {
    show() {
      this.setData({
        confirm: this.data.confirm,
        cancel: this.data.cancel,
        showCancel: this.data.showCancel,
        title: this.data.title
      })
    }
  },

  methods: {

    /**
     * 设置组件隐藏
     */
    _hideModal: function() {
      this.setData({
        hidden: true
      })
    },

    /**
     * 防止误触
     */
    _preventTouchMove: function() {},

    /**
     * item Click 事件
     */
    itemClick: function(evt) {
      // detail对象，提供给事件监听函数
      let idx = evt.currentTarget.dataset.idx;
      const myEventDetail = {
        cancel: idx == 1,
        confirm: idx == 2
      }
      // 触发事件的选项
      const myEventOption = {
        capturePhase: true
      }
      this.triggerEvent('itemClick', myEventDetail, myEventOption);
      // 关闭Modal
      this._hideModal();
    }

  }
})