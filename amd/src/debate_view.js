/**
 * js for debate page.
 *
 * @package     mod_debate
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/config', 'core/notification', 'core/templates'],
    function($, AJAX, str, mdlcfg, notification, templates) {
        var debateView = {
            init: function(userFullName, userImageURL, userID) {
                $(document).on('click', '.mod-debate-positive-icon', function() {
                    if ($("#mod-debate-response-input").length === 0) {
                        debateView.debateResponse(userFullName, userImageURL,
                            'mod-debate-positive-response',
                            '#mod-debate-pos-side', userID);
                    }
                });
                $(document).on('click', '.mod-debate-negative-icon', function() {
                    if ($("#mod-debate-response-input").length === 0) {
                        debateView.debateResponse(userFullName, userImageURL,
                            'mod-debate-negative-response',
                            '#mod-debate-neg-side', userID);
                    }
                });
                $(document).on('click', '#mod-debate-cancel-respose', function() {
                    $('#' + userID + '-mod-debate').remove();
                });
            },
            debateResponse: function(userFullName, userImageURL, responseClass, responseId, userID) {
                var context = {
                    response_class: responseClass,
                    user_profile_image: userImageURL,
                    user_full_name: userFullName,
                    userid: userID + '-mod-debate'
                };
                templates.render('mod_debate/debate_response', context).then(function (html, js) {
                    var debateResponse = $(responseId);
                    debateResponse.after(html);
                }).fail(notification.exception);
            }
        };
        return debateView;
    });
