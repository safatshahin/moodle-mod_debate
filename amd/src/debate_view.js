/**
 * Js for debate page.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/config', 'core/notification', 'core/templates'],
    function ($, AJAX, str,
              mdlcfg, notification, templates) {
        var debateView = {
            init: function (userFullName, userImageURL, userID, courseID, debateID,
                            userCapability, userEditCapability, userDeleteCapability) {
                // VARIABLES TO MAINTAIN THE FRONTEND FEATURES
                var responseType = 0;
                var responseId = '';
                var responseTextID = '';
                var id = 0;
                var elementid = '';
                var elementidContainer = '';
                var editID = '';
                var deleteID = '';
                // DELAY TIMER FOR THE USER TO FINISH TYPING TO FIND POSSIBLE MATCH
                var delay = (function () {
                    var timer = 0;
                    return function (callback, ms) {
                        clearTimeout(timer);
                        timer = setTimeout(callback, ms);
                    };
                })();
                // ANIMATE TO THE TOP OF THE PAGE FOR EDITING RESPONSE
                $.animateToDiv = function () {
                    $('html, body').animate({
                        scrollTop: $(".mod-debate-topic-container").offset().top
                    }, 2000);
                };
                // POSSIBLE MATCH WITH CURRENTLY TYPED RESPONSE
                $(".mod-debate-response-input").keyup(function () {
                    delay(function () {
                        $("div").remove(".mod-debate-find-response");
                        $("div").remove(".mod-debate-sentiment-container");
                        var userResponsetext = $(responseTextID).val();
                        if (userResponsetext.length > 0) {
                            toxicity.load(0.9).then(model => {
                                model.classify(userResponsetext).then(predictions => {
                                    var responseAjax = AJAX.call([{
                                        methodname: 'mod_debate_find_debate_respose',
                                        args: {
                                            courseid: parseInt(courseID),
                                            debateid: parseInt(debateID),
                                            response: userResponsetext,
                                            responsetype: parseInt(responseType),
                                            sentiment: JSON.stringify(predictions)
                                        }
                                    }]);
                                    responseAjax[0].done(function (found) {
                                        if (found.result) {
                                            var getResponses = JSON.parse(found.data);
                                            var sentiment = JSON.parse(found.sentiment);
                                            var sentimentContext = {
                                                found_sentiment: sentiment,
                                            };
                                            var context = {
                                                found_response: getResponses,
                                            };
                                            if ($('#mod-debate-insert-postive-response').is(":visible")
                                                || $('#mod-debate-insert-negative-response').is(":visible")) {
                                                templates.render('mod_debate/debate_find_response', context).then(function (html, js) {
                                                    var debateResponse = $(responseId);
                                                    debateResponse.after(html);
                                                }).fail(notification.exception);
                                                templates.render('mod_debate/debate_sentiment', sentimentContext).then(function (html, js) {
                                                    var debateResponse = $(responseId);
                                                    debateResponse.after(html);
                                                }).fail(notification.exception);
                                            }
                                        }
                                    }).fail(notification.exception);
                                });
                            });

                        }
                    }, 700);
                });
                // DELETE RESPONSE
                $(document).on('click', '.mod-debate-negative-delete', function () {
                    id = $(this).attr("data-id");
                    debateView.deleteResponse(courseID, debateID, id);
                });
                $(document).on('click', '.mod-debate-positive-delete', function () {
                    id = $(this).attr("data-id");
                    debateView.deleteResponse(courseID, debateID, id);
                });
                // EDIT RESPONSE
                $(document).on('click', '.mod-debate-negative-edit', function () {
                    id = $(this).attr("data-id");
                    elementid = '#element' + id;
                    elementidContainer = '#element' + id + 'container';
                    if ($('#mod-debate-insert-postive-response').is(":hidden")
                        && $('#mod-debate-insert-negative-response').is(":hidden")) {
                        var text = $(elementid).text().trim();
                        responseType = 0;
                        responseId = '#mod-debate-insert-negative-response';
                        responseTextID = '#mod-debate-negative-response-input';
                        editID = 'mod-debate-negative-edit';
                        deleteID = 'mod-debate-negative-delete';
                        $(responseTextID).val(text);
                        $(responseTextID).html(text);
                        $(elementidContainer).css('display', 'none');
                        $(responseId).css('display', 'block');
                        $.animateToDiv();
                    } else {
                        debateView.renderNotification(0, 'info');
                    }
                });
                $(document).on('click', '.mod-debate-positive-edit', function () {
                    id = $(this).attr("data-id");
                    elementid = '#element' + id;
                    elementidContainer = '#element' + id + 'container';
                    if ($('#mod-debate-insert-negative-response').is(":hidden")
                        && $('#mod-debate-insert-postive-response').is(":hidden")) {
                        var text = $(elementid).text().trim();
                        responseType = 1;
                        responseId = '#mod-debate-insert-postive-response';
                        responseTextID = '#mod-debate-positive-response-input';
                        editID = 'mod-debate-positive-edit';
                        deleteID = 'mod-debate-positive-delete';
                        $(responseTextID).val(text);
                        $(responseTextID).html(text);
                        $(elementidContainer).css('display', 'none');
                        $(responseId).css('display', 'block');
                        $.animateToDiv();
                    } else {
                        debateView.renderNotification(0, 'info');
                    }
                });
                // ADD RESPONSE
                $(document).on('click', '.mod-debate-positive-icon', function () {
                    if ($('#mod-debate-insert-negative-response').is(":hidden")
                        && $('#mod-debate-insert-postive-response').is(":hidden")) {
                        var allocationAjax = AJAX.call([{
                            methodname: 'mod_debate_check_response_allocation',
                            args: {
                                debateid: parseInt(debateID),
                                attribute: 'positive',
                                userid: parseInt(userID)
                            }
                        }]);
                        allocationAjax[0].done(function (output) {
                            var result = output.result;
                            if (result) {
                                responseType = 1;
                                responseId = '#mod-debate-insert-postive-response';
                                responseTextID = '#mod-debate-positive-response-input';
                                editID = 'mod-debate-positive-edit';
                                deleteID = 'mod-debate-positive-delete';
                                $(responseTextID).val('');
                                $(responseId).css('display', 'block');
                                id = 0;
                            } else {
                                notification.addNotification({
                                    message: output.message,
                                    type: 'info'
                                });
                            }
                        }).fail(notification.exception);
                    } else {
                        debateView.renderNotification(0, 'info');
                    }
                });
                $(document).on('click', '.mod-debate-negative-icon', function () {
                    if ($('#mod-debate-insert-postive-response').is(":hidden")
                        && $('#mod-debate-insert-negative-response').is(":hidden")) {
                        var allocationAjax = AJAX.call([{
                            methodname: 'mod_debate_check_response_allocation',
                            args: {
                                debateid: parseInt(debateID),
                                attribute: 'negative',
                                userid: parseInt(userID)
                            }
                        }]);
                        allocationAjax[0].done(function (output) {
                            var result = output.result;
                            if (result) {
                                responseType = 0;
                                responseId = '#mod-debate-insert-negative-response';
                                responseTextID = '#mod-debate-negative-response-input';
                                editID = 'mod-debate-negative-edit';
                                deleteID = 'mod-debate-negative-delete';
                                $(responseTextID).val('');
                                $(responseId).css('display', 'block');
                                id = 0;
                            } else {
                                notification.addNotification({
                                    message: output.message,
                                    type: 'info'
                                });
                            }
                        }).fail(notification.exception);
                    } else {
                        debateView.renderNotification(0, 'info');
                    }
                });
                // CANCEL ADD RESPONSE
                $(document).on('click', '#mod-debate-cancel-respose', function () {
                    $(responseId).css('display', 'none');
                    $(elementidContainer).css('display', 'block');
                    $("div").remove(".mod-debate-find-response");
                    $("div").remove(".mod-debate-sentiment-container");
                });
                // UPDATE OR SAVE NEW OR EDITED RESPONSE
                $(document).on('click', '#mod-debate-update-response', function () {
                    $("div").remove(".mod-debate-find-response");
                    $("div").remove(".mod-debate-sentiment-container");
                    var userResponse = $(responseTextID).val();
                    if (userResponse.length > 0) {
                        var responseCall = AJAX.call([{
                            methodname: 'mod_debate_add_debate_respose',
                            args: {
                                courseid: parseInt(courseID),
                                debateid: parseInt(debateID),
                                response: userResponse,
                                responsetype: parseInt(responseType),
                                id: parseInt(id)
                            }
                        }]);
                        responseCall[0].done(function (output) {
                            if (output.result) {
                                $(responseId).css('display', 'none');
                                $("div").remove(".mod-debate-find-response");
                                $("div").remove(".mod-debate-sentiment-container");
                                $(elementidContainer).remove();
                                if (id === 0 && $.isNumeric(output.id)) {
                                    id = output.id;
                                }
                                var outputContext = {
                                    user_profile_image: userImageURL,
                                    user_full_name: userFullName,
                                    response: userResponse,
                                    elementidcontainer: 'element' + id + 'container',
                                    elementid: 'element' + id,
                                    user_capability: userCapability,
                                    user_edit_capability: userEditCapability,
                                    user_delete_capability: userDeleteCapability,
                                    id: id,
                                    editid: editID,
                                    deleteid: deleteID
                                };
                                templates.render('mod_debate/debate_response_output', outputContext).then(function (html, js) {
                                    var outputResponse = $(responseId);
                                    outputResponse.after(html);
                                    id = 0;
                                    $("div").remove(".mod-debate-find-response");
                                    $("div").remove(".mod-debate-sentiment-container");
                                    elementidContainer = '';
                                    elementid = '';
                                }).fail(notification.exception);
                            } else {
                                debateView.renderNotification(2, 'error');
                            }
                            $("div").remove(".mod-debate-find-response");
                            $("div").remove(".mod-debate-sentiment-container");
                        }).fail(notification.exception);
                    } else {
                        debateView.renderNotification(1, 'info');
                    }
                });
            },
            deleteResponse: function (courseID, debateID, id) {
                str.get_strings([
                    {'key': 'confirm_delete', component: 'mod_debate'}
                ]).done(function (s) {
                    if (confirm(s[0])) {
                        var elementidContainer = '#element' + id + 'container';
                        var responseAjax = AJAX.call([{
                            methodname: 'mod_debate_delete_debate_respose',
                            args: {
                                courseid: parseInt(courseID),
                                debateid: parseInt(debateID),
                                id: parseInt(id)
                            }
                        }]);
                        responseAjax[0].done(function (deleted) {
                            if (deleted.result) {
                                $(elementidContainer).remove();
                                debateView.renderNotification(4, 'info');
                            } else {
                                debateView.renderNotification(3, 'info');
                            }
                        }).fail(notification.exception);
                    }
                });
            },
            renderNotification: function (notificationValue, notificationType) {
                var strings = [
                    {'key': 'edit_mode_active', component: 'mod_debate'},
                    {'key': 'empty_response', component: 'mod_debate'},
                    {'key': 'error_add', component: 'mod_debate'},
                    {'key': 'error_delete', component: 'mod_debate'},
                    {'key': 'success_delete', component: 'mod_debate'}
                ];
                str.get_strings(strings).then(function (results) {
                    notification.addNotification({
                        message: results[notificationValue],
                        type: notificationType
                    });
                });
            }
        };
        return debateView;
    });
