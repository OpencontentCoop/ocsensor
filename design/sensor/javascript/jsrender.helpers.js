;let SensorTranslate = function () {
    let translate = function (key, context, replacements){
        if (context && $.inArray(context, ['privacy', 'status', 'type', 'moderation']) > -1){
            context = 'sensor/'+context;
        }else{
            context = 'sensor';
        }
        function getNested(obj, ...args) {
            return args.reduce((obj, level) => obj && obj[level], obj)
        }
        if (getNested(SensorI18n, 'custom', key)){
            key = SensorI18n['custom'][key];
        }else if (getNested(SensorI18n, context, key)){
            key = SensorI18n[context][key];
        }else if ($('#debug').length > 0){
            console.log('Missing translations for ' + key);
        }
        return key;
    };
    return {
        translate: function (key, context, replacements) {
            if ($.isArray(key)){
                return key.map(function (keyItem, context, replacements){
                    return translate(keyItem, context, replacements);
                })
            }
            return translate(key, context, replacements);
        }
    }
};
let SensorTranslateSingleton = (function () {
    var instance;
    function createInstance() {
        return SensorTranslate();
    }
    return {
        getInstance: function () {
            if (!instance) {
                instance = createInstance();
            }
            return instance;
        }
    };
})();
$.sensorTranslate = SensorTranslateSingleton.getInstance();
$.views.helpers($.extend({}, $.opendataTools.helpers, {
    'eventName': function (value) {
        var text = $('select#triggers option[value="' + value + '"]').text();
        return text ? text : value;
    },
    'fromNow': function (value) {
        return moment(new Date(value)).fromNow();
    },
    'last_response': function () {
        return (window.sessionStorage !== undefined) ? sessionStorage.getItem('todo-response') : null;
    },
    'progressiveDate': function (value) {
        var date = moment(new Date(value));
        var today = moment();
        var yesterday = moment().subtract(1, 'day');
        if (date.isSame(today, "day")) {
            return date.format('HH:mm')
        }
        if (date.isSame(yesterday, "day")) {
            return 'Ieri, ' + date.format('HH:mm')
        }
        if (date.isSame(new Date(), "month")) {
            return date.format('D MMM')
        }
        return date.format('DD/MM/YY')
    },
    'sensorTranslate': function (key, context, replacements) {
        return $.sensorTranslate.translate(key, context, replacements);
    },
    'inArray': function (needle, haystack) {
        return $.inArray(needle, haystack) > -1;
    },
    'accessPath': function (path) {
        return $.opendataTools.settings('accessPath') + path;
    }
}));
