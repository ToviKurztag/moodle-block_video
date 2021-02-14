// require.config({catchError:true});
// require(['jquery', 'jqueryui', 'datatables', 'core/ajax'], function($, jqueryui, datatables, ajax) {
//     $(document).ready(function() {
//         var table = $("#video_table").DataTable({
//             $.ajax({url: M.cfg.wwwroot + '/blocks/video/get_video_ajax.php',
//                 success: function(data){
//                        return data;             
//                } 
//                ,error: function(error){console.log(error);}
//                 });

           
//             },
//             "order": [[{{order}}, "desc"]],
//             "columns": [
//                 {{#fields}}
//                 {"data": "{{name}}"},
//                 {{/fields}}
//             ],
//             initComplete: function(){
//                 $('.showembed').click(function(e) {
//                     e.preventDefault();
//                     // Find index of line by video id.
//                     for (index = 0; index < table.columns().data()[2].length; ++index) {
//                         if (table.columns().data()[2][index] == $(this).data('id')) {
//                             var myindex = index;
//                         }
//                     }
//                     var qrimg = '<img src="' + M.cfg.wwwroot + '/local/video_directory/qr.php?id=' + $(this).data('id') + '">';
//                     $(".ui-dialog-content").dialog("close");
//                     $('<div id="messagemodal">' + table.columns().data()[8][myindex] + qrimg + '</div>').dialog({width: 650, height: 400});
//                 })
//             }
//         });

//         var clickEmbed = function(){
//             $('.showembed').click(function(e) {
//                 e.preventDefault();
//                 // Find index of line by video id.
//                 for (index = 0; index < table.columns().data()[2].length; ++index) {
//                     if (table.columns().data()[2][index] == $(this).data('id')) {
//                         var myindex = index;
//                     }
//                 }
//                 var qrimg = '<img src="' + M.cfg.wwwroot + '/local/video_directory/qr.php?id=' + $(this).data('id') + '">';
//                 $(".ui-dialog-content").dialog("close");
//                 $('<div id="messagemodal">' + table.columns().data()[8][myindex] + qrimg + '</div>').dialog({width: 650, height: 400});
//             })
//         };

//     var myReload = function(){
//         table.ajax.reload(clickEmbed);
//     }

//     $('#datatable_ajax_reload').click(function() {
//         table.ajax.reload(clickEmbed);
//     });


//     $('#datatable_ajax_clear_tags').click(function() {
//         window.location = 'list.php';
//     });

//     $('#video_table').on('change', '.ajax_edit', function () {
//         var data = this.id.split('_');
//         var field = this.type == 'checkbox' ? 'private' : 'orig_filename';
//         var id = data.pop();
//         var status = this.type == 'checkbox' ? this.checked : null;
//         var value = this.type == 'checkbox' ? null : this.value;
//         var promises = ajax.call([
//             { methodname: 'local_video_directory_edit', args: { videoid: id, field: field, value: value, status: status } }
//         ]);
//     });

//     $('.play_video').click(function () {
//         $("#video_player").show();
//     });
//     // reload table every 60 seconds
//     setTimeout(myReload, 60000);
// });
// }