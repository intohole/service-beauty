$('.img-uploader-box').click(function(){
   
    var This=$(this);

        if( This.find('input')[0] || This.find('img').eq(0).attr('src').length<70 ){
            return;
        }    

    var items=getImgItmes(This),
        imgIndex=This.data('index');

        openPhotoSwipe(items,imgIndex);

});
$('.img-uploader-box').find('a').on('click',function(e){
    e=e||window.event;
    e.preventDefault();
});
function getImgItmes(obj){
    var imgs=obj.parent().parent().find('.img-uploader-box'),
        imgLen=imgs.size(),
        imgdata=[],
        num=0;

    for(var i=0; i<imgLen; i++){
        var ajson={};
        var src=imgs.eq(i).find('img').eq(0).attr('src');
        var imgsize=imgs.eq(i).find('a').eq(0).data('size').split('x');
        var w=Number(imgsize[0])?Number(imgsize[0]):800;
        var h=Number(imgsize[1])?Number(imgsize[1]):800;
        if(src.length>70){
            imgs.eq(i).attr('data-index',num);
            ajson.src=src;
            ajson.title=imgs.eq(i).next().text();
            ajson.h=h;
            ajson.w=w;
            imgdata.push(ajson);
            num++;
        }        
    }
    return imgdata;
}

function openPhotoSwipe(json,imgIndex) {
    // 选取主弹窗DOM
    var pswpElement = document.querySelectorAll('.pswp')[0];
    // build items array
    var items = json;
    
    var options = {
        history: false,
        focus: true,
        showAnimationDuration: 0,
        hideAnimationDuration: 0,
        fullscreenEl: false
    };

    var galleryContainer = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
    galleryContainer.init();
    galleryContainer.goTo(imgIndex);
}