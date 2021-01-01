/**
 * js for debate page.
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
                $(document).on('click', '.mod-debate-positive-icon', function() {
                    var result = $.getAllocation('positive');
                    if ($("#mod-debate-response-input").length === 0 && result) {
                        responseType = 1;
                        responseId = '#mod-debate-pos-side';
                        debateView.debateResponse(userFullName, userImageURL,
                            'mod-debate-positive-response',
                            responseId, userID, courseID, debateID, cmID, responseType);
                    }
                });
                $(document).on('click', '.mod-debate-negative-icon', function() {
                    var result = $.getAllocation('negative');
                    if ($("#mod-debate-response-input").length === 0 && result) {
                        responseType = 0;
                        responseId = '#mod-debate-neg-side';
                        debateView.debateResponse(userFullName, userImageURL,
                            'mod-debate-negative-response',
                            responseId, userID, courseID, debateID, cmID, responseType);
                    }
                });
                $(document).on('click', '#mod-debate-cancel-respose', function() {
                    $('#' + userID + '-mod-debate').remove();
                    $("div").remove(".mod-debate-find-response");
                });
                $(document).on('click', '#mod-debate-update-response', function() {
                    var userResponse = $("#mod-debate-response-input").val();
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
                            $('#' + userID + '-mod-debate').remove();
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
                });
            },
            debateResponse: function(userFullName, userImageURL, responseClass,
                                     responseId, userID, courseID, debateID, cmID, responseType) {
                var context = {
                    response_class: responseClass,
                    user_profile_image: userImageURL,
                    user_full_name: userFullName,
                    userid: userID + '-mod-debate',
                    courseid: courseID,
                    debateid: debateID,
                    cmid: cmID,
                    responsetype: responseType
                };
                templates.render('mod_debate/debate_response', context).then(function (html, js) {
                    var debateResponse = $(responseId);
                    debateResponse.after(html);
                    templates.runTemplateJS(js);
                }).fail(notification.exception);
            }
        };
        return debateView;
    });
