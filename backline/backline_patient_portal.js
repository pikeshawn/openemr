'use strict';
(function () {
    var _blOldClearActive;
    var _blOldSetPatient;
    var _insertedTdSendMessageEl;
    var _insertedTdPCCMessageEl;
    var _backlineChatLinkEl;
    var _backlineChatLinkBadgeEl;
    var _backlineSidebarButtonEl;
    var _backlineSidebarBadgeEl;
    var _lastPatientPid;
    var _patientQueryIntervalHandle;

    var _backlineData;//Cached copy of the last call to backline_injector
    var _backlinePatientData;//Cached copy of the last call to backline_injector for the current patient
    var rootUrl = window.location.protocol + '//' + window.location.host;


    function injectBacklineStyleSheet(doc) {
        var cssId = 'backlineCSS';  // you could encode the css path itself to generate id..
        if (!doc.getElementById(cssId)) {

            var head = doc.getElementsByTagName('head')[0];
            var link = doc.createElement('link');
            link.id = cssId;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = rootUrl + '/backline/backline.css';
            link.media = 'all';
            head.appendChild(link);
        }
    }

    /**
     * Add a header to the page showing that there are new backline messages.  If there are no unread messsages, the 
     * bar will be hidden
     */
    function addBacklineChatLink(){
        jQuery.get(rootUrl + '/backline/patient_portal_chat.php', function (data) {
            _backlineData =  JSON.parse(data);
            
            
            
            if (!_backlineChatLinkEl) {
                _backlineChatLinkEl = jQuery('<a href="" class="backline-patient-portal-chat-link" href="" onclick="return top.window.parent.left_nav.loadBacklineFrame(\'adm0\',\'RTop\',\'user\')" target="_blank"><img class="backline-chat-logo" src="../backline/chat_logo.png" /><h1>Chat with your Care Provider</h1><div><!--You have <span class="badge"></span> unread backline messages --><small>click here to start a backline chat session</small></div></a>');
                _backlineChatLinkBadgeEl = _backlineChatLinkEl.find('.badge');
                jQuery(window.document.body).append(_backlineChatLinkEl);
            }
            _backlineChatLinkEl.attr('href', _backlineData.url);

            if (_backlineData.error_code) {
                alert('Backline error, patient chat will be unavailable: ' + _backlineData.error_code + ' - ' + _backlineData.error_text);

            } else {
                _backlineChatLinkBadgeEl.html(_backlineData.unread_count);
                _backlineChatLinkEl.show();
            }
        });
    }


    injectBacklineStyleSheet(document);
    addBacklineChatLink();


})();