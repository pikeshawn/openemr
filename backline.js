alert('backline loaded!');

'use strict';
(function() {
  var _blOldClearActive;
  var _blOldSetPatient;

  function blClearActive() {
    alert('clear patient');
  }

  function blSetPatient(pname, pid, pubpid, frname, str_dob) {
    alert('set patient');
  }


  function injectBacklineIntegration() {
    if (window.clearactive && window.setpatient) {
      _blOldSetPatient = window.setpatient;
      _blOldClearActive = window.clearactive;

      return true;
    } else {
      setTimeout(injectBacklineIntegration, 1000);
    }
  }
  // debugger;
  injectBacklineIntegration();
})();