/*jshint browser: true */
/*global mediaWiki, jQuery */
( function (mw, $) {
    'use strict';

    /* */
    if($.cookie("ui-feedback-type")===null)
        $.cookie("ui-feedback-type", (Math.random() >= 0.5)?'screenshot':'questionnaire', { path: '/', expires: 21 });
    var use_html2canvas = $.cookie("ui-feedback-type") === 'screenshot';

    console.log($.cookie("ui-feedback-type"));

    /*TODO JUST FOR TESTING*/
    if(window.location.hash=='#screenshot'){
        use_html2canvas = true;
    }else if(window.location.hash=='#questionnaire'){
        use_html2canvas = false;
    }

    /* the feedback button */
    var button = document.createElement('div');
    button.className = 'ui-feedback-button';
    button.innerHTML = '&nbsp;';

    /*   ________                          __  .__                            .__
         \_____  \  __ __   ____   _______/  |_|__| ____   ____   ____ _____  |__|______   ____
         /  / \  \|  |  \_/ __ \ /  ___/\   __\  |/  _ \ /    \ /    \\__  \ |  \_  __ \_/ __ \
         /   \_/.  \  |  /\  ___/ \___ \  |  | |  (  <_> )   |  \   |  \/ __ \|  ||  | \/\  ___/
         \_____\ \_/____/  \___  >____  > |__| |__|\____/|___|  /___|  (____  /__||__|    \___  >
                \__>           \/     \/                      \/     \/     \/                \/ */
    /* the questionnaire-form */
    var feedbackform = document.createElement('div');
    feedbackform.innerHTML =
        '<div class="ui-feedback noselect green">' +

            '<div class="ui-feedback-head">'+
                '<div class="ui-feedback-help-button"></div>' +
                '<h2 class="h_green">' + mw.message('ui-feedback-headline', mw.user).escaped() + '</h2>' +
                '<div class="ui-feedback-close"></div>' +
            '</div>'+ // end head

            '<form id="ui-feedback-form" method="post" action="' + mw.util.wikiGetlink('Special:UiFeedback_api') + '" target="_new" enctype="multipart/form-data">' + /*target="ui-feedback-iframe"*/
            '<ul>' +

                /*i wanted to*/
                '<li id="ui-feedback-task-li">'+
                    '<label class="headline">' + mw.message('ui-feedback-task-label', mw.user).escaped() + '</label><br/>' +
                    '<select name="ui-feedback-task" id="ui-feedback-task">' +
                    '	<option value="add/edit a item">'+mw.message('ui-feedback-task-1', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit a label">'+mw.message('ui-feedback-task-2', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit a description">'+mw.message('ui-feedback-task-3', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit a alias">'+mw.message('ui-feedback-task-4', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit a links">'+mw.message('ui-feedback-task-5', mw.user).escaped()+'</option>' +
                    '	<option value="search">'+mw.message('ui-feedback-task-6', mw.user).escaped()+'</option>' +
                    '	<option value="other">'+mw.message('ui-feedback-task-7', mw.user).escaped()+'</option>' +
                    '</select>'+
                '</li>' +

                /* what happened */
                '<li>'+
                    '<label class="headline" for="ui-feedback-happened">' + mw.message('ui-feedback-happened-label', mw.user).escaped() + '</label><br/>' +
                    '<label><input type="radio" name="ui-feedback-happened" value="1" />' + mw.message('ui-feedback-happened-1', mw.user).escaped() + '</label><br/>' +
                    '<label><input type="radio" name="ui-feedback-happened" value="2" />' + mw.message('ui-feedback-happened-2', mw.user).escaped() + '</label><br/>' +
                    '<label><input type="radio" name="ui-feedback-happened" value="3" />' + mw.message('ui-feedback-happened-3', mw.user).escaped() + '</label><br/>' +
                    '<label><input type="radio" name="ui-feedback-happened" value="4" />' + mw.message('ui-feedback-happened-4', mw.user).escaped() + '</label><br/>' +
                '</li>' +

                 /*  */
                '<li>'+
                    '<label class="headline" for="ui-feedback-text1">' + mw.message('ui-feedback-comment-label', mw.user).escaped() + '<br/>' +
                    '<textarea class="ui-feedback-textarea" id="ui-feedback-text1" name="ui-feedback-text1" rows="3" id="textarea_id1" resize-y></textarea></label>'+
                '</li>' +

                /* done */
                '<li>'+
                    '<label class="headline" for="ui-feedback-done">' + mw.message('ui-feedback-done-label', mw.user).escaped() + '</label><br/>' +
                    '<label><input type="radio" name="ui-feedback-done" value="1" />' + mw.message('ui-feedback-yes', mw.user).escaped() + '</label>' +
                    '<label><input type="radio" name="ui-feedback-done" value="0" />' + mw.message('ui-feedback-no', mw.user).escaped() + '</label>' +
                '</li>' +

                /* importance */
                '<li>' +
                    '<label class="headline">' + mw.message('ui-feedback-importance-label', mw.user).escaped() + '</label><br/>' +
                    '<label><small>'+ mw.message('ui-feedback-importance-1', mw.user).escaped() + '</small><input type="radio" name="ui-feedback-importance" value="1" />' +'</label>' +
                    '<label><input type="radio" name="ui-feedback-importance" value="2" />' +'</label>' +
                    '<label><input type="radio" name="ui-feedback-importance" value="3" />' +'</label>' +
                    '<label><input type="radio" name="ui-feedback-importance" value="4" />' +'</label>' +
                    '<label><input type="radio" name="ui-feedback-importance" value="5" /><small>' +mw.message('ui-feedback-importance-5', mw.user).escaped() + '</small></label>' +
                '</li>' +

                '<br/><hr/><br/>' +

                '<li>'+
                    '<input type="checkbox" id="ui-feedback-anonymous" name="ui-feedback-anonymous" value="true">'+
                    '<label for="ui-feedback-anonymous">' + mw.message('ui-feedback-anonym-label', mw.user).escaped() + '</label>'+
                    '<div class="ui-feedback-help-icon" title="'+mw.message('ui-feedback-anonym-help', mw.user).escaped()+'"></div>' +
                '</li>' +

                '<li>' +
                    '<input type="checkbox" id="ui-feedback-notify" name="ui-feedback-notify" value="true">'+
                    '<label for="ui-feedback-notify">' + mw.message('ui-feedback-notify-label', mw.user).escaped() + '</label>' +
                    '<div class="ui-feedback-help-icon" title="'+mw.message('ui-feedback-notify-help', mw.user).escaped()+'"></div>' +
                '</li>' +
            '</ul>' +

            '<input type="hidden" name="ui-feedback-username" value="'+wgUserName+'">' +
            '<input type="hidden" name="ui-feedback-useragent" value="' + navigator.userAgent + '"/>' +
            '<input type="hidden" name="ui-feedback-type" value="0" />' +
            '<input type="hidden" name="ui-feedback-url" value="'+document.URL+'" />' +

            '<div id="ui-feedback-action-buttons" >'+
//                '<input type="button" name="cancel" id="ui-feedback-close" value="' + mw.message('ui-feedback-problem-close', mw.user).escaped() + '" />' +
                '<input type="button" name="reset" id="ui-feedback-reset" value="' + mw.message('ui-feedback-problem-reset', mw.user).escaped() + '" />'+
                '&nbsp;<input type="button" name="send" id="ui-feedback-send" value="' + mw.message('ui-feedback-problem-send', mw.user).escaped() + '" />' +
            '</div>' +
        '</form>' +
    '</div>';//end questionnaire-form

    /*    _________                                         .__            __
         /   _____/ ___________   ____   ____   ____   _____|  |__   _____/  |_
         \_____  \_/ ___\_  __ \_/ __ \_/ __ \ /    \ /  ___/  |  \ /  _ \   __\
         /        \  \___|  | \/\  ___/\  ___/|   |  \\___ \|   Y  (  <_> )  |
        /_______  /\___  >__|    \___  >\___  >___|  /____  >___|  /\____/|__|
                \/     \/            \/     \/     \/     \/     \/             */
    /* the 'screenshot' form */
    var screenshotform = document.createElement('div');
    screenshotform.innerHTML = '' +
        '<div class="ui-feedback noselect purple">' +
            '<div class="ui-feedback-head">'+
                '<div class="ui-feedback-help-button"></div>' +
                '<h2 class="h_purple">' + mw.message('ui-feedback-scr-headline', mw.user).escaped() + '</h2>' +
                '<div class="ui-feedback-collapse"></div>' +
                '<div class="ui-feedback-expand"></div>' +
                '<div class="ui-feedback-close"></div>' +
            '</div>'+
            '<form id="ui-feedback-form" method="post" action="' + mw.util.wikiGetlink('Special:UiFeedback') + '" target="ui-feedback-iframe" enctype="multipart/form-data">' +
                '<ul>' +

                    /*i wanted to*/
                    '<li id="ui-feedback-task-li">'+
                    '<label class="headline">' + mw.message('ui-feedback-task-label', mw.user).escaped() + '</label><br/>' +
                    '<select name="ui-feedback-task" id="ui-feedback-task">' +
                    '	<option value="add/edit item">'+mw.message('ui-feedback-task-1', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit label">'+mw.message('ui-feedback-task-2', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit description">'+mw.message('ui-feedback-task-3', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit alias">'+mw.message('ui-feedback-task-4', mw.user).escaped()+'</option>' +
                    '	<option value="add/edit links">'+mw.message('ui-feedback-task-5', mw.user).escaped()+'</option>' +
                    '	<option value="search">'+mw.message('ui-feedback-task-6', mw.user).escaped()+'</option>' +
                    '	<option value="other">'+mw.message('ui-feedback-task-7', mw.user).escaped()+'</option>' +
                    '</select>'+
                    '</li>' +

                    /* highlight/blackout */
                    '<li>'+
                        '<label  class="headline">'+mw.message('ui-feedback-highlight-label', mw.user).escaped()+'</label><br/>'+
                        '<label><input type="radio" id="ui-feedback-highlight-checkbox" name="marker" value="rgba(225,255,0,0.25)" checked><div class="highlight-button"></div> '+mw.message('ui-feedback-yellow', mw.user).escaped() + '</label><br>'+
                        '<label><input type="radio" name="marker" value="#000"><div class="blackout-button"></div> '+mw.message('ui-feedback-black', mw.user).escaped() + '</label>'+
                    '</li>'+
                    /* comment */
                    '<li>' +
                        '<label  class="headline" for="ui-feedback-text3">' + mw.message('ui-feedback-comment-label', mw.user).escaped() + '<br/>' +
                        '<textarea class="ui-feedback-textarea" id="ui-feedback-text3" name="ui-feedback-text3" style="height:80px" resize-y></textarea></label>' +
                    '</li>' +

                    /* done? */
                    '<li>'+
                        '<label class="headline" for="ui-feedback-done">' + mw.message('ui-feedback-done-label', mw.user).escaped() + '</label><br/>' +
                        '<label><input type="radio" name="ui-feedback-done" value="1" />' + mw.message('ui-feedback-yes', mw.user).escaped() + '</label>' +
                        '<label><input type="radio" name="ui-feedback-done" value="0" />' + mw.message('ui-feedback-no', mw.user).escaped() + '</label>' +
                    '</li>' +

                    /* importance */
                    '<li>' +
                        '<label class="headline">' + mw.message('ui-feedback-importance-label', mw.user).escaped() + '</label><br/>' +
                        '<label><small>'+ mw.message('ui-feedback-importance-1', mw.user).escaped() + '</small><input type="radio" name="ui-feedback-importance" value="1" />' +'</label>' +
                        '<label><input type="radio" name="ui-feedback-importance" value="2" />' +'</label>' +
                        '<label><input type="radio" name="ui-feedback-importance" value="3" />' +'</label>' +
                        '<label><input type="radio" name="ui-feedback-importance" value="4" />' +'</label>' +
                        '<label><input type="radio" name="ui-feedback-importance" value="5" /><small>' +mw.message('ui-feedback-importance-5', mw.user).escaped() + '</small></label>' +
                    '</li>' +


                    '<br/><hr/><br/>' +

                    '<li>' +
                        '<input type="checkbox" id="ui-feedback-anonymous-scr" name="ui-feedback-anonymous-scr" value="true">'+
                        '<label for="ui-feedback-anonymous-scr">' + mw.message('ui-feedback-anonym-label', mw.user).escaped() + '</label>' +
                        '<div class="ui-feedback-help-icon" title="'+mw.message('ui-feedback-anonym-help', mw.user).escaped()+'"></div>' +
                    '</li>' +

                    '<li>' +
                        '<input type="checkbox" id="ui-feedback-notify" name="ui-feedback-notify" value="true">'+
                        '<label for="ui-feedback-notify">' + mw.message('ui-feedback-notify-label', mw.user).escaped() + '</label>' +
                        '<div class="ui-feedback-help-icon" title="'+mw.message('ui-feedback-notify-help', mw.user).escaped()+'"></div>' +
                    '</li>' +

                '</ul>' +

                '<input type="hidden" id="ui-feedback-username-scr" name="ui-feedback-username" value="'+wgUserName+'">' +
                '<input type="hidden" name="ui-feedback-useragent" value="' + navigator.userAgent + '"/>' +
                '<input type="hidden" name="ui-feedback-url" value="'+document.URL+'" />' +

                '<div id="ui-feedback-action-buttons" >'+
//                    '<input type="button" name="cancel" id="ui-feedback-close" value="' + mw.message('ui-feedback-problem-close', mw.user).escaped() + '" />' +
                    '<input type="button" name="reset" id="ui-feedback-reset" value="' + mw.message('ui-feedback-problem-reset', mw.user).escaped() + '" />'+
                    '&nbsp;<input type="button" name="send" id="ui-feedback-send_html2canvas" value="' + mw.message('ui-feedback-problem-send', mw.user).escaped() + '" />' +
                '</div>' +

            '</form>' +
         '</div>'; // end screenshot-form

    /*   __________                  __________                   .___
         \______   \_______   ____   \______   \ ____   ____    __| _/___________
         |     ___/\_  __ \_/ __ \   |       _// __ \ /    \  / __ |/ __ \_  __ \
         |    |     |  | \/\  ___/   |    |   \  ___/|   |  \/ /_/ \  ___/|  | \/
         |____|     |__|    \___  >  |____|_  /\___  >___|  /\____ |\___  >__|
                                \/          \/     \/     \/      \/    \/     */
    /* modal confirmation dialoge before rendering the screenshot*/
    var pre_render_dialogue = document.createElement('div');
    $(pre_render_dialogue).addClass('ui-feedback-overlay');
    pre_render_dialogue.innerHTML = '' +
        '<div class="ui-feedback-modal-dialogue grey">' +
            '<div class="title">' +
                '<h3 class="h_purple">Confirm Screenshot-Feedback</h3>' +
                '<div class="ui-feedback-modal-close"></div>'+
            '</div>' +
            '<div class = "text">' +
                mw.message('ui-feedback-prerender-text1', mw.user).escaped()+'<br/><br/>' +
                '<div style="text-align:center;"><b>'+mw.message('ui-feedback-prerender-text2', mw.user).escaped()+'</b></div>'+
            '</div>' +
            '<div class="footer">&nbsp;' +
                '<span class="left"><button type="button" class="cancel">'+mw.message('ui-feedback-problem-cancel', mw.user).escaped()+'</button></span>' +
                '<span class="right"><button type="button" class="send">'+mw.message('ui-feedback-problem-send', mw.user).escaped()+'</button><span>' +
            '</div>' +
        '</div>';// end modal pre-render-dialogue

    /* function to show/hide the pre-render-dialogue*/
    function toggleModalDialogue(){
        $('.ui-feedback-overlay').toggle();
    }

    /*   .___       .__  __
         |   | ____ |__|/  |_
         |   |/    \|  \   __\
         |   |   |  \  ||  |
         |___|___|  /__||__|
                  \/   */
    /* add click handlers and  make the forms movable */
    function init_ui_feedback(){
        var form = feedbackform;
        $(form).remove();
        if(use_html2canvas){
            form = screenshotform;
            $(button).css('height','119px');
            $(button).css('background-position','-70px 0px');
            $(button).hover(
                function () {$(button).css('background-position','-105px 0px');},
                function () {$(button).css('background-position','-70px 0px');}
            );
            $(form).find('.ui-feedback-collapse').click(collapseForm);
            $(form).find('.ui-feedback-expand').click(expandForm);
            $(form).find('.ui-feedback-expand').hide();

        }
        /* add the textbox for other tasks */
        var dropdown = $(form).find("#ui-feedback-task");
        var other_text_box = $('<li><input type="text" name="ui-feedback-task-other"></li>');
        var inserted = false;
        dropdown.change(function() {
            /* add other-textbox when other is selected */
            if(!inserted && $("#ui-feedback-task option:selected").text() == mw.message('ui-feedback-task-7', mw.user).escaped()){
                console.log('other');
                $(other_text_box).css('width',$(dropdown).css('width'));
                $(other_text_box).insertAfter($('#ui-feedback-task-li'));
                $(other_text_box).focus()
                inserted = true;
            }else{
                $(other_text_box).remove();
                inserted = false;
            }
        });

        /* anonymous and notify, only one of them should be checked */
        $(form).find('#ui-feedback-anonymous').change(
            function(){$('#ui-feedback-notify').prop("disabled",!$('#ui-feedback-notify').prop("disabled"));}
        );
        $(form).find('#ui-feedback-anonymous-scr').change(
            function(){$('#ui-feedback-notify').prop("disabled",!$('#ui-feedback-notify').prop("disabled"));}
        );
        $(form).find('#ui-feedback-notify').change(
            function(){
                $('#ui-feedback-anonymous').prop("disabled",!$('#ui-feedback-anonymous').prop("disabled"));
                $('#ui-feedback-anonymous-scr').prop("disabled",!$('#ui-feedback-anonymous-scr').prop("disabled"));
            }
        );

        /*append forms to body and register click-handlers*/
        $('body').append(form);
        $(form).find('.ui-feedback-close').click(toggleForm);
        $(form).find('.ui-feedback-help-button').click(show_help);
        $(form).find('#ui-feedback-close').click(toggleForm);
        $(form).find('#ui-feedback-reset').click(resetForm);
        $(form).find('#ui-feedback-send').click(sendFeedback);
        $(form).find('.ui-feedback').draggable().draggable("option", "opacity", 0.66).draggable({ cancel: "#ui-feedback-form" });
        $(button).click(toggleForm);
        $('.ui-feedback').toggle();
        /*append pre-render dialogue to body*/
        if(use_html2canvas){
            $('body').append(pre_render_dialogue);
            $('.ui-feedback-modal-dialogue').find('.ui-feedback-modal-close').click(toggleModalDialogue);
            $('.ui-feedback-modal-dialogue').find('.cancel').click(toggleModalDialogue);
            $('.ui-feedback-modal-dialogue').draggable();
            $('.ui-feedback-modal-dialogue').draggable("option", "cancel", ".text, .footer");
            $('.ui-feedback-modal-dialogue').draggable({ revert: true });
            $('.ui-feedback-overlay').toggle();
        }
    }

     /*___________                  .__
       \__    ___/___   ____   ____ |  |   ____
         |    | /  _ \ / ___\ / ___\|  | _/ __ \
         |    |(  <_> ) /_/  > /_/  >  |_\  ___/
         |____| \____/\___  /\___  /|____/\___  >
                     /_____//_____/           \/ */
    /* function to show/hide the form */
    function toggleForm(event) {
        /*for the stats*/
        try{
            /*count the clicks on the button*/
            if(event.target.className == 'ui-feedback-button'){
                // count the number of requests shown ) type 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button
                if(use_html2canvas)
                    $.post(mw.util.wikiGetlink('Special:UiFeedback_api'), {'method':'count','type':'2', 'click':'1'});
                else
                    $.post(mw.util.wikiGetlink('Special:UiFeedback_api'), {'method':'count','type':'1', 'click':'1'});
            }
        }catch (e){
            console.log('no target for this event');
        }

        /*toggle*/
        $('.ui-feedback').fadeToggle('fast');
        $(button).animate({width: 'toggle'});
        $('.ui-feedback').animate({top:$(window).scrollTop()+window.innerHeight/10},500);

        /*if html2canvas is used hide the markes*/
        if(use_html2canvas){
            $("body").htmlfeedback("toggle");
            $("body").htmlfeedback('color', $("input[name='marker']:checked").val());

            $('#ui-feedback-anonymous-scr').attr('checked', false);

            $('#ui-feedback-anonymous-scr').change(function(){
                console.log('click');
                if($('#p-personal').is(":visible")){
                    $('#p-personal').hide();
                    $('#ui-feedback-username').attr('value','anonymous');
                }else{
                    $('#p-personal').show();
                    $('#ui-feedback-username').attr('value',wgUserName);
                }
            });
        }
        /*close all help and notify-windows*/
        $('.ui-feedback-help').remove();
        $('.ui-feedback-notification').remove();
    }

    /**
     * sends the Questionnaire-form to the server
     * @param e
     */
    function sendFeedback(e) {
        $.ajax({
            type: "POST",
            url:  mw.util.wikiGetlink('Special:UiFeedback_api'),
            data: $('#ui-feedback-form').serializeArray()
        });

        resetForm();
        toggleForm();
        show_notification('Feedback sent'+'<br/><br/><small>See <a href="'+mw.util.wikiGetlink('Special:UiFeedback')+'">Feedback-Table</a></small>',5000,'green');
//        $.data(this, 'timer', setTimeout(function() {
//            $('.ui-feedback-notification').animate({"right": "-500px"},500);
//        }, 5000));
    }

    /**
     * Collapses the Form to the title-bar (only used in screenshot-form)
     * @param e
     */
    function collapseForm(e){
        $('#ui-feedback-form').slideToggle();
        $('.ui-feedback-collapse').hide();
        $('.ui-feedback-expand').show();
    }

    /**
     * expands the Form
     * @param e
     */
    function expandForm(e){
        $('#ui-feedback-form').slideToggle();
        $('.ui-feedback-collapse').show();
        $('.ui-feedback-expand').hide();

    }

    /*   __________                      __
         \______   \ ____   ______ _____/  |_
         |       _// __ \ /  ___// __ \   __\
         |    |   \  ___/ \___ \\  ___/|  |
         |____|_  /\___  >____  >\___  >__|
                \/     \/     \/     \/ */
    /* resets all not hidden fields of the feedback-forms */
    function resetForm() {
        console.log('resetForm');
        var form = '#ui-feedback-form';
        $(':input', $(form)).each(function(i, item) {
            switch(item.tagName.toLowerCase()) {
                case 'input':
                    switch(item.type.toLowerCase()) {
                        case 'text':
                            item.value = '';
                            break;
                        case 'radio':
                        case 'checkbox':
                            item.checked = '';
                            break;
                    }
                    break;
                case 'select':
                    item.selectedIndex = 0;
                    break;
                case 'textarea':
                    item.value = '';
                    break;
            }
        });
        $('#ui-feedback-highlight-checkbox').attr('checked','checked');
    }

        /*_______          __  .__  _____.__               __  .__
          \      \   _____/  |_|__|/ ____\__| ____ _____ _/  |_|__| ____   ____
          /   |   \ /  _ \   __\  \   __\|  |/ ___\\__  \\   __\  |/  _ \ /    \
         /    |    (  <_> )  | |  ||  |  |  \  \___ / __ \|  | |  (  <_> )   |  \
         \____|__  /\____/|__| |__||__|  |__|\___  >____  /__| |__|\____/|___|  /
                 \/                              \/     \/                    \/ */
    /*shows a notification with given color until timeout*/
    function show_notification(message, timeout, color, offset_top){
        $('.ui-feedback-notification').remove();

        if( timeout == null ) timeout = 5000;
        if( offset_top == null) offset_top = '37%';
        /* the notification-window */
        var notification = document.createElement('div');
        notification.setAttribute('class', 'ui-feedback-notification '+color);
        notification.innerHTML = '&nbsp;';

        $(notification).css('top',offset_top);
        notification.innerHTML = message;

        $(notification).click(function(){
            $('.ui-feedback-notification').animate({"right": "-500px"},500);
        });

        $('body').append(notification);
        $('.ui-feedback-notification').animate({"right": "+=50px"},500);//
    }

    /*     ___ ___         .__
          /   |   \   ____ |  | ______
         /    ~    \_/ __ \|  | \____ \
         \    Y    /\  ___/|  |_|  |_> >
          \___|_  /  \___  >____/   __/
               \/       \/     |__|    */
    /* The Help-Dialogue */
    var help = document.createElement('div');
    help.setAttribute("class", "ui-feedback-help grey noselect");
    help.innerHTML = ''+
    '<div class="title">'+
        '<h3 class="h_green">What to report:</h3>'+
        '<div class="ui-feedback-close"></div>'+
    '</div>'+
    '<div id="help-content"></div>';
    /* Text for both help-dialogues */
    var helpcontent = '<div class="text">'+
        'Elements and interactions that: <ul><li>prevent task completion</li><li>have an effect on task performance or cause a significant delay</li><li>make suggestion necessary</li><li>confuse and frustrate you</li><li>is a minor but annoying detail</li></ul>'+
    '</div>';
    if(use_html2canvas){
        $(help).find('.h_green').removeClass('h_green').addClass('h_purple');       
         helpcontent += ''+
        '<div class="title sub">'+
            '<h3 class="h_purple">How to Use:</h3>'+
        '</div>'+
        '<div class="text">'+
            mw.message('ui-feedback-help-text', mw.user).escaped() +
            '<div class="image"></div>'+
        '</div>';
    }
    $(help).find('#help-content').append(helpcontent); // close help-content

    /**
     * shows the Help-Dialogue
     */
    function show_help(){
        $('body').prepend(help);
        $(help).fadeIn();
        var left = $('.ui-feedback').offset().left;
        var top = $('.ui-feedback').css('top');
        $(help).css('left',left-290);
        $(help).css('top',top);
        $(help).find('.ui-feedback-close').click(function(){
            $(help).fadeOut();
        });
        $(help).draggable().draggable("option", "opacity", 0.66);
        $(help).draggable("option", "cancel", "#help-content");
    }

    /*   ________                                             __                               .___
         \______ \   ____   ____  __ __  _____   ____   _____/  |_  _______   ____ _____     __| _/__.__.
          |    |  \ /  _ \_/ ___\|  |  \/     \_/ __ \ /    \   __\ \_  __ \_/ __ \\__  \   / __ <   |  |
          |    `   (  <_> )  \___|  |  /  Y Y  \  ___/|   |  \  |    |  | \/\  ___/ / __ \_/ /_/ |\___  |
         /_______  /\____/ \___  >____/|__|_|  /\___  >___|  /__|   /\__|    \___  >____  /\____ |/ ____|
                 \/            \/            \/     \/     \/       \/           \/     \/      \/\/     */
    $(document).ready(function () {

        /* insert the button */
        $('body').prepend(button);
        // type: 0 dynamic, 1 static
        if(use_html2canvas){
            $.post(mw.util.wikiGetlink('Special:UiFeedback_api'), {'method':'count','type':'2', 'show':'1'});
        }else{
            $.post(mw.util.wikiGetlink('Special:UiFeedback_api'), {'method':'count','type':'1', 'show':'1'});
        }

        /* insert the form */
        init_ui_feedback();

        /*WikiData post-edit-stuff*/
        try{
            $( wb ).on( 'stopItemPageEditMode', function( a, origin) {
                var offset_top = '37%';
                try{
                    offset_top = $(origin.__toolbarParent[0]).offset().top;
                }catch(e){
                    console.log('cant get parent-object');
                    console.log(origin);
                    offset_top = '37%';
                }
                var color = 'green';
                if(use_html2canvas)
                    color = 'purple';
                console.log(''+origin.API_VALUE_KEY);
                console.log(offset_top);
                if($.cookie("ui-feedback-show-postedit")!='false'){

                    show_notification( 'Please consider sharing your feedback with the developers.</br></br><small id="ui-feedback-show-postedit"><a href="#" >Don\'t ask again</a></small>', 5000, color, offset_top );

                    $.data(this, 'timer', setTimeout(function() {
                        $('.ui-feedback-notification').animate({"right": "-500px"},500);
                    }, 5000));

                    $("#ui-feedback-show-postedit").click(function(e) {
                        $.cookie("ui-feedback-show-postedit", 'false', { path: '/', expires: 21 });
                        console.log('click');
                        $('.ui-feedback-notification').animate({"right": "-500px"},500);
                    });
                }else{
                    console.log('C is for cookie, and cookie is for me! (Cookiemonster)')
                }

                // count the number of requests shown ) type 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button
                $.post(mw.util.wikiGetlink('Special:UiFeedback_api'), {'method':'count','type':'0', 'show':'1'});

            } );
        } catch (e) {
            console.log('wikibase not found');
        }
        /* end wikidata post-edit*/

        /* HTMLFeedback */
        $("body").htmlfeedback({
            onShow: function () {
                $("#htmlfeedback-close").show();
                $('body').css('cursor','crosshair');
                $('.ui-feedback').css('cursor','auto');
                // $('#p-personal').hide();
                $('body').addClass('noselect');
            },
            onHide: function () {
                $("#htmlfeedback-close").hide();
                $('body').css('cursor','auto');
                $('#p-personal').show();
                $('body').addClass('noselect');

            },
            onPreRender: function () {
//                alert("A screenshot will now be rendered and uploaded to the server. That could take some time. Please don't close the browser.");
               // $('#p-personal').hide();
                $(".ui-feedback").hide();
                $('.ui-feedback-help').remove();
                $('body').css('cursor','wait');
                console.time('rendering');

            },
            onPostRender: function (canvas) {
                console.log('postrender');
                //alert("thanks for your patience");
                $(".ui-feedback").show();
                toggleForm();
                $('#p-personal').show();
                $("body").htmlfeedback("toggle");
                $('canvas').css('width','0px').css('height','0px');
                $('.markers').hide();
                $('.htmlfeedback-rect').remove();
                $('body').css('cursor','auto');
                $('body').addClass('noselect');
                show_notification('Feedback sent<br/>thanks for your patience'+'<br/><br/><small>See <a href="'+mw.util.wikiGetlink('Special:UiFeedback')+'">Feedback-Table</a></small>',5000,'purple');
//                $.data(this, 'timer', setTimeout(function() {
//                    $('.ui-feedback-notification').animate({"right": "-500px"},500);
//                }, 5000));
                console.log('postrender done');
                console.time('rendering');
            }
        });

        // Show or hide HTMLFeedback
        $("#ui-feedback-close").click(function () {
            $("body").htmlfeedback("toggle");
            $('canvas').hide();
            $('.markers').hide();
        });

        $("#ui-feedback-close").click(function () {
            $("body").htmlfeedback("toggle");
            $('#p-personal').show();
            $('canvas').hide();
            $('.markers').hide();
            $('body').css('cursor','auto');

        });

        // Reset HTMLFeedback when we reset the form
        $("#ui-feedback-reset").click(function () {
            $('.htmlfeedback-rect').remove();
        });

        // Upload sreenshot and comment to the server
        $("#ui-feedback-send_html2canvas").click(toggleModalDialogue);
        $('.ui-feedback-modal-dialogue').find('.send').click(function (e) {
            toggleModalDialogue();
            e.preventDefault();
                $("body").htmlfeedback("upload", {
                data: {
                    "ui-feedback-type": 1,
                    "ui-feedback-url" : $("input[name=ui-feedback-url]").val(),
                    "ui-feedback-username": wgUserName,
                    "ui-feedback-anonymous": document.getElementById('ui-feedback-anonymous-scr').checked,
                    "ui-feedback-notify": document.getElementById('ui-feedback-notify').checked,
                    "ui-feedback-useragent": navigator.userAgent,
                    "ui-feedback-text1": $("#ui-feedback-text3").val(), /* comment */
                    "ui-feedback-task": $('select[name=ui-feedback-task]').find(":selected").text(),
                    "ui-feedback-task-other":$("#ui-feedback-task-other").val(),
                    "ui-feedback-done": $('input[name=ui-feedback-done]:checked').val(),
                    "ui-feedback-importance": $('input[name=ui-feedback-importance]:checked').val()
                },
                url: mw.util.wikiGetlink('Special:UiFeedback_api')
            });
        });

        // Change marker color
        $("input[name='marker']").change(function () {
            $("body").htmlfeedback('color', $("input[name='marker']:checked").val());
        });
        /*HTMLFeedback END*/

    });


}(mediaWiki, jQuery) );



