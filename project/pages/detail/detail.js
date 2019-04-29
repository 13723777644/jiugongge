// pages/detail/detail.js
const app = getApp()
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    info: '',
    text: '',
    id: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let id = options.id
    util.Request('/api/index/getContent.html', { id: id }, (res) => {
      let info = res.data
      console.log(info)
      this.setData({
        info: info,
        text: info.content,
        id: id
      })
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },
  setText: function(e) {
    let text = e.detail.value
    this.setData({
      text: text
    })
  },
  doCopy: function() {
    let that = this
    let info = this.data.info.content
    let text = this.data.text
    console.log(text)
    wx.setClipboardData({
      data: text,
      success: function(res) {
        if (text == info) {
          wx.showToast({
            title: '复制成功',
            icon: 'success'
          })
        } else {
          wx.showToast({
            title: '复制成功',
            icon: 'success'
          })
          that.saveData(text)
        }
      }
    })
  },
  // 保存数据
  saveData: function (str) {
    let data = {
      str: str,
      id: this.data.id
    }
    util.Request('/api/index/update.html', data, (res) => {
      if (res.status == 1) {

      } else if (res.status == 0) {
        wx.showToast({
          title: res.err,
          icon: 'none'
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})