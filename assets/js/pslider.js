$(document).ready(function () {
    //Удаление новых строк, герерируемых jquery при удаление элементов из html
    jQuery.fn.cleanWhitespace=function () {
        textNodes=this.contents().filter(
            function () {
                return (this.nodeType == 3 && !/\S/.test(this.nodeValue));
            })
            .remove();
        return this;
    }
    $('.pslider__photos__list').cleanWhitespace();

    jQuery.fn.pslider = function() {

        return this.each(function() {

            var $this = $(this);
            var $activePhoto = 1;
            var $totalPhoto = $('.pslider__photos__list > .item', $this).length;


            function init () {

                $('.pslider__photos__list > .item', $this).each(function(index) {
                    $(this).attr('data-pslider-pos', index+1);
                });

                $this.prepend('<div class="pslider__title"><span class="pslider__title__txt">Фотографии</span><span class="pslider__title__counter"></span></div>');
                $this.append('<div class="pslider__photoDesc"></div>')
                $('.pslider__photos__wrapper', $this).prepend('<div class="navi_left"></div><div class="navi_right"></div>');

                navi();
            }
            init();

            function next (event) {

                var pList = $this.find('.pslider__photos__list');
                var currentPhotoWidth = pList.find('.active').width() + 10;

                pList.find('.item').removeClass('active');

                $activePhoto++;

                if ($activePhoto > $totalPhoto) {
                    $activePhoto--;
                }
                else {
                    pList.find('.item[data-pslider-pos="'+$activePhoto+'"]').addClass('active');
                }

                pList.animate({
                    left: "-="+currentPhotoWidth
                }, 500, function() {
                    navi();
                });

            };

            function prev (event) {

                var pList = $this.find('.pslider__photos__list');
                var prevPhotoWidth = pList.find('.item[data-pslider-pos="'+($activePhoto-1)+'"]').width() + 10;

                pList.find('.item').removeClass('active');

                $activePhoto--;

                if ($activePhoto < 1) {
                    $activePhoto++;
                }
                else {
                    pList.find('.item[data-pslider-pos="'+$activePhoto+'"]').addClass('active');
                }

                pList.animate({
                    left: "+="+prevPhotoWidth
                }, 500, function() {
                    navi();
                });

            };

            function navi() {

                if ($totalPhoto > 1) {
                    $this.find('.navi_left').show();
                    $this.find('.navi_right').show();
                }

                if ($activePhoto == 1) {
                    $this.find('.navi_left').hide();
                }

                if ($activePhoto == $totalPhoto) {
                    $this.find('.navi_right').hide();
                }

                $('.pslider__title__counter', $this).html($activePhoto+' из '+$totalPhoto);

                photoDesc();

            }

            function link() {
                if ($(this).parent('.item').hasClass('active')) {
                    //just go to
                }
                else {
                    var index = $(this).parent('.item').attr('data-pslider-pos');
                    goToPhoto(index);
                    return false;
                }
            }

            function goToPhoto(index) {
                var pList = $this.find('.pslider__photos__list');
                var position = $('.item[data-pslider-pos="'+index+'"]', $this).position();
                position = position.left - 100;
                pList.find('.item').removeClass('active');
                $activePhoto = index;
                pList.find('.item[data-pslider-pos="'+$activePhoto+'"]').addClass('active');

                if (position < 0) {
                    position = 100;
                    pList.animate({
                        left: position
                    }, 500, function() {
                        navi();
                    });
                }
                else {
                    pList.animate({
                        left: '-'+position
                    }, 500, function() {
                        navi();
                    });
                }

                photoDesc();

            }

            function photoDesc(index) {
                if (index == null) {
                    index = $activePhoto;
                }
                $('.pslider__photoDesc', $this).text( $('.item[data-pslider-pos="'+index+'"] img', $this).attr('title') );
            }

            $this.find('.navi_left').on('click', prev);
            $this.find('.navi_right').on('click', next);
            $this.find('.item > a').on('click', link);

        });

    };

    $('.pslider__wrapper').pslider();

});
