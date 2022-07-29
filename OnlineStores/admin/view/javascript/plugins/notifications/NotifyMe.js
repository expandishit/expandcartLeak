
var NotifyMe = function (data) {
    this._id = null;

    this.counter = 0;

    this.data = data;
};

NotifyMe.wordWrapper = function (word, max) {

    max = max || 10;

    var tmp = word.substring(0, max);

    if (word.length > tmp.length) {
        tmp += ' ...';
    }

    return tmp;
};

NotifyMe.prototype.setInterval = function (interVal) {
    this.data.interVal = interVal;

    return this;
};

NotifyMe.prototype.getInterval = function () {
    return this.data.interVal || 3000;
};

NotifyMe.prototype.setUrl = function (url) {
    this.data.url = url;

    return this;
};

NotifyMe.prototype.getUrl = function () {
    return this.data.url;
};


NotifyMe.prototype.defaultCallback = function (response) {

    var notificationsHandler = $('#notifications .media-list.dropdown-content-body');
    var countHandler = $('#notifications .notificationsCount');

    if (typeof response['notifications'] == 'undefined') {
        console.log('no not');
        notificationsHandler.html(response['alert']);

        return;
    }

    var template = function (notification) {
        return '<li class="media">' +
            '<div class="media-body">' +
            '<a href="user/notificatioins/browse" class="media-heading">' +
            '<span class="text-semibold">'+notification['submitter_info']['name']+'</span>' +
            '<span class="media-annotation pull-right">'+notification['created_at']+'</span>' +
            '</a>' +
            '<span class="text-muted">' + NotifyMe.wordWrapper(notification['notification']) + '</span>' +
            '</div>' +
            '</li>';
    }

    notificationsHandler.html('');

    countHandler.html(response['unread']);

    for (notification in response['notifications']) {
        notificationsHandler.append(
            template(response['notifications'][notification])
        );
    }
};

NotifyMe.prototype.request = function (data) {
    return $.ajax({
        url: data.url,
        data: {},
        dataType: 'JSON',
        method: 'POST',
        success: data.cb || this.defaultCallback
    });
};

NotifyMe.prototype.resetInterval = function (interval) {
    clearInterval(Notify._id);
    $n.setInterval(interval);
    $n.Notify();
};

NotifyMe.prototype.clearInterval = function (intervalId) {
    clearInterval(intervalId || Notify._id);
};

NotifyMe.prototype.Notify = function (data) {

    data = data || {};

    this.data = Object.assign(this.data, data);

    this._id = setInterval(function () {
        $n.request($n.data);

        Notify.counter++;

        if (Notify.counter === 6) {
            $n.resetInterval(30000);
        }

        if (Notify.counter === 10) {
            $n.resetInterval(60000);
        }

        if (Notify.counter === 30) {
            $n.resetInterval(90000);
        }

        if (Notify.counter >= 50) {
            $n.clearInterval(Notify._id);
        }
    }, this.getInterval());
};

var $n = Notify = new NotifyMe({
    interVal: 15000,
});

/**
 * How to use ?
 *
 * <script type="text/javascript" src="view/javascript/plugins/notifications/NotifyMe.js"></script>
 *
$n.Notify({
    url: 'user/notifications/pull'
});
 *
 * */