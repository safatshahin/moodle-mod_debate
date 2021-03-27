/**
 * Js for debate page.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/config', 'core/notification', 'core/templates'],
    function ($, AJAX, str,
              mdlcfg, notification, templates) {
        var debateView = {
            init: function (userFullName, userImageURL, userID, courseID, debateID,
                            responseAllowed, positiveResponse, negativeResponse, userCapability,
                            userEditCapability, userDeleteCapability) {
                // VARIABLES TO MAINTAIN THE FRONTEND FEATURES
                var responseType = 0;
                var responseId = '';
                var responseTextID = '';
                var id = null;
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
                        var userResponsetext = $(responseTextID).val();
                        if (userResponsetext.length > 0) {
                            var responseAjax = AJAX.call([{
                                methodname: 'mod_debate_find_debate_respose',
                                args: {
                                    courseid: courseID,
                                    debateid: debateID,
                                    response: userResponsetext,
                                    responsetype: responseType
                                }
                            }]);
                            responseAjax[0].done(function (found) {
                                if (found.result) {
                                    var getResponses = JSON.parse(found.data);
                                    var context = {
                                        found_response: getResponses
                                    };
                                    if ($('#mod-debate-insert-postive-response').is(":visible")
                                        || $('#mod-debate-insert-negative-response').is(":visible")) {
                                        templates.render('mod_debate/debate_find_response', context).then(function (html, js) {
                                            var debateResponse = $(responseId);
                                            debateResponse.after(html);
                                        }).fail(notification.exception);
                                    }
                                }
                            }).fail(notification.exception);
                        }
                    }, 700);
                });
                // DELETE RESPONSE
                $(document).on('click', '.mod-debate-negative-delete', function () {
                    id = $(this).attr("data-id");
                    if (negativeResponse > 0) {
                        negativeResponse = negativeResponse - 1;
                    }
                    debateView.deleteResponse(courseID, debateID, id);
                });
                $(document).on('click', '.mod-debate-positive-delete', function () {
                    id = $(this).attr("data-id");
                    if (positiveResponse > 0) {
                        positiveResponse = positiveResponse - 1;
                    }
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
                                courseid: courseID,
                                debateid: debateID,
                                debatetype: parseInt(responseAllowed),
                                attribute: 'positive',
                                positive_response: positiveResponse,
                                negative_response: negativeResponse,
                                userid: userID
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
                                id = null;
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
                                courseid: courseID,
                                debateid: debateID,
                                debatetype: parseInt(responseAllowed),
                                attribute: 'negative',
                                positive_response: positiveResponse,
                                negative_response: negativeResponse,
                                userid: userID
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
                                id = null;
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
                });
                // UPDATE OR SAVE NEW OR EDITED RESPONSE
                $(document).on('click', '#mod-debate-update-response', function () {
                    $("div").remove(".mod-debate-find-response");
                    var userResponse = $(responseTextID).val();
                    if (userResponse.length > 0) {
                        var responseCall = AJAX.call([{
                            methodname: 'mod_debate_add_debate_respose',
                            args: {
                                courseid: courseID,
                                debateid: debateID,
                                response: userResponse,
                                responsetype: responseType,
                                id: id
                            }
                        }]);
                        responseCall[0].done(function (output) {
                            if (output.result) {
                                if (responseType === 0) {
                                    negativeResponse++;
                                } else {
                                    positiveResponse++;
                                }
                                $(responseId).css('display', 'none');
                                $("div").remove(".mod-debate-find-response");
                                $(elementidContainer).remove();
                                if (id === null && $.isNumeric(output.id)) {
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
                                    id = null;
                                    $("div").remove(".mod-debate-find-response");
                                    elementidContainer = '';
                                    elementid = '';
                                }).fail(notification.exception);
                            } else {
                                debateView.renderNotification(2, 'error');
                            }
                            $("div").remove(".mod-debate-find-response");
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
                                courseid: courseID,
                                debateid: debateID,
                                id: id
                            }
                        }]);
                        responseAjax[0].done(function (deleted) {
                            if (deleted.result) {
                                $(elementidContainer).remove();
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
                    {'key': 'error_delete', component: 'mod_debate'}
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
