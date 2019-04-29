// pages/todo/todo.js
const app = getApp()
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    photo: '',
    focus: false,
    text: '',
    translate: '',
    // showCamera: false,
    defeat: false,
    // autoHeight: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    console.log(options)
    let that = this
    let photo = options.photo
    console.log(photo)
    this.setData({
      photo: photo
    }, function() {
      that.upLoadImg()
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
  preview: function() {
    wx.previewImage({
      current: app.globalData.tempFilePaths[0],
      urls: app.globalData.tempFilePaths
    })
  },

  focus: function() {
    wx.switchTab({
      url: '../index/index',
    })
  },
  // 点击翻译按钮
  doTranslate: function(e) {
    let _type = e.currentTarget.id
    wx.showToast({
      title: '翻译中',
      icon: 'loading',
      duration: 20000
    })
    let data = {}
    if (_type == 'EN') {
      data = {
        str: this.data.text
      }
    } else if (_type == 'CH') {
      data = {
        str: this.data.text,
        type: 1
      }
    }
    util.Request('/api/uploade/translate.html', data, (res) => {
      if (res.status == 1) {
        this.setData({
          translate: res.data
        })
        wx.hideToast()
      } else if (res.status == 0) {
        wx.showToast({
          title: res.err,
          icon: 'none'
        })
      }
    })
  },
  setText: function(e) {
    let text = e.detail.value
    this.setData({
      text: text
    })
  },
  setTranslate: function(e) {
    let translate = e.detail.value
    this.setData({
      translate: translate
    })
  },
  doCopy: function() {
    let that = this
    let text = this.data.text
    let translate = this.data.translate
    if (text && !translate) {
      wx.setClipboardData({
        data: text,
        success: function(res) {
          that.saveData(text)
        }
      })
    } else if (text && translate) {
      wx.showActionSheet({
        itemList: ['复制原文', '复制翻译'],
        success: function(res) {
          console.log(res.tapIndex)
          let idx = res.tapIndex
          switch (idx) {
            case 0:
              wx.setClipboardData({
                data: text,
                success: function(res) {
                  that.saveData(text)
                }
              })
              break;
            case 1:
              wx.setClipboardData({
                data: translate,
                success: function(res) {
                  that.saveData(translate)
                }
              })
              break;
          }
        },
        fail: function(res) {}
      })
    }
  },
  upLoadImg: function() {
    wx.showToast({
      title: '识别中',
      icon: 'loading',
      duration: 20000
    })
    let that = this
    let text = this.data.text
    console.log(app.globalData.tempFilePaths[0])
    wx.uploadFile({
      url: app.globalData.serverUrl + '/api/uploade/uploads.html',
      filePath: this.data.photo,
      name: 'file',
      formData: {},
      success: function(res) {
        let ret = JSON.parse(res.data)
        console.log(res)
        if (ret.status === 0) {
          wx.showToast({
            title: ret.err,
            icon: 'none'
          })
        } else {
          util.Request('/api/uploade/ocr.html', { img: ret.img }, (rs) => {
            if (rs.status == 1) {
              let textList = rs.data
              let text = ''
              textList.forEach((item, index) => {
                text += item + ' ' 
              })
              that.setData({
                text: text,
                defeat: false
              })
              wx.hideToast()
            } else if (rs.status == 0) {
              wx.showToast({
                title: rs.err,
                icon: 'none'
              })
              that.setData({
                defeat: true,
                text: ''
              })
            }
          })
        }
      }
    })
  },
  // 保存数据
  saveData: function(str) {
    let data = {
      uid: app.globalData.uid,
      str: str
    }
    util.Request('/api/uploade/save.html', data, (res) => {
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