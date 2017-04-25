var chart;

$(document).ready(function(){
    //if browser does not support the native html5 datepicker
    if(!Modernizr.inputtypes.date){
        $('input[type=date]').datepicker({
            changeMonth: true,
            maxDate: 0,
            showOtherMonths: true,
            dateFormat: "yy-mm-dd"
        });
        $('input[type=month]').datepicker({
            changeMonth: true,
            maxDate: 0,
            showOtherMonths: true,
            dateFormat: "yy-mm"
        });
    }
    $("#no-data-error").hide();
    $("#mostRecent").click(function(){
        $("#startDate").val("");
        $("#endDate").val("");
        $("#submit").click();
    });
    $(".lastxdays").click(function(){
        var curDate = new Date();
        var lastDate = new Date(new Date().setDate(new Date().getDate() - $(this).attr("days")));
        $("#startDate").val(lastDate.toYMD());
        $("#endDate").val(curDate.toYMD());
        $("#submit").click();
    });
    $("#hideData").click(function(){
        chart.hide();
    });
    $("#showData").click(function(){
        chart.show();
    });
});

//Generates an array of dates from the start Date to end Date
function getDatesArray(){
    var startDate = new Date($('#startDate').val());
    var endDate = new Date($('#endDate').val());
    var array = [];
    while (startDate <= endDate){
        array.push(startDate.toYMD());
        startDate = new Date(startDate.setDate(startDate.getDate()+1));
    }
    return array;
}
function getHoursArray(){
    var startDate = new Date($('#startHour').val());
    var endDate = new Date($('#endHour').val());                //i.e. This makes 12/25/2017 0:00 into 12/25/2017 23:00
    endDate = new Date(endDate.setDate(endDate.getDate()+1));
    endDate = new Date(endDate.setHours(endDate.getHours()-1));

    var array = [];
    while (startDate <= endDate){
        array.push(startDate.toYMDH());
        startDate = new Date(startDate.setHours(startDate.getHours()+1));
    }
    return array;
}
function getMonthsArray(){
    var startDate = new Date($('#startDate').val());
    var endDate = new Date($('#endDate').val());
    var array = [];
    while (startDate <= endDate){
        array.push(startDate.toYM());
        startDate = new Date(startDate.setMonth(startDate.getMonth()+1));
    }
    return array;
}
//formats js date object to YYYY-MM-DD
Date.prototype.toYMD = function(date){
    var mm = this.getUTCMonth() + 1;
    var dd = this.getUTCDate();
    return [this.getFullYear(), 
            (mm>9 ? '' : '0') + mm,
            (dd>9 ? '' : '0') + dd
        ].join('-');
};
//formats js date object to YYYYMM
Date.prototype.toYM = function(date){
    var mm = this.getUTCMonth() + 1;
    return [this.getFullYear(), 
            (mm>9 ? '' : '0') + mm
        ].join('');
};
//formats js date object to YYYY-MM-DD HH:00
Date.prototype.toYMDH = function(date){
    var mm = this.getUTCMonth() + 1;
    var dd = this.getUTCDate();
    var hh = this.getUTCHours();
    return [this.getFullYear(), 
            (mm>9 ? '' : '0') + mm,
            (dd>9 ? '' : '0') + dd
        ].join('-') + ' ' + (hh>9 ? '' : '0') + hh + ":00";
};
//Adds missing dates to recieved data
function addMissingDates(data, defaultObject){
    var dates = getDatesArray();
    data.forEach(function(arrayItem){
        var index = dates.indexOf(arrayItem['Day']);
        if (index > -1){
            dates.splice(index, 1);
        }
    });
    dates.forEach(function(arrayItem, i){
        var missingObject = {Day: arrayItem};
        data.push($.extend(missingObject, defaultObject));
    });
    return data;
}
//Adds missing dates to recieved data
function addMissingMonths(data, defaultObject){
    var dates = getMonthsArray();
    data.forEach(function(arrayItem){
        var index = dates.indexOf(arrayItem['Months']);
        if (index > -1){
            dates.splice(index, 1);
        }
    });
    dates.forEach(function(arrayItem, i){
        var missingObject = {Month: arrayItem};
        data.push($.extend(missingObject, defaultObject));
    });
    return data;
}
//Adds missing dates to recieved data
function addMissingHours(data, defaultObject){
    var dates = getHoursArray();
    data.forEach(function(arrayItem){
        var index = dates.indexOf(arrayItem['Hour']);
        if (index > -1){
            dates.splice(index, 1);
        }
    });
    dates.forEach(function(arrayItem, i){
        var missingObject = {Hour: arrayItem};
        data.push($.extend(missingObject, defaultObject));
    });
    return data;
}
//Main daily report function
function runReport(data){
    if (data.length > 0){
        var keys = Object.keys(data[0]);
        var defaultObject = {};
        keys.forEach(function(arrayItem){
            if (arrayItem != "Day"){
                defaultObject[arrayItem] = 0;   //We don't want the day property to be 0
            }
        })

        data = addMissingDates(data, defaultObject);

        chart = c3.generate({
            bindto: "#chart",
            xFormat: "%Y-%m-%d",
            data: {
                json: data,
                keys: {
                    x: "Day",
                    value: keys,
                }
            },
            axis: {
                x:{
                    type: "timeseries",
                    tick: {
                        format: "%m/%d"
                    }
                }
            }
        });
        chart.hide()
        chart.show('Total Interactions');
    }
    else{
        $('#no-data-error').show();
    }
};
//Main monthly report function
function runMonthlyReport(data){
    if (data.length > 0){
        var keys = Object.keys(data[0]);
        var defaultObject = {};
        keys.forEach(function(arrayItem){
            if (arrayItem != "Month"){
                defaultObject[arrayItem] = 0;   //We don't want the day property to be 0
            }
        })

        data = addMissingMonths(data, defaultObject);

        chart = c3.generate({
            bindto: "#chart",
            data: {
                xFormat: '%Y%m',
                json: data,
                keys: {
                    x: "Month",
                    value: keys,
                }
            },
            axis: {
                x:{
                    type: "timeseries",
                    tick: {
                        format: "%y/%m"
                    }
                }
            }
        });
        chart.hide()
        chart.show('Total Interactions');
    }
    else{
        $('#no-data-error').show();
    }
};
//Main Hourly report function
function runHourlyReport(data){
    if (data.length > 0){
        var keys = Object.keys(data[0]);
        var defaultObject = {};
        keys.forEach(function(arrayItem){
            if (arrayItem != "Hour"){
                defaultObject[arrayItem] = 0;   //We don't want the day property to be 0
            }
        })

        data = addMissingHours(data, defaultObject);

        chart = c3.generate({
            bindto: "#chart",
            data: {
                xFormat: '%Y-%m-%d %H:00',
                json: data,
                keys: {
                    x: "Hour",
                    value: keys,
                }
            },
            axis: {
                x:{
                    type: "timeseries",
                    tick: {
                        format: "%m/%d %H:00"
                    }
                }
            }
        });
        chart.hide()
        chart.show('Total Interactions');
    }
    else{
        $('#no-data-error').show();
    }
};
//Recent Activity Report Functino
function runRecentActivityReport(data){
    //Render columns as text to prevent code from being run in the datatables
    $.extend( true, $.fn.dataTable.defaults, {
        column: {
            render: $.fn.dataTable.render.text()
        }
    });
    $('#recent-activity').DataTable({
        fixedHeader: true,
        data: data,
        columns: [
            {"data": "datetime", "title": "Date"},
            {"data": "event", "title": "Event"},
            {"data": "handle", "title": "User"},
            {"data": "ipaddress", "title": "IP Address"},
            {"data": "params", "title": "More Details"}
        ],
        paginate: false,
        order: [[0, "desc"]]
    });
}