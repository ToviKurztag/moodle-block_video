require(['jquery' , 'jqueryui', 'core/config'], function($, CFG) {

   
        $(document).ready(function() {
            $(".datepicker").datepicker({  dateFormat: 'dd-mm-yy', autoclose: true });
          });
        

    var listvideos = [];
    $('#choosevideo_table .rowtable').each(function() {
        var item = [];
        item['vid'] = $(this).find('.id').text();
        item['name'] = $(this).find('.name').text();
        date = new Date($(this).find('.date').attr('date'));
        
        item['owner'] = $(this).find('.owner').text();
        item['date'] = date;
        item['showtext'] = true;
        item['showstartdate'] = true;
        item['showenddate'] = true;
        listvideos.push(item);
        
    })

    $('#searchtext').keyup(function() {
        for (let i = 0; i < listvideos.length; i++) {
            if (listvideos[i]['name'].indexOf(this.value) == -1 &&
                listvideos[i]['owner'].indexOf(this.value) == -1 &&
                listvideos[i]['vid'].indexOf(this.value) == -1
                ) {
                listvideos[i]['showtext'] = false;
                $('#row_' + listvideos[i]['vid']).css('display', 'none');
            } else {
                listvideos[i]['showtext'] = true;
                if (listvideos[i]['showstartdate'] && listvideos[i]['showenddate']) {
                    $('#row_' + listvideos[i]['vid']).css('display', 'table-row');
                }
            }
        }
    })
    
    $('#searchstartdate').change(function() {      
        // var date = this.value.datepicker({ dateFormat: 'dd-mm-yy' }).value;

        var d = $(this).val();
        d = $.datepicker.parseDate("dd-mm-yy", d);
        var startdate = new Date(d);


        // var startdate = new Date(date);
        startdate.setDate(startdate.getDate() - 1);
        for (let i = 0; i < listvideos.length; i++) {
            date = listvideos[i]['date'];
            // date.setDate(date.getDate() + 1);
            if (date < startdate ) {
                listvideos[i]['showstartdate'] = false;
                $('#row_' + listvideos[i]['vid']).css('display', 'none');
            } else {
                listvideos[i]['showstartdate'] = true;
                if (listvideos[i]['showtext'] && listvideos[i]['showenddate']) {
                    $('#row_' + listvideos[i]['vid']).css('display', 'table-row');
                }
            }
        }
    })

    $('#searchenddate').change(function() {

        var d = $(this).val();
        d = $.datepicker.parseDate("dd-mm-yy", d);
        var enddate = new Date(d);
        enddate.setDate(enddate.getDate() + 1);
        for (let i = 0; i < listvideos.length; i++) {
            if (listvideos[i]['date'] >= enddate) {
                listvideos[i]['showenddate'] = false;
                $('#row_' + listvideos[i]['vid']).css('display', 'none');
            } else {
                listvideos[i]['showenddate'] = true;
                if (listvideos[i]['showtext'] && listvideos[i]['showstartdate']) {
                    $('#row_' + listvideos[i]['vid']).css('display', 'table-row');
                }
            }
        }
    })
    var classes = $('body').attr('class');
    var course = classes.match(/course\-\d+/gi)[0];
    $('#savechoosevideos').click(function() {
        console.log('click on save');
        data = [];
        $('.videoselected input').each(function() {
            // data[this.name] = this.checked;
            obj = {};
            obj.id = this.name;
            obj.checked = this.checked;
            data.push(obj);
        });
        data = JSON.stringify(data);
        console.log(data);
       
        var url = M.cfg.wwwroot + '/blocks/video/ajax/save_videos_in_course.php';
        $.ajax({
            url: url,
            type: "POST",
            data: {'videos' : data, 
                   'course' : course},
            success: function (response) {
                console.log(response);
                window.location = response ;
            }
        });
    })



})