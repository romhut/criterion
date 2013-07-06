$(document).ready(function() {
    $('.timeago').timeago();
});

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

var criterion = {
    test : {
        getStatus : function(id) {
            $.get('/test/status/' + id, function(data) {

                if (data.status.code == '1') {
                    var status_class = 'success';
                    clearInterval(build);
                } else if (data.status.code == '3') {
                    var status_class = 'warning';
                } else if (data.status.code == '0') {
                    var status_class = 'important';
                } else {
                    var status_class = 'info';
                }

                $('#status').text(data.status.message);
                $('#status').removeClass().addClass('label').addClass('label-' + status_class);
                if (typeof data.commit != 'undefined') {

                    if (data.commit.url) {
                        $('#commit-hash').html(
                            '<a href="' + data.commit.url + '">' +
                                data.commit.hash.short +
                            '</a>'
                        );
                    } else {
                        $('#commit-hash').text(data.commit.hash.short);
                    }


                    $('#commit-author').text(data.commit.author.name);

                    if (data.commit.branch.url) {
                        $('#branch').html(
                            '<a href="' + data.commit.branch.url + '">' +
                                data.commit.branch.name +
                            '</a>'
                        );
                    } else {
                        $('#branch').text(data.commit.branch.name);
                    }
                }

                if (data.log.length > 0) {
                    $('#logs').html('');
                    $.each(data.log, function(key, val) {

                        if (val.response == '0') {
                            var hide_class = 'hide';
                            var alert = 'success';
                        } else {
                            var hide_class = false;
                            var alert = 'error';
                        }

                        if (val.output == '') {
                            val.output = 'There is no output for this command.';
                        }

                        $('#logs').append(
                        '<div>' +
                            '<a href="javascript:void(0)" class="output-log">' +
                                '<p class="alert alert-'+alert+' ">' +
                                    val.command +
                                '</p>' +
                            '</a>' +
                            '<div class="well well-small ' + hide_class + '">' +
                                nl2br(val.output) +
                            '</div>' +
                        '</div>');
                    });

                    $('.output-log').on('click', function(){
                        $(this).next().toggle();
                    });
                }

            });
        }
    }
}

