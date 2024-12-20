var originalBxOnCustoEvent = BX.onCustomEvent;

BX.onCustomEvent = function (eventObject, eventName, eventParams, securePrams) {
  var logData = {
    eventObject,
    eventName,
    eventParams,
    securePrams,
    eventParamsClassNames: [],
  };

  for (var i in eventParams) {
    var param = eventParams[i];
    if (param !== null && typeof param == "object" && param.constructor) {
      logData.eventParamsClassNames.push(param.constructor.name);
    } else {
      logData.eventParamsClassNames.push(null);
    }
  }

  console.log(logData);
  originalBxOnCustoEvent.apply(null, [
    eventObject,
    eventName,
    eventParams,
    securePrams,
  ]);
};
