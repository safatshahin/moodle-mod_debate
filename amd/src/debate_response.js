// require(['jquery', 'core/ajax', 'core/templates'], function($, AJAX, templates) {
import $ from 'jquery';
import AJAX from 'core/ajax';
import templates from 'core/templates';
import notification from 'core/notification';

var delay = (function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();
$("#mod-debate-response-input").keyup(function() {
    delay(function() {
        $("div").remove(".mod-debate-find-response");
        var userResponsetext = $("#mod-debate-response-input").val();
        var courseID = parseInt($("#mod-debate-response-input").attr('data-course'));
        var debateID = parseInt($("#mod-debate-response-input").attr('data-debate'));
        var cmID = parseInt($("#mod-debate-response-input").attr('data-cm'));
        var responseType = parseInt($("#mod-debate-response-input").attr('data-responsetype'));
        var dataUserID = $("#mod-debate-response-input").attr('data-userid');
        var responseAjax = AJAX.call([{
            methodname: 'mod_debate_find_debate_respose',
            args: {
                courseid: courseID,
                debateid: debateID,
                cmid: cmID,
                response: userResponsetext,
                responsetype: responseType
            }
        }]);
        responseAjax[0].done(function(found) {
            if (found.result) {
                var getResponses = JSON.parse(found.data);
                var context = {
                    found_response: getResponses
                };
                templates.render('mod_debate/debate_find_response', context).then(function(html, js) {
                    var debateResponse = $('#' + dataUserID);
                    debateResponse.after(html);
                }).fail(notification.exception);
            }
        }).fail(notification.exception);
    }, 700);
});
// });
