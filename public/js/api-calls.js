/**
 *
 */

/* Funções CORE ========================================================================================================================================================================================================================================================================================================================================== */

function WSRestPost(sURL, oObj, returnResultFunction, iTimeOut, bDebug) {
    if (!iTimeOut) {
        iTimeOut = 8000;
    }
    if (bDebug) {
        sDataType = 'html';
    }
    else {
        sDataType = 'json';
    }
    $.ajax({
        type: 'POST',
        url: sURL,
        dataType: sDataType,
        processData: false,
        contentType: false,
        data: oObj,
        cache: false,
        timeout: iTimeOut,
        success: function(json) {
            if (!bDebug) {
                switch (json.status) {
                    case 200:   // Sucesso
                        json = json;
                        break;
                    case 403:   var json = {
                        status: '403',
                        message: json.message,
                    };
                        break;
                    case 406:   var json = {
                        status: '406',
                        message: json.message,
                    };
                        break;
                    case 409:   var json = {
                        status: '409',
                        message: json.message,
                        errors: json.errors,
                    };
                        break;
                    default:   var json = {
                        status: '500',
                        message: 'Internal server error: '+json.message,
                    };
                }
                returnResultFunction(json);
            }
            else {
                alert(json);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            switch (textStatus) {
                case 'timeout'	:	var json = {
                    status: '408',
                    message: 'Request timeout',
                };
                    returnResultFunction(json);
                default         : 	var json = {
                    status: '500',
                    message: 'Internal server error: '+errorThrown,
                };
                    returnResultFunction(json);
            }
        }
    });
}




function WSRestGet(sURL, returnResultFunction, iTimeOut) {
    if (!iTimeOut) {
        iTimeOut = 8000;
    }
    $.ajax({
        type: 'GET',
        url: sURL,
        //contentType: 'application/json',
        dataType: 'json',
        //dataType: 'json',
        //        		  processData: false,
        //        		  contentType: false,
        cache: false,
        timeout: iTimeOut,
        success: function(json) {
            if (json.status=='200') {
                returnResultFunction(json);
            }
            else {
                var json = {
                    status: '500',
                    message: 'Internal server error: '+json.message,
                };
                returnResultFunction(json);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            switch (textStatus) {
                case 'timeout'	:	var json = {
                    status: '408',
                    message: 'Request timeout',
                };
                    returnResultFunction(json);
                default         : 	var json = {
                    status: '501',
                    message: 'Internal server error: '+errorThrown+' - '+textStatus,
                };
                    returnResultFunction(json);
            }
        }
    });
}
