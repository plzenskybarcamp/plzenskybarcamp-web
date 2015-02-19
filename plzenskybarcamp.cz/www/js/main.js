registerForm = function(container, nextContainer) {
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

registerAjaxRegistration = function(container) {
    $(container).on('submit', 'form', function(e) {
        form = this;
        e.preventDefault();
        action = this.action;
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

ajaxPost = function(form) {
    return $.ajax({
        type: 'POST',
        url: form.get(0).action,
        data: form.serialize()
    }).promise();
}

validateForms = function(forms) {
    for (index in forms) {
        if (!Nette.validateForm(forms[index].get(0))) {
            return $.Deferred().reject().promise();
        }
    }
    return forms;
}

commitForms = function(forms) {
    commitForm = function(form) {
        return ajaxPost(form).then(function(res) {
            if (!res.updated) {
                return $.Deferred().reject('Během uloženi došlo k chybě').promise();
            }
            return 'Změny byly úspěšně uloženy';
        });
    }
    return $.when.apply($, forms.map(commitForm));
}

showMessage = function(container, elmClass) {
    return function(message) {
        if (message) {
            elm = $('<p class="js-message text-center ' + elmClass + '">' + message + '</p>');
            container.html('').append(elm);
            $.scrollTo(container, 500);
            setTimeout(function() {
                elm.hide();
            }, 5000);
        }
    }
}

registerAjaxProfileUpdate = function(talkOnButton, actionButton, userContainer, talkContainer, talkButtonContainer) {
    talkOnButton.on('click', function() {
        talkContainer.show();
        talkButtonContainer.hide();
    })
    actionButton.on('click', function(e) {
        actionButton.get(0).disabled = true;
        forms = [$('form', userContainer)]
        if (talkContainer.is(":visible")) {
            forms.push($('form', talkContainer));
        }
        unLockSave = function(msg) {
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

processVote = function(container) {
    actionAdd = container.data( 'actionAdd' );
    actionRemove = container.data( 'actionRemove' );
    return function(doAdded, talkId) {
        action = doAdded ? actionAdd : actionRemove;
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

registerVotes = function(container) {
    checkins = {}
    processSubmit = processVote(container);
    $('tr.talks-detail', container).each(function(index, elem){
        elem = $(elem);
        talkId = elem.data('id');
        checkins[talkId] = $('.vote', elem).data('checked');
        boxs = [$('.vote', elem),  $('.vote', elem.prev())];
        $.each(boxs, function(index, box){
            box.prop('checked', checkins[talkId]);
            $(box).click((function(talkId, boxs, voteCount, trHeadElement){
                return function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    processSubmit(!checkins[talkId], talkId).done(function(count){
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


registerVotesDetail = function(container) {
    if (!container.get(0)) {
        return;
    }
    checkins = {}
    processSubmit = processVote(container);

    box = $(".vote-detail", container);
    voteCount = $("#votes-count", container);
    talkId = container.data("id");
    isChecked = box.data('checked');
    box.prop('checked', isChecked);
    voted = $(".voted", container);
    box.click(function(e) {
        processSubmit(!isChecked, talkId).done(function(count){
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
});
$(document).ready(function() {
    $('.tooltip').tooltipster();
    $('a').smoothScroll();
    registerForm('#speaker-regestration', '#user-regestration');
    registerForm('#user-regestration', '#speaker-regestration');
    registerAjaxRegistration($('#registration'));
    registerAjaxProfileUpdate($('#talk-registration'), $('#profile-save'), $('#user-form'), $('#talk-form'), $('#talk-button'));
    registerVotes($('#talks-list'));
    registerVotesDetail($("#speaker-detail .voting-detail"));
});
// window.fbAsyncInit = function() {
// 	FB.init({
// 		appId: '504432792996629',
// 		status: true,
// 		cookie: true,
// 		xfbml: true
// 	});

// 	FB.Event.subscribe('auth.authResponseChange', function(response) {
// 		if (response.status === 'connected') {
// 			console.log('Logged');
// 		} else if (response.status === 'not_authorized') {
// 			console.log('Not authorized');
// 		} else {
// 			console.log('Never logged');
// 		}
// 	});
// }

// function login() {
// 	FB.login(function(response) {

// 	}, {scope: 'email'});
// }


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
    var $listTableTr = $('.table-list tr');
    $listTableTr.on('click touchstart', function() {
        var _$this = $(this);
        if (_$this.hasClass('active')) {
            _$this.removeClass('active').find('.crop').removeClass('more');
        } else {
            $listTableTr.removeClass('active').find('.crop').removeClass('more');
            _$this.addClass('active').find('.crop').addClass('more');
        }
    });

    var $listTableTalks = $('.table-list#talks-list tr');
    $listTableTalks.on('click touchstart', function( event ) {
        var
                _$this = $(this),
                _id = _$this.attr('data-id'),
                _$detailTrHead = $listTableTalks.parent().find('.talks-head'),
                _$detailTr = $listTableTalks.parent().find('.talks-detail'),
                _$detailTrHeadCurrent = $listTableTalks.parent().find('.talks-head[data-id="' + _id + '"]'),
                _$detailTrCurrent = $listTableTalks.parent().find('.talks-detail[data-id="' + _id + '"]');

        //Disable expand/collapse when click to link
        if( $( event.target ).is('a, a img')) {
            return;
        }

        _$detailTr.hide();
        _$detailTrHead.show();

        if (_$this.hasClass('active-detail-head')) {
            _$detailTrHeadCurrent.show();
            _$detailTrCurrent.hide();
        } else {
            _$detailTrHeadCurrent.hide();
            _$detailTrCurrent.addClass('active-detail').show();
        }

        if (_$this.hasClass('active-detail')) {
            _$detailTrCurrent.hide();
            _$detailTrHeadCurrent.show();
        }
    });
});