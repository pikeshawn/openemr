'use strict';

(function () {
    var _blOldClearActive;
    var _blOldSetPatient;
    var _insertedTdSendMessageEl;
    var _insertedTdPCCMessageEl;
    var _backlineHeaderEl;
    var _backlineHeaderBadgeEl;
    var _backlineSidebarButtonEl;
    var _backlineAdminSidebarButtonEl;
    var _backlineSidebarBadgeEl;
    var _lastPatientPid;
    var _patientQueryIntervalHandle;

    var _backlineData;//Cached copy of the last call to backline_injector
    var _backlinePatientData;//Cached copy of the last call to backline_injector for the current patient
    var rootUrl = 'https://openemr.staging.drfirst.com';
    //var rootUrl = window.location.protocol + '//' + window.location.host;


    function injectBacklineStyleSheet(doc) {
        var cssId = 'backlineCSS';  // you could encode the css path itself to generate id..
        console.log(JSON.stringify(doc));
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
    function updateBacklineHeader(){
        if (!_backlineAdminSidebarButtonEl) {
            injectBacklineSidebarAdminButton();
        }

        jQuery.get(rootUrl + '/backline/backline_injector.php', function (data) {
            if (!data.error_code) {
                _backlineData = data;

                if (!_backlineSidebarButtonEl) {
                    injectBacklineSidebarButton();
                }

                if (!_backlineHeaderEl) {
                    _backlineHeaderEl = jQuery('<a  class="backline-top-header" href="" onclick="return top.window.parent.left_nav.loadBacklineFrame(\'adm0\',\'RTop\',\'user\')" target="_blank"><div>You have <span class="badge"></span> unread backline messages <small>(click this bar to read them)</small></div></a>');
                    _backlineHeaderBadgeEl = _backlineHeaderEl.find('.badge');
                    jQuery('html').prepend(_backlineHeaderEl);
                }

                if (data.unread_count > 0) {
                    _backlineHeaderBadgeEl.html(data.unread_count);
                    _backlineHeaderEl.attr('href', data.recent_link.url);
                    _backlineHeaderEl.hide();

                    if (_backlineSidebarBadgeEl) {
                        _backlineSidebarButtonEl.addClass('has-messages');
                        _backlineSidebarBadgeEl.html(data.unread_count);
                    }
                } else {
                    if (_backlineSidebarButtonEl) {
                        _backlineSidebarButtonEl.removeClass('has-messages');
                    }
                    _backlineHeaderEl.hide();
                }
            }
        });
    }


    /**
     * Update the unread messages on the backline button that links to a single user chat with a patient
     * @param showLoading
     */
    function updateBacklinePatientButton(showLoading, _lastPatientPid, rootUrl){
        var sendMessageLink = _insertedTdSendMessageEl.find('#send_message');
        var sendPCCLink = _insertedTdPCCMessageEl.find('#send_pcc');

        if (showLoading === true) {
            sendMessageLink.attr('href', '');
            sendMessageLink.removeClass('has-messages');
            sendMessageLink.addClass('loading');

            sendPCCLink.removeClass('has-messages');
            sendPCCLink.addClass('loading');
        }
        console.log(rootUrl);
        console.log(_lastPatientPid);
        rootUrl = 'https://openemr.staging.drfirst.com';
        jQuery.get(rootUrl + '/backline/backline_injector.php?patient_id=' + _lastPatientPid, function (data) {
        //jQuery.get('https://openemr.staging.drfirst.com/backline/backline_injector.php?patient_id=1', function (data) {
            console.log(JSON.stringify(data));
            _backlinePatientData = data;
            sendMessageLink.removeClass('loading');

            sendPCCLink.removeClass('loading');

            var sendMessageBadge = _insertedTdSendMessageEl.find('.send-message-badge');
            var sendPCCBadge = _insertedTdPCCMessageEl.find('.send-message-badge');

            var errorCode = data.error_code;
            var errorMessage = data.error_message;

            if (!errorCode) {
                if (data.user_chat && data.user_chat.error_code) {
                    errorCode = data.user_chat.error_code;
                    errorMessage = 'User Chat Error (' + data.user_chat._sourceUrl + ') - ' + errorCode + ': ' + data.user_chat.error_text;
                }
            }


            if (errorCode) {
                setErroMessage(errorMessage);
                sendMessageLink.removeClass('has-messages');
                sendMessageBadge.hide();
                sendPCCBadge.hide();
            } else {
                setErroMessage(undefined);
                if (data.user_chat.unread_count > 0) {
                    sendMessageLink.addClass('has-messages');
                    sendMessageBadge.html(data.user_chat.unread_count);
                    sendMessageBadge.show();
                } else {
                    sendMessageLink.removeClass('has-messages');
                    sendMessageBadge.hide();
                }


                if (data.pcc_chat.unread_count > 0) {
                    sendPCCLink.addClass('has-messages');
                    sendPCCBadge.html(data.pcc_chat.unread_count);
                    sendPCCBadge.show();
                } else {
                    sendPCCLink.removeClass('has-messages');
                    sendPCCBadge.hide();
                }
            }
        }).fail(function(response) {
	    console.log(JSON.stringify(response));
	    console.log('Failed pulling back patient data');
	});
    }


    function injectBacklineSidebarButton() {
        var failed = false;
        if (window.left_nav && window.left_nav.document) {

            injectBacklineStyleSheet(window.left_nav.document);

            var liEl = jQuery(window.left_nav.document.getElementById('patimg')).parent();
            if (liEl.length > 0) {
                if (!_backlineSidebarButtonEl) {
                    _backlineSidebarButtonEl = jQuery(
                        '<li><a class="collapsed backline-sidebar-link" id="backlineLeftLink" onclick="return loadBacklineFrame(\'adm0\',\'RTop\',\'recent\')"><i class="fa fa-fw fa-send fa-2x"><span>&nbsp;Backline</span></span></a></li>'
                    );
                    _backlineSidebarBadgeEl = _backlineSidebarButtonEl.find('.badge');
                    liEl.append(_backlineSidebarButtonEl);
                }
            } else {
                failed = true;
            }

            //Inject the backline admin link
            var administrationLiEl = jQuery(window.left_nav.document.getElementById('admimg')).parent();
            var adminSubList = administrationLiEl.find('ul');

            if (adminSubList.length > 0) {
                _backlineAdminSidebarButtonEl = jQuery('<li><a href="" id="adm0" onclick="return loadBacklineAdminFrame(\'adm0\',\'RTop\',\'recent\')">Backline Admin</a></li>');
                adminSubList.append(_backlineAdminSidebarButtonEl);
            } else {
                failed = true;
            }

        } else {
            failed = true;
        }

        if (failed) {
            setTimeout(injectBacklineSidebarButton, 500);
        }

    }

    function injectBacklineSidebarAdminButton() {
        var failed = false;
        if (window.left_nav && window.left_nav.document) {

            injectBacklineStyleSheet(window.left_nav.document);

            //Inject the backline admin link
            var administrationLiEl = jQuery(window.left_nav.document.getElementById('admimg')).parent();
            var adminSubList = administrationLiEl.find('ul');

            if (adminSubList.length > 0) {
                _backlineAdminSidebarButtonEl = jQuery('<li><a href="" id="adm0" onclick="return loadBacklineAdminFrame(\'adm0\',\'RTop\',\'recent\')">Backline Admin</a></li>');
                adminSubList.append(_backlineAdminSidebarButtonEl);
            } else {
                failed = true;
            }

        } else {
            failed = true;
        }

        if (failed) {
            setTimeout(injectBacklineSidebarAdminButton, 500);
        }

    }

    var _insertedTdErrorMessageEl;
    function setErroMessage(message) {
        //Find the parent td element containing a link with an id of clear_active
        var activeButtonEl = window.Title.document.getElementById('clear_active');
        var tdEl = jQuery(activeButtonEl).parent('td');

        if (message) {
            if (!_insertedTdErrorMessageEl) {
                var divContent = '<td style="vertical-align:text-bottom;position:relative;"> ' +
                    '<a class="css_button_small backline-error-button" onclick="return alert(\'' + message + '\')" style="margin:0px;vertical-align:top;" id="alert_message"> ' +
                    '<span class="error-message-text">Backline Error</span>' +
                    '</a> ' +
                    '</td>';
                _insertedTdErrorMessageEl = jQuery(divContent);
                tdEl.after(_insertedTdErrorMessageEl);
            } else {
                if (!_backlineHeaderEl) {
                    _backlineHeaderEl = jQuery('<a  class="backline-top-header" href="" onclick="return top.window.parent.left_nav.loadBacklineFrame(\'adm0\',\'RTop\',\'user\')" target="_blank"><div>You have <span class="badge"></span> unread backline messages <small>(click this bar to read them)</small></div></a>');
                    _backlineHeaderBadgeEl = _backlineHeaderEl.find('.badge');
                    jQuery('html').prepend(_backlineHeaderEl);
                }
                _insertedTdErrorMessageEl = _backlineHeaderEl.find('.error-message-text');
                _insertedTdErrorMessageEl.html(message);
            }
            _insertedTdErrorMessageEl.show();
        } else {
            if (_insertedTdErrorMessageEl) {
                _insertedTdErrorMessageEl.hide();
            }
        }


    }


    /**
     * We want to keep the changes to the openEMR source files to a minimum so rather then modify the PHP where the buttons
     * are injected, we simply hijack the javascript functions and modify the DOM to inject our own buttons on the client
     * side when they are needed.
     * @returns {boolean}
     */
    function injectBacklinePatientButton() {
        if (window.left_nav && window.left_nav.clearactive && window.left_nav.setPatient) {
            injectBacklineStyleSheet(window.Title.document);

            //We are going to hijack the setPatient and clearactive function calls.  We save the old versions
            //so we can call them after we inject our data
            _blOldSetPatient = window.left_nav.setPatient;
            _blOldClearActive = window.left_nav.clearactive;


            /**
             * When set Patient is called, we should query a service to get the backline data for the new patient
             * and insert a button that will launch a backline window when clicked.  It should also show the
             * unread counts for the patient in the button.
             * @returns {*}
             */
            window.left_nav.setPatient = function (pname, pid, pubpid, frname, str_dob, rootUrl) {
                var retVal = _blOldSetPatient.apply(this, arguments);
                _lastPatientPid = pid;


                //Find the parent td element containing a link with an id of clear_active
                var activeButtonEl = window.Title.document.getElementById('clear_active');
                var tdEl = jQuery(activeButtonEl).parent('td');
                var foundSendMessageEl = window.Title.document.getElementById('ccd_send_message');
                if (!_insertedTdSendMessageEl) {
                    var divContent = '<td style="vertical-align:text-bottom;position:relative;"> ' +
                        '<a class="css_button_small backline-chat-link-button" onclick="return top.window.parent.left_nav.loadBacklineFrame(\'adm0\',\'RTop\',\'user\')" style="margin:0px;vertical-align:top;" id="send_message"> ' +
                        '<span class="badge send-message-badge"></span>' +
                        '<span class="send-message-text">Patient Chat</span>' +
                        '</a> ' +
                        '</td>';
                    _insertedTdSendMessageEl = jQuery(divContent);
                    tdEl.after(_insertedTdSendMessageEl);
                }
                _insertedTdSendMessageEl.show();



                var foundSendMessageEl = window.Title.document.getElementById('send_pcc');
                if (!_insertedTdPCCMessageEl) {
                    var divContent = '<td style="vertical-align:text-bottom;position:relative;"> ' +
                        '<a class="css_button_small backline-chat-link-button" onclick="return top.window.parent.left_nav.loadBacklineFrame(\'adm0\',\'RTop\',\'pcc\')" style="margin:0px;vertical-align:top;" id="send_pcc"> ' +
                        '<span class="badge send-message-badge"></span>' +
                        '<span class="send-message-text">PCC</span>' +
                        '</a> ' +
                        '</td>';
                    _insertedTdPCCMessageEl = jQuery(divContent);
                    tdEl.after(_insertedTdPCCMessageEl);
                }
                _insertedTdPCCMessageEl.show();



                clearInterval(_patientQueryIntervalHandle);
		var rootUrl = 'https://openemr.staging.drfirst.com';
                _patientQueryIntervalHandle = setInterval(updateBacklinePatientButton(true, _lastPatientPid, rootUrl), 5000);
                updateBacklinePatientButton(true, _lastPatientPid, rootUrl);

                return retVal;
            };


            /**
             * Remove the button we inserted when the active patient is cleared
             * @returns {*}
             */
            window.left_nav.clearactive = function () {
                _insertedTdSendMessageEl.hide();
                _insertedTdPCCMessageEl.hide();
                clearInterval(_patientQueryIntervalHandle);

                return _blOldClearActive.apply(this, arguments);
            };



            // Load the specified url into a frame to be determined, with the specified
            // frame as the default; the url must be relative to interface.
            window.top.loadBacklineFrame = window.left_nav.loadBacklineFrame = function (fname, frame, chatType) {
                var usage = fname.substring(3);

                var f = window.left_nav.document.forms[0];
                top.restoreSession();

                if(f.sel_frame)
                {
                    var fi = f.sel_frame.selectedIndex;
                    if (fi == 1) frame = 'RTop'; else if (fi == 2) frame = 'RBot';
                }
                if (!f.cb_bot.checked) frame = 'RTop'; else if (!f.cb_top.checked) frame = 'RBot';


                var location;
                if (chatType === 'recent') {
/*		           
jQuery.get(_backlineData.recent_link.url, function(data){
                               if (data.url[4] === ':') {
                                   var domain = data.url.substring(4);
                                   data.url = 'https' + domain;
                               }
                               location = data.url;
                               finished = true;
                           });*/
                    location = 'https://webplus.demo.akariobl.com?auth_token=sRaX_HYyN_tfQQHee4ew#/recent';

                } else if (chatType==='pcc') {

		    if (_backlinePatientData.pcc_chat.url[4] === ':') {
			var domain = _backlinePatientData.pcc_chat.url.substring(4);
			_backlinePatientData.pcc_chat.url = 'https' + domain;
		    }
                    location = _backlinePatientData.pcc_chat.url;
                } else if (chatType==='user') {
		    if (_backlinePatientData.user_chat.url[4] === ':') {
			var domain = _backlinePatientData.user_chat.url.substring(4);
			_backlinePatientData.user_chat.url = 'https' + domain;
		    }
                    location = _backlinePatientData.user_chat.url;
                }
                top.frames[frame].location = location;
                console.log(location);
                // alert(location);

                return false;
            };

            window.top.loadBacklineAdminFrame = window.left_nav.loadBacklineAdminFrame = function (fname, frame, chatType) {

                var usage = fname.substring(3);

                var f = window.left_nav.document.forms[0];
                top.restoreSession();

                if(f.sel_frame)
                {
                    var fi = f.sel_frame.selectedIndex;
                    if (fi == 1) frame = 'RTop'; else if (fi == 2) frame = 'RBot';
                }
                if (!f.cb_bot.checked) frame = 'RTop'; else if (!f.cb_top.checked) frame = 'RBot';


                var location = '../../backline/backline_orgs.php';
                top.frames[frame].location = location;

                return false;
            };


            return true;
        } else {
            setTimeout(injectBacklinePatientButton, 1000);
        }
    }
    injectBacklineStyleSheet(document);
    updateBacklineHeader();
    injectBacklinePatientButton();


    //Update the header count periodically
    setInterval(updateBacklineHeader, 5000);

})();
