var submitsCounter = 0;

function opayHandleSubmit() {
  submitsCounter++;
  
  if (submitsCounter == 1) {
      if (jQuery("#opay-payment-form input[name=opay_channel]:checked").length == 0) {
          alert(opayCheckoutData.pleaseSelectChannelMsg);
          submitsCounter = 0;
          return false;
      }
      else {
          return true;
      }
  } else {
      return false;
  }
}
