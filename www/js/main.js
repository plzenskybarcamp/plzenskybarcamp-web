//@koala-prepend "libs/jquery-2.1.3.js"
//@koala-prepend "libs/jquery.scrollTo-1.4.14.js"
//@koala-prepend "libs/jquery.smooth-scroll-1.5.5.js"
//@koala-prepend "libs/jquery.tooltipster-3.3.0.js"
//@koala-prepend "libs/lightbox-2.7.1.js"
//@koala-prepend "libs/netteForms.js"

(function() {
var registerForm = function(container, nextContainer) {
    $('#registration').on('click', container + ' .registration-button', function(e) {
        e.preventDefault();
        $(this).hide();
        var form = $('.form', container);
        var nextForm = $('.form', nextContainer);
        if (form.is(':visible')) {
            form.hide();
            $.scrollTo(container);
        } else {
            nextForm.hide();
            form.show();
            $.scrollTo(container, 500);
        }
    })
}

var registerAjaxRegistration = function(container) {
    $(container).on('submit', 'form', function(e) {
        var form = this;
        e.preventDefault();
        var action = this.action;
        $('body').css('cursor', 'wait');
        $.ajax({
            type: 'POST',
            url: action,
            data: $(form).serialize()
        }).done(function(data) {
            if (data.redirect) {
                $.ajax({
                    type: "GET",
                    url: data.redirect
                }).done(function(data) {
                    $('body').css('cursor', 'auto');
                    container.html(data.html);
                    $.scrollTo(container);
                });
            }
        })
    })
}

var ajaxPost = function(form) {
    return $.ajax({
        type: 'POST',
        url: form.get(0).action,
        data: form.serialize()
    }).promise();
}

var validateForms = function(forms) {
    for (index in forms) {
        if (!Nette.validateForm(forms[index].get(0))) {
            return $.Deferred().reject().promise();
        }
    }
    return forms;
}

var commitForms = function(forms) {
    var commitForm = function(form) {
        return ajaxPost(form).then(function(res) {
            if (!res.updated) {
                trackEvent( "process-ended", "profile-update-failed" );
                return $.Deferred().reject('Během uloženi došlo k chybě').promise();
            }
            trackEvent( "process-ended", "profile-update-success" );
            return 'Změny byly úspěšně uloženy';
        });
    }
    return $.when.apply($, forms.map(commitForm));
}

var showMessage = function(container, elmClass) {
    return function(message) {
        if (message) {
            var elm = $('<p class="js-message text-center ' + elmClass + '">' + message + '</p>');
            container.html('').append(elm);
            $.scrollTo(container, 500);
            setTimeout(function() {
                elm.hide();
            }, 5000);
        }
    }
}

var registerAjaxProfileUpdate = function(talkOnButton, actionButton, userContainer, talkContainer, talkButtonContainer) {
    talkOnButton.on('click', function() {
        talkContainer.show();
        talkButtonContainer.hide();
    })
    actionButton.on('click', function(e) {
        actionButton.get(0).disabled = true;
        var forms = [$('form', userContainer)]
        if (talkContainer.is(":visible")) {
            forms.push($('form', talkContainer));
        }
        var unLockSave = function(msg) {
            actionButton.get(0).disabled = false;
            return msg;
        }
        messageContainer = $('#messages', userContainer);
        $.when(validateForms(forms)).then(commitForms)
                .then(unLockSave, unLockSave)
                .done(showMessage(messageContainer, 'success'))
                .fail(showMessage(messageContainer, 'error'));
    });
}

var processVote = function(container) {
    var actionAdd = container.data( 'actionAdd' );
    var actionRemove = container.data( 'actionRemove' );
    return function(doAdded, talkId) {
        var action = doAdded ? actionAdd : actionRemove;
        return $.ajax({
            type: 'GET',
            url: action,
            data: 'talkId=' + talkId
        }).promise().then(function(res){
            if ( res.votes_count >= 0 ) {
                return res.votes_count;
            }
            return $.Deferred().reject().promise();
        })
    }
}

var registerVotes = function(container) {
    var checkins = {};
    var processSubmit = processVote(container);
    $('tr.talks-detail', container).each(function(index, elem){
        var elem = $(elem);
        var talkId = elem.data('id');
        checkins[talkId] = $('.vote', elem).data('checked');
        var boxs = [$('.vote', elem),  $('.vote', elem.prev())];
        var elems = elem.add(elem.prev());
        var statuses = $('.status-box', elems);
        $.each(boxs, function(index, box){
            box.prop('checked', checkins[talkId]);
            $(box).click((function(talkId, boxs, voteCount, trHeadElement){
                return function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    statuses.text('Ukládám…').addClass('show');
                    processSubmit(!checkins[talkId], talkId).done(function(count){
                        trackEvent( "talk-vote", "vote-list", talkId );
                        statuses.text('Uloženo')
                        setTimeout(function(){statuses.removeClass('show');}, 1000);
                        voteCount.html(count);
                        checkins[talkId] = !checkins[talkId]
                        $.each(boxs, function(index, box){
                            box.prop('checked', checkins[talkId]);
                        });
                        if (checkins[talkId]) {
                            trHeadElement.addClass('voted-for')
                        } else {
                            trHeadElement.removeClass('voted-for')
                        }
                    });
                }
            })(talkId, boxs, $('.votes_count', elem), elem.prev()));
        });
    });
}


var registerVotesDetail = function(container) {
    if (!container.get(0)) {
        return;
    }
    var checkins = {};
    var processSubmit = processVote(container);

    var box = $(".vote-detail", container);
    var voteCount = $("#votes-count", container);
    var talkId = container.data("id");
    var isChecked = box.data('checked');
    box.prop('checked', isChecked);
    var voted = $(".voted", container);
    box.click(function(e) {
        processSubmit(!isChecked, talkId).done(function(count){
            trackEvent( "talk-vote", "vote-detail", talkId );
            voteCount.html(count);
            isChecked = !isChecked
            box.prop('checked', isChecked);
            if (isChecked) {
                voted.show()
            } else {
                voted.hide()
            }
        });
    });
}

$(document).ready(function() {
    setTimeout(function() {$('.flash.success').fadeOut(2000);}, 6000);

    $('.tooltip').tooltipster();
    $('a').smoothScroll();
    registerForm('#speaker-regestration', '#user-regestration');
    registerForm('#user-regestration', '#speaker-regestration');
    registerAjaxRegistration($('#registration'));
    registerAjaxProfileUpdate($('#talk-registration'), $('#profile-save'), $('#user-form'), $('#talk-form'), $('#talk-button'));
    registerVotes($('#talks-list'));
    registerVotesDetail($("#speaker-detail .voting-detail"));
});

$(document).ready(function() {
    var $show_login_panel_hp = $('.show-login-panel-hp');
    $show_login_panel_hp.click(function( e ){
        $show_login_panel_hp.hide();
        var $login_choose_network = $('#login-choose-network').show();
        $.scrollTo($login_choose_network, 500);
        e.preventDefault();
    });
});


$(document).ready(function() {
    var $listTableTr = $('.table-list#users-list tr');
    $listTableTr.on('click touchstart', function() {
        var _$this = $(this);
        if (_$this.hasClass('active')) {
            _$this.removeClass('active').find('.crop').removeClass('more');
        } else {
            $listTableTr.removeClass('active').find('.crop').removeClass('more');
            _$this.addClass('active').find('.crop').addClass('more');
        }
    });

    var $listTableTalks = $('.table-list#talks-list tr').not('.table-list-sub tr');
    $listTableTalks.on('click', function( e ) {
        var
                _$this = $(this),
                _id = _$this.attr('data-id'),
                _$detailTrHead = $listTableTalks.parent().find('.talks-head'),
                _$detailTr = $listTableTalks.parent().find('.talks-detail'),
                _$detailTrHeadCurrent = $listTableTalks.parent().find('.talks-head[data-id="' + _id + '"]'),
                _$detailTrCurrent = $listTableTalks.parent().find('.talks-detail[data-id="' + _id + '"]');

        //Disable expand/collapse when click to link
        if( $( e.target ).is('a, a img')) {
            history.replaceState({}, document.title, "#talk_" + _id);
            return;
        }

        _$detailTr.hide();
        _$detailTrHead.show();

        if (_$this.hasClass('active-detail')) {
            _$detailTrHeadCurrent.show();
            _$detailTrCurrent.hide();
            history.replaceState({}, document.title, location.href.replace(/#.*/, ''));
        } else {
            _$detailTrHeadCurrent.hide();
            _$detailTrCurrent.addClass('active-detail').show();
            history.replaceState({}, document.title, "#talk_" + _id);
        }
    });
    if( location.hash.match(/^#talk_[0-9a-f]+$/) && $(location.hash).length == 1 ) {
        $(location.hash).click();
    }
});
})();

function logError(details) {
  $.ajax({
    type: 'POST',
    url: 'https://' + location.host + '/api/log/js-error',
    data: {
        context: navigator.userAgent,
        details: details,
        referer: location.href,
    },
//    contentType: 'application/json; charset=utf-8'
  });
};

function trackEvent( eventName, action, label ) {
    dataLayer.push({
        'event': eventName,
        'action': action,
        'label': label
    });
}
