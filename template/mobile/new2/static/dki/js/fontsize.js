
//获取当前页面根元素的font-size
function resize() {
    var deviceWidth = document.documentElement.clientWidth
    if(deviceWidth >= 750) deviceWidth = 750
    document.documentElement.style.fontSize = deviceWidth / 7.5 + 'px';
}
resize();
window.addEventListener('resize', resize);


