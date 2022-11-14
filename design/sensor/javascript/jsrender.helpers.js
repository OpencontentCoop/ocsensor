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
$.postStatusStyle = function (post) {
    var statusCss = 'info';
    if (post.status.identifier === 'pending') {
        statusCss = 'default';
    } else if (post.status.identifier === 'open') {
        statusCss = 'warning';
    } else if (post.status.identifier === 'close') {
        statusCss = 'danger';
    } else if (post.status.identifier === 'approved') {
        statusCss = 'success';
    }
    return statusCss;
};
$.views.helpers($.extend({}, $.opendataTools.helpers, {
    'eventName': function (value) {
        var values = value.split('|');
        var texts = [];
        $.each(values, function (){
            var text = $('select#triggers option[value="' + this + '"]').text();
            texts.push(text ? text : this);
        });

        return texts;
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
    },
    'boldify': function(text){
        var identifier = '__';
        var htmltag = 'b';
        var array = text.split(identifier);
        var previous = "";
        var previous_i;
        for (i = 0; i < array.length; i++) {
            if (i % 2) {
                //odd number
            } else if (i !== 0) {
                previous_i = eval(i - 1);
                array[previous_i] = "<" + htmltag + ">" + previous + "</" + htmltag + ">";
            }
            previous = array[i];
        }
        var newtext = "";
        for (i = 0; i < array.length; i++) {
            newtext += array[i];
        }
        return newtext;
    },
    'statusStyle': function (post) {
        return $.postStatusStyle(post);
    }
}));
