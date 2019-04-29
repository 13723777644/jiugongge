// pages/knowledge/knowledge.js
const app = getApp()
const util = require('../../utils/util.js');
const WxParse = require('../../wxParse/wxParse.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    title: ''
  },
  /**
 * 生命周期函数--监听页面加载
 */
  onLoad: function (options) {
    let id = options.id
    util.Request('/api/Index/getnews.html', { id: id }, (res) => {
      var content = res.data.content;
      WxParse.wxParse('content', 'html', content, this, 3);
      let title = res.data.name
      this.setData({
        title: title
      })
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})