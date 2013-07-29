
var Criterion = new function()
{
    var self = this;
    var poller = false;
    var pollerInterval = 5000;

    // Text variables
    var text = {
        project_delete: 'Are you sure you wish to delete this project?'
    }

    // Routing variables
    var routes = {
        project_delete: '/project/delete/',
        test_status: '/test/status/'
    }

    // Element variables

    // Get on it
    this.init = function()
    {
        $('.timeago').timeago();

        $('#addProject').on('shown', function ()
        {
            $('#repo_url').focus();
        });

        $('.delete_project').on('click', function()
        {
            self.project.delete(this);
        });

        $('#show_ssh_keys').on('click', function()
        {
            $(this).parent().hide();
            $('#ssh_keys').show();
        });

        $('#edit_project').on('click', function()
        {
            $('#edit_project_row').toggle();
        });


        $('.project_nav li').on('click', function() {
            $('.project_nav li a').removeClass('active');
            $(this).find('a').addClass('active');

            var content = $(this).find('a').data('content');
            $('.project_content').hide();
            $('#' + content).show();
        });

        $('.btn-add-project,.close').on('click', function()
        {
            $('#addProject').toggle();
        });

        // Hack to check if we're on a test page
        if(window.location.href.indexOf("test") > -1) 
        {
            var url = window.location.href;
            url = url.split("/");
            
            // get the project ID from the last part of the URL
            self.test.initPoller(url[url.length - 1], 1000);
        }
    }

    // Project functions
    this.project = {
        delete : function(el)
        {
            var el = $(el);

            var id = el.data('id');
            var sure = confirm(text.project_delete);

            if (sure) {
                window.location.href = routes.project_delete + id;
            } else {
                el.removeAttr('disabled');
                return false;
            }
        }
    }

    // Test functions
    this.test = {
        initPoller: function(id, interval)
        {
            if (interval) {
                pollerInterval = interval;
            }

            poller = setInterval(function()
            {
                self.test.getStatus(id);
            }, pollerInterval);
        },

        getStatus: function(id)
        {
            self.request(routes.test_status + id, 'get', '', function(data)
            {
                var status_class = 'info';

                if (data.status.code == '1') {
                    status_class = 'success';
                } else if (data.status.code == '3') {
                    status_class = 'warning';
                } else if (data.status.code == '0') {
                    status_class = 'important';
                }

                // Stop test, and show "re-test" button
                if (data.test_again !== false) {
                    clearInterval(poller);
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
                    $.each(data.log, function(key, val)
                    {
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
                                self.nl2br(val.output) +
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

    // Request function
    this.request = function(url, method, data, func)
    {
        $.ajax({
            url: url,
            data: data,
            method: method
        }).done(function(data)
        {
            func(data);
        }).fail(function(jqXHR, textStatus, textError)
        {
             if(poller)
                clearInterval(poller);

             throw new Error(textStatus + " " + textError);
        });
    }

    this.nl2br = function(str, is_xhtml)
    {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
    }
}

