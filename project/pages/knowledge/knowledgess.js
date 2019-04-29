// pages/knowledge/knowledges.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url: '',
    arr1: [],
    arr2: [],
    c:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.upLoadImg(options.url);
    this.setData({
      url: options.url
    })

  },
  upLoadImg: function (reg) {

    let that = this
    let text = this.data.text
    wx.uploadFile({
      url: app.globalData.serverUrl + '/api/uploade/upload.html',
      filePath: reg,
      name: 'file',
      formData: {},
      success: function (res) {
        let ret = JSON.parse(res.data)

        if (ret.status === 0) {
          console.log(88999)
          wx.showToast({
            title: ret.err,
            icon: 'none'
          })
        } else {
          //成功返回
          wx.request({
            url: app.d.Url + '/Api/Index/save',
            method: "post",
            data: {
              uid: app.globalData.uid,
              url: ret.img,
              type: 4
            },
            header: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
              console.log(res.data.arrs1)
              console.log(8888)
              that.setData({
                arr1: res.data.arrs1,
                arr2: res.data.arrs2,

              })

            },
            fail: function (e) {
              wx.showToast({
                title: '网络异常！',
                duration: 3000
              });
            },

          })
        }
      }
    })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  fanhui: function () {
    wx.navigateTo({
      url: '../index/index'
    })
  },
  xiazais: function () {

    var c = this.data.arr1.concat(this.data.arr2);
    console.log(c);
    var that = this;
    wx.getSetting({
      success(res) {
        console.log(res.authSetting['scope.writePhotosAlbum'])
        if (!res.authSetting['scope.writePhotosAlbum']) {
          wx.authorize({
            scope: 'scope.writePhotosAlbum',
            success() {
              that.c = c;
              that.index = 0;
              that.digest()
            },
            fail() {
              wx.showToast({
                title: '请从新授权',
                icon: 'success',
                duration: 2000
              })
            }
          })
        } else {
          that.c = c;
          that.index = 0;
          that.digest()
        }
     }
    })
  },
  digest() {
    wx.getImageInfo({
      src: this.c[this.index].photo,
      success: (res) => {
        let path = res.path
        wx.saveImageToPhotosAlbum({
          filePath: path,
          success: (res) => {
            if (++this.index < this.c.length) {
              this.digest();
            } else {
              wx.showToast({
                title: '保存成功',
                icon: 'success',
                duration: 2000
              })
            }
          },
          fail(res) {
            console.log(res);
          }
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },
  xiazai: function () {

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