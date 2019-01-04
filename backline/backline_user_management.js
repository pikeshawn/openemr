'use strict';
var createBacklineUser;
var deleteBacklineUser;

(function () {
    var rootUrl = window.location.protocol + '//' + window.location.host;



    createBacklineUser = function createBacklineUser(userId, elementId) {

        jQuery('#' + elementId).html('creating...');

        jQuery.get(rootUrl + '/backline/create_backline_user.php?id=' + userId, function (data) {
            var response =  JSON.parse(data);
            if (response.error_code) {
                alert('Could not create backline user: ' + response.error_code + ' - ' + response.error_text);
                jQuery('#' + elementId).html('No <a onclick=\"createBacklineUser(\'' + userId + '\', \'' + elementId + '\')\" >[Create Backline Act.]</a>');
            } else {
                jQuery('#' + elementId).html('Yes <a onclick=\"deleteBacklineUser(\'' + userId + '\', \'' + elementId + '\')\" >[Delete Backline Act.]</a>');
            }


        });
    };


    deleteBacklineUser = function createBacklineUser(userId, elementId) {
        jQuery('#' + elementId).html('deleting...');

        jQuery.get(rootUrl + '/backline/create_backline_user.php?delete=true&id=' + userId, function (data) {
            var response =  JSON.parse(data);
            if (response.error_code) {
                alert('Could not delete backline user: ' + response.error_code + ' - ' + response.error_text);
                jQuery('#' + elementId).html('Yes <a onclick=\"deleteBacklineUser(\'' + userId + '\', \'' + elementId + '\')\" >[Delete Backline Act.]</a>');
            } else {
                jQuery('#' + elementId).html('No <a onclick=\"createBacklineUser(\'' + userId + '\', \'' + elementId + '\')\" >[Create Backline Act.]</a>');

            }


        });
    };

})();

