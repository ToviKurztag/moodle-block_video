require(['jquery', 'core/config'], function($, CFG) {
    
    var updateshowtable = function(selector, arr) {
        $(selector).each(function(i) {
            if (arr[i] == true) {
                $(this).css('display', 'inline-block');
            } else {
                $(this).css('display', 'none');
            }
        })
    }
    var areas;
    var updateshowingtable = function() {
        
        areas = {  'videos' : { 'len' : $('.showingpreferencetable #videos .videotableitem[dissearch="true"]').length,
                                'show' : []},
                    'zoomvideos' : {'len' : $('.showingpreferencetable #zoomvideos .videotableitem[dissearch="true"]').length,
                                'show' : []}};
        
        for (var type in areas) {
            areas[type]['show'] = new Array(areas[type]['len']);
            areas[type]['show'].fill(false);
            areas[type]['show'].fill(true, 0, 2);
            $('#arrowright'+type).css('display', 'none');
            $('#arrowleft'+type).css('display', 'none');
            if (areas[type]['len'] > 2) {                
                $('#arrowright'+type).css('display', 'block');
            }
            updateshowtable('.showingpreferencetable #' + type +' .videotableitem[dissearch="true"]', areas[type]['show']);


        };
       
        $('#arrowrightvideos').click(function() {
            arrowright('videos');
            exit();
        })
        $('#arrowrightzoomvideos').click(function() {
            arrowright('zoomvideos');
            exit();
        }) 
        $('#arrowleftvideos').click(function() {
            arrowleft('videos');
            exit();
        })
        $('#arrowleftzoomvideos').click(function() {
            arrowleft('zoomvideos');
            exit();

        })   
    }

    updateshowingtable();
    var arrowright = function(type) {

        end = areas[type]['show'].lastIndexOf(true);
        areas[type]['show'].fill(false);
        areas[type]['show'].fill(true, end+1, end+3);
        updateshowtable('.showingpreferencetable #' + type +' .videotableitem', areas[type]['show']);
        if (end + 3 >= areas[type]['show'].length) {
            $('#arrowright'+type).css('display', 'none');
        }
        if (areas[type]['show'][0] == false) {
            $('#arrowleft'+type).css('display', 'block');
        }
    }
    var arrowleft = function(type) {

        start = areas[type]['show'].indexOf(true);
        areas[type]['show'].fill(false);
        areas[type]['show'].fill(true, start-2, start);
        updateshowtable('.showingpreferencetable #' + type +' .videotableitem', areas[type]['show']);
        if (start + 3 >= areas[type]['show'].length) {
            $('#arrowright'+type).css('display', 'block');
        }
        
        if (areas[type]['show'][0] == true) {
            $('#arrowleft'+type).css('display', 'none');
        }
    }

    var classes = $('body').attr('class');
    var course = classes.match(/course\-\d+/gi)[0];

    var changepreference = function(prefer) {
        var url = M.cfg.wwwroot + '/blocks/video/ajax/change_showing_preference.php';
        $.ajax({
            url: url,
            data: {'prefer' : prefer, 
                   'course' : course},
            success: function (response) {
            }
        });
        
    }
    $('#preferlist').click(function() {
        changepreference('list');
        $('#prefertable').removeClass('active');
        $('#preferlist').addClass('active');
        $('.showingpreferencelist').removeClass('hidden');
        $('.showingpreferencetable').addClass('hidden');
    })
    $('#prefertable').click(function() {
        changepreference('table');
        $('#prefertable').addClass('active');
        $('#preferlist').removeClass('active');
        $('.showingpreferencelist').addClass('hidden');
        $('.showingpreferencetable').removeClass('hidden');
    })

    $(".editname").click(function() {
        id = $(this).attr('vidid');
        show =  $(this).attr('show');
        selector = '#videos [rowvid="'+id+'"] .colsubject';
        selector = '.showingpreference'+show+' #videos [rowvid="'+id+'"]';

        $(selector+' span').addClass('hidden');
        $(selector+' i').addClass('hidden');
        $(selector+' form').css('display', 'block');

    })

    $(".videovisible").click(function() { 
        var url = M.cfg.wwwroot + '/blocks/video/ajax/changevisibility.php';
        vidid = $(this).attr('vidid');
        selector = 'i.blockvideo_i.videovisible[vidid="'+vidid+'"]';
        show =  $(this).attr('show');
        if ($(selector+'').hasClass('fa-eye')) {
            hidden = 1;
        } else {
            hidden = 0;
        }
        $.ajax({
            url: url,
            data: {
                'vidid' : vidid, 
                'hidden' : hidden
                },
            success: function (response) {
                if ($(selector+'').hasClass('fa-eye-slash')) {
                    $(selector+'').removeClass('fa-eye-slash');
                    $(selector+'').addClass('fa-eye');
                    $(selector+'').parent().parent().removeClass('hiddenfromstudents');
                    $('.textname a[vidid="'+vidid+'"]').removeClass('hiddenfromstudents');
                    $('.colsubject a[vidid="'+vidid+'"]').removeClass('hiddenfromstudents');
                } else {
                    $(selector+'').removeClass('fa-eye');
                    $(selector+'').addClass('fa-eye-slash');
                    $(selector+'').parent().parent().addClass('hiddenfromstudents');
                    $('.textname a[vidid="'+vidid+'"]').addClass('hiddenfromstudents');
                    $('.colsubject a[vidid="'+vidid+'"]').addClass('hiddenfromstudents');
                }
            }
        });
    });

    $('form.formeditname').submit(function(event) {
        event.preventDefault();
        var url = M.cfg.wwwroot + '/blocks/video/ajax/changenamevideo.php';
        vidid = $(this).attr('datavideo');
        show =  $(this).attr('show');
        selector = '.showingpreference'+show+' #videos [rowvid="'+id+'"]';
        subselector = '#videos [rowvid="'+id+'"]';
        
        text = $(selector+' .formeditname [name="videoname"]').val();
        $.ajax({
            url: url,
            data: {'vidid' : vidid, 
                   'videoname' : text,
                   'course' : course},
            success: function (response) {    
                $(selector+' span').removeClass('hidden').text(text);
                $(selector+' i').removeClass('hidden');
                $(selector+' form').css('display', 'none');
                $(subselector + ' span').text(text);
                $(subselector + ' .textname a').attr('title', text);
                $(subselector + ' .colsubject a').attr('title', text);
            }
        });
    })
  

    var listvideos = [];
    var types = ['videos', 'zoomvideos'];
    $('.showingpreferencelist .bodytable .row').each(function() {
        var item = [];
        for (part in types) {
            if ($(this).parents('#'+types[part]).length) {
                item['type'] = types[part]; 
            }
        }
        item['rowvid'] = $(this).attr('rowvid');
        item['show'] = 'list';
        item['name'] = $(this).find('.colsubject span').text();
        listvideos.push(item);
        
    })

    $('input.inputsearch').keyup(function() {

        var listvideos = [];
        var types = ['videos', 'zoomvideos'];
        $('.showingpreferencelist .bodytable .row').each(function() {
        var item = [];
        for (part in types) {
            if ($(this).parents('#'+types[part]).length) {
                item['type'] = types[part]; 
            }
        }
        item['rowvid'] = $(this).attr('rowvid');
        item['show'] = 'list';
        item['name'] = $(this).find('.colsubject span').text();
        listvideos.push(item);
        })

        $('.nosearchresult').css('display', 'none');
        shows = [];
        shows['videos'] = 0;
        shows['zoomvideos'] = 0;
        for (let i = 0; i < listvideos.length; i++) {
            if (listvideos[i]['name'].indexOf(this.value) == -1) {
                $('.showingpreferencelist .bodytable .row[rowvid="'+listvideos[i]['rowvid'] +'"]').css('display', 'none');
                $('.showingpreferencetable .videotableitem[id="'+listvideos[i]['rowvid'] +'"]').attr('dissearch', 'false').css('display', 'none');
            } else {
                $('.showingpreferencelist .bodytable .row[rowvid="'+listvideos[i]['rowvid'] +'"]').css('display', 'flex');
                $('.showingpreferencetable .videotableitem[id="'+listvideos[i]['rowvid'] +'"]').attr('dissearch', 'true').css('display', 'inline-block');
                shows[listvideos[i]['type']]++;
            }
        }
        flag = false;
        for (part in shows) {
            if (shows[part] == 0) {
                $('.showingpreferencetable #' + part).css('display', 'none');
                $('.showingpreferencelist #' + part).css('display', 'none');
            } else {
                $('.showingpreferencetable #' + part).css('display', 'block');
                $('.showingpreferencelist #' + part).css('display', 'block');
                flag = true;
            }
        }
        if (flag == false) {
            $('.nosearchresult').css('display', 'block');
        }
        updateshowingtable();
    })
})
