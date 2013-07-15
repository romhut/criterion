$(document).ready(function() {
    $('.timeago').timeago();

    $('#addProject').on('shown', function () {
        $('#repo_url').focus();
    });

    $('.delete_project').on('click', function() {
        criterion.project.delete(this);
    });

    $('#show_ssh_keys').on('click', function() {
        $(this).parent().hide();
        $('#ssh_keys').show();
    });

    $('#edit_project').on('click', function() {
        $('#edit_project_form').toggle();
    });
});

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

var criterion = {
    project : {
        delete : function(el) {
            var el = $(el);
            el.attr('disabled', true);
            var id = el.data('id');
            var sure = confirm('Are you sure you wish to delete this project?');
            if (sure) {
                window.location.href = '/project/delete/' + id;
            } else {
                el.removeAttr('disabled');
                return false;
            }
        }
    },
    test : {
        getStatus : function(id) {
            $.get('/test/status/' + id, function(data) {

                // Set status_classes
                if (data.status.code == '1') {
                    var status_class = 'success';
                } else if (data.status.code == '3') {
                    var status_class = 'warning';
                } else if (data.status.code == '0') {
                    var status_class = 'important';
                } else {
                    var status_class = 'info';
                }

                // Stop test, and show "re-test" button
                if (data.test_again !== false) {
                    clearInterval(build);
                    if (typeof data.commit != 'undefined') {
                        var branchQuery = 'branch=' + data.commit.branch.name + '&';
                    } else {
                        var branchQuery = '';
                    }
                    $('.test_id').prepend('<a href="/project/run/'+data.project._id+'?'+branchQuery+'test_id='+data._id+'" class="btn btn-success btn-mini" href="#">Re-test</a>');
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

                        if (val.status == '1') {
                            if (val.response == '0') {
                                var alert = 'success';
                            } else {
                                var alert = 'error';
                            }
                        } else {
                            var alert = 'warning';
                        }

                        if (val.output) {
                            var output = '<div class="output">' +
                                nl2br(val.output) +
                            '</div>';

                            var output_href = 'href="javascript:void(0)"';
                        } else {
                            var output_href = '';
                            var output = '';
                        }

                        $('#logs').append(
                        '<div>' +
                            '<a ' + output_href + ' class="output-log">' +
                                '<p class="'+alert+' ">' +
                                    '<span class="dollar">$</span> ' + val.command +
                                '</p>' +
                            '</a>' + output +
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

