/**
 * Js for debate page.
 *
 * @package     mod_debate
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/config', 'core/notification', 'core/templates'],
    function($, AJAX, str,
             mdlcfg, notification, templates) {
        var debateView = {
            init: function(userFullName, userImageURL, userID, courseID, debateID, cmID,
                           responseAllowed, positiveResponse, negativeResponse) {
                var responseType = 0;
                var responseId = '';
                var responseTextID = '';
                var delay = (function() {
                    var timer = 0;
                    return function(callback, ms) {
                        clearTimeout(timer);
                        timer = setTimeout(callback, ms);
                    };
                })();
                $.getAllocation = function(attr) {
                    var result = true;
                    switch (responseAllowed) {
                        case '0':
                            // UNLIMITED RESPONSE
                            break;
                        case '1':
                            // ONE RESPONSE IN ANY ONE SIDE
                            if (positiveResponse > 0 || negativeResponse > 0) {
                                result = false;
                            }
                            break;
                        case '2':
                            // ONE RESPONSE IN EACH SIDE
                            if (positiveResponse > 0 && negativeResponse > 0) {
                                result = false;
                            } else if (attr === 'positive' && positiveResponse > 0) {
                                result = false;
                            } else if (attr === 'negative' && negativeResponse > 0) {
                                result = false;
                            }
                            break;
                    }
                    return result;
                };
                $(".mod-debate-response-input").keyup(function() {
                    delay(function() {
                        $("div").remove(".mod-debate-find-response");
                        var userResponsetext = $(responseTextID).val();
                        if (userResponsetext.length > 0) {
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
                                        var debateResponse = $(responseId);
                                        debateResponse.after(html);
                                    }).fail(notification.exception);
                                }
                            }).fail(notification.exception);
                        }
                    }, 700);
                });
                $(document).on('click', '.mod-debate-positive-icon', function() {
                    var result = $.getAllocation('positive');
                    if (result && $('#mod-debate-insert-negative-response').is(":hidden")) {
                        responseType = 1;
                        responseId = '#mod-debate-insert-postive-response';
                        responseTextID = '#mod-debate-positive-response-input';
                        $(responseTextID).val('');
                        $(responseId).css('display', 'block');
                    }
                });
                $(document).on('click', '.mod-debate-negative-icon', function() {
                    var result = $.getAllocation('negative');
                    if (result && $('#mod-debate-insert-postive-response').is(":hidden")) {
                        responseType = 0;
                        responseId = '#mod-debate-insert-negative-response';
                        responseTextID = '#mod-debate-negative-response-input';
                        $(responseTextID).val('');
                        $(responseId).css('display', 'block');
                    }
                });
                $(document).on('click', '#mod-debate-cancel-respose', function() {
                    $(responseId).css('display', 'none');
                    $("div").remove(".mod-debate-find-response");
                });
                $(document).on('click', '#mod-debate-update-response', function() {
                    $("div").remove(".mod-debate-find-response");
                    var userResponse = $(responseTextID).val();
                    if (userResponse.length > 0) {
                        var responseCall = AJAX.call([{
                            methodname: 'mod_debate_add_debate_respose',
                            args: {
                                courseid: courseID,
                                debateid: debateID,
                                cmid: cmID,
                                response: userResponse,
                                responsetype: responseType
                            }
                        }]);
                        responseCall[0].done(function(result) {
                            if (result) {
                                if (responseType === 0) {
                                    negativeResponse++;
                                } else {
                                    positiveResponse++;
                                }
                                $(responseId).css('display', 'none');
                                $("div").remove(".mod-debate-find-response");
                                var outputContext = {
                                    user_profile_image: userImageURL,
                                    user_full_name: userFullName,
                                    response: userResponse
                                };
                                templates.render('mod_debate/debate_response_output', outputContext).then(function (html, js) {
                                    var outputResponse = $(responseId);
                                    outputResponse.after(html);
                                }).fail(notification.exception);
                            } else {
                                //error checking
                            }
                        }).fail(notification.exception);
                    }
                });
            }
        };
        return debateView;
    });
