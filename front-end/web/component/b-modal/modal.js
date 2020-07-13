Component({
  /**
   * 组件的属性列表
   */
  properties: {
    hidden: {
      type: Boolean,
      value: false,
      observer(newVal, oldVal, changedPath) {
        this.setData({
          hidden: newVal
        })
      }
    },
    first: String,
    second: String,
    isShare: {
      type: Number,
      value: 0
    }
  },

  /**
   * 组件的初始数据
   */
  data: {

  },

  pageLifetimes: {
    show() {
      this.setData({
        first: this.data.first,
        second: this.data.second
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
      const myEventDetail = {
        idx: evt.currentTarget.dataset.idx
      }
      this.triggerEvent('itemClick', myEventDetail, {});
      // 关闭Modal
      this._hideModal();
    }

  }
})