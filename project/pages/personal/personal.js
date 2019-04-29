// pages/record/record.js
const app = getApp()
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [],
    isEdit: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
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
    this.getData()
  },
  edit: function () {
    let isEdit = !this.data.isEdit
    this.setData({
      isEdit: isEdit
    })
  },
  cencal: function () {
    this.setData({
      isEdit: false
    })
  },
  choose: function (e) {
    let idx = e.currentTarget.dataset.index
    let list = this.data.list
    list.forEach((item, index) => {
      if (idx === index) {
        item.status = !item.status
      }
    })
    this.setData({
      lsit: list
    })
  },
  toDetail: function (e) {
    let idx = e.currentTarget.dataset.index
    let list = this.data.list
    list.forEach((item, index) => {
      if (idx === index) {
        let id = item.id
        wx.navigateTo({
          url: '../detail/detail?id=' + id,
        })
      }
    })
  },
  doMerge: function () {
    let list = this.data.list
    let arr = list.filter(item => item.status == true)
    let content = ''
    let idArr = []
    if (arr.length >= 2) {
      arr.forEach((item, index) => {
        content += item.content
        idArr.push(item.id)
      })
      let ids = idArr.join()
      let data = {
        uid: app.globalData.uid,
        ids: ids
      }
      util.Request('/api/index/saves.html', data, (res) => {
        if (res.status == 1) {
          util.Request('/api/index/del.html', { ids: ids }, (ret) => {
            if (ret.status == 1) {
              wx.showToast({
                title: '合并成功',
                icon: 'success'
              })
              this.getData()
            } else {
              wx.showToast({
                title: ret.err,
                icon: 'none'
              })
            }
          })
        } else {
          wx.showToast({
            title: res.err,
            icon: 'none'
          })
        }
      })
    } else {
      wx.showToast({
        title: '请选择两个或以上进行合并',
        icon: 'none'
      })
    }
  },
  doDelete: function () {
    let list = this.data.list
    let arr = list.filter(item => item.status == true)
    let idArr = []
    if (arr.length >= 1) {
      arr.forEach((item, index) => {
        idArr.push(item.id)
      })
      let ids = idArr.join()
      util.Request('/api/index/del.html', {
        ids: ids
      }, (res) => {
        if (res.status == 1) {
          wx.showToast({
            title: '删除成功',
            icon: 'success'
          })
          this.getData()
        } else {
          wx.showToast({
            title: res.err,
            icon: 'none'
          })
        }
      })
    } else {
      wx.showToast({
        title: '请选择一个或以上进行删除',
        icon: 'none'
      })
    }
  },
  // 获取数据
  getData: function () {
    let data = {
      uid: app.globalData.uid
    }
    util.Request('/api/index/historyList.html', data, (res) => {
      let list = res.data
      this.setData({
        list: list
      })
      // console.log(list)
      if (list.length == 0) {
        this.setData({
          isEdit: false
        })
      }
    })
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