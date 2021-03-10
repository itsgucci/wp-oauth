let pointerNumber = 0;
let totalCardsShown = 0;
let elementType = false;
$mo = jQuery;

$mo(window).load( function() {
    if( ( ! moTour.tourTaken || ! moTour.tconfShown ) && ! moTour.overAllDone ) {
        $mo('#mo_oauth_display_app_name_div').hide();
        startTour(pointerNumber);
    }
    /** Restart tour for current page when clicked on the Restart button */
    $mo("#restart_tour_button").click( function() {
        resetTour();
        tourComplete();
        if(moTour.tourData.length <= 0) {
            return;
        }
        startTour(pointerNumber);
     });
});

/**
 * This function calls the functions that add overlay and create the cards.
 * @param pointerNumber int value
 */
function startTour(pointerNumber){
    if (!moTour.tourData) return;
    $mo("#mo_tutorial_overlay").show();
    // $mo("#mo_tutorial_overlay").css('animation', 'fadeOut 500ms');
    createCard(pointerNumber);
}

/**
 * This function creates the cards and adds them on a calculated position
 * @param pointerNumber
 */
function createCard(pointerNumber) {
    let tourElement =   moTour.tourData[pointerNumber];
    if(!tourElement) {
        abruptEnd();
        return;
    }
    let card        =   '<div id="mo-card" class="mo-card mo-'+tourElement.cardSize+'">'+
                            '<div id="mo-card-arrow" class="mo-tour-arrow mo-point-'+tourElement.pointToSide+'">';
                            if(!tourElement.img.includes("replay.svg")) {
                                card += '<em id="mo-card-arrow-img" style="color:#ffffff;position: relative;" ' +
                                    'class=" dashicons dashicons-arrow-'+tourElement.pointToSide+'"></em>';
                            }
                            card += '</div>'+
                            '<div id="mo-card-content" class="mo-tour-content-area mo-point-'+tourElement.pointToSide+'">'+
                                '<div class="mo-tour-title"><span id="mo-card-content-title">'+tourElement.titleHTML+'</span></div>'+
                                '<div class="mo-tour-content"><span id="mo-card-content-html">'+tourElement.contentHTML+'</span></div>'+
                                '<img id="mo-card-content-img" '+(tourElement.img ? '':'hidden')+' src="'+tourElement.img+'" alt=""> '+
                                '<div class="mo-tour-button-area"></div>'
                                +'<div hidden class="mo-tour-card-bottom"></div>' +
                            '</div>' +
                        '</div>';
    let nextChecker =   tourElement.buttonText === "false" || tourElement.buttonText === '' ? false : true;
    let nextButton  =   '<input id="mo-card-nextButton" type="button"  class="mo-tour-button mo-tour-primary-btn" value="'+tourElement.buttonText+'">';
    let skipButton  =   '<input type="button" id="skippy" class="mo-tour-button mo-skip-btn" value="Skip Tour">';

    $mo("#mo_tutorial_overlay").empty();
    $mo(card).insertAfter('#mo_tutorial_overlay');
    // .animate({
    //     opacity: 1
    // }, 500);
    $mo("#mo-card-arrow").remove();
    $mo("#mo-card-arrow-img").remove();
    // $mo("#mo-card-content-img").hide();
    $mo('.mo-tour-button-area').append(skipButton);
    if(nextChecker) {
        $mo('.mo-tour-button-area').append(nextButton); //Will keep true always
    } else {
        $mo('#skippy').val('End Tour'); // skip a completed tour?
    }

    // Emphasised shadow When not pointing to any element and placed in the center
    if(tourElement.pointToSide=='' || tourElement.pointToSide=='center') {
        $mo('.mo-card').attr('style', 'box-shadow:0px 0px 0px 3px #979393');
    }

    setInterval(function() {
        $mo("#mo-card").css('opacity', 1);
        $mo("#mo-card").css('transform', 'scale(1)');
    }, 100);
    if (tourElement.img.includes("first_tour_gotosignin")) {
        $mo("#mo-card-content-img").remove();
        elementType = "gotosignin";
    }

    // On Next button clicked, create and display next card if exist else Save tour option
    $mo('.mo-tour-primary-btn').click( function() {
        $mo('.mo-target-index').removeClass('mo-target-index');
        if (moTour.tourData[pointerNumber].buttonText === "start_mov") {
            play_fake_config_movie();
        }
        if(elementType) {
            fillValues(elementType);
        }
        pointerNumber+=1;
        if(moTour.tourData[pointerNumber]) {
            changeCard(pointerNumber);
        } else {
            $mo('.mo-card').remove();
            resetTour();
            tourComplete();
        }
    });

    // On Skip button click, Reset the tour and remove the overlay and existing card.
    $mo(".mo-skip-btn").click(function () {
        $mo('.mo-target-index').removeClass('mo-target-index');
        $mo('html, body').animate({
            scrollTop: 0
        }, 300).promise().then(function() {
            moTour.tourData = {};
            showContactSupport();
        });
    });
    handleCardInteractions(pointerNumber);
}

function showContactSupport() {
    var pointerData = {
        'targetE':     'mo_support_layout',
        'pointToSide': 'right',
        'titleHTML':   '<h1>We are here!!</h1>',
        'contentHTML': 'Get in touch with us and we will help you setup the plugin in no time.',
        'buttonText':  'false',
        'img':         moTour.tbase_url + '/help.svg',
        'cardSize':    'big',
    }
    moTour.tourData[0] = pointerData;
    changeCard(0);
    $mo("#mo-card-img").remove();
    $mo("#skippy").val("Close");
    $mo("#skippy").on('click', function () {
        $mo('.mo-target-index').removeClass('mo-target-index');
        $mo('.mo-card').remove();
        resetTour();
        let callback = function () {
            window.location.replace(window.location.href);
        };
        let tourSkipBool = function() {
            return "" === moTour.overAllDone ? false : JSON.parse(moTour.overAllDone);
        }
        if (!tourSkipBool()) {
            callback = function () {
                tourSkip(true, function () {
                    tourComplete(true, function () {
                        secondTourComplete(true, function () {
                            setTconfDone(true, function () {
                                let redirect_url = moTour.base_url;
                                redirect_url += '/wp-admin/admin.php?page=mo_oauth_settings&tab=config';
                                redirect_url += '&action=delete&app=CognitoApp';
                                window.location.replace(redirect_url);
                            });
                        });
                    });
                });
            }
        }
        tourComplete(true, callback);
    });
}

/**
 * Function to handle human interactions.
 * @param {any} pointerNumber
 * @param {any} card
 */
function handleCardInteractions(pointerNumber) {
    let tourElement =   moTour.tourData[pointerNumber];
    // When poiniting to any element, calculate the position of the card
    if(tourElement.targetE) {
        getPointerPosition(tourElement.targetE, tourElement.pointToSide);
    }

    if(tourElement.img.includes("first_tour_testconfig")) {
        elementType = tourElement.img.substring(tourElement.img.indexOf("first_tour_") + "first_tour_".length, tourElement.img.length);
        $mo("#mo-card-arrow").hide();
        $mo("#mo-card-arrow-img").hide();
        $mo("#mo-card-content-img").hide();
        if(elementType) {
            fillValues(elementType);
        }
    }
}
/**
 * This function calculates the Top and Left position for the card to be added
 * w.r.t. the target element. getBoundingClientRect() function returns top, bottom, left,
 * right, height and width of an element.
 * @param targetE     -   Target element to which card points
 * @param pointToSide -   The direction to which card points
 */
function getPointerPosition(targetE,pointToSide,elementy="mo-card") {
    let target = document.getElementById(targetE);
    if(!target) {
        abruptEnd();
        return;
    }
    let targetDimentions = target.getBoundingClientRect();
    let cardDimentions   = document.getElementById(elementy).getBoundingClientRect();
    let finalLeft,finalTop;

    switch(pointToSide) {
        case 'up'    :
            finalLeft   =   targetDimentions.left + (targetDimentions.width - cardDimentions.width)/2 ;
            finalTop    =   targetDimentions.top + targetDimentions.height + 5;
            break;
        case 'down'  :
            finalLeft   =   targetDimentions.left + (targetDimentions.width - cardDimentions.width)/2 ;
            finalTop    =   targetDimentions.top - cardDimentions.height ;
            break;
        case 'left'  :
            finalLeft   =   targetDimentions.left + targetDimentions.width;
            finalTop    =   targetDimentions.top + (targetDimentions.height - cardDimentions.height)/2 ;
            break;
        case 'right' :
            finalLeft   =   targetDimentions.left - cardDimentions.width;
            finalTop    =   targetDimentions.top + (targetDimentions.height - cardDimentions.height)/2 ;
            break;

    }
    // To adjust if card goes out of screen
    if(finalTop<0)  {
        $mo('.mo-tour-arrow>i').css('top','calc(50% + '+finalTop+'px)');
        finalTop=0;
    }

    // Return if element is on screen.
    if(finalTop<=0 && finalLeft<=0) {
        abruptEnd();
        return;
    }
    //Adding the calculated position to the card as css property
    $mo('.'+elementy).css({
        'top':(finalTop+$mo(window).scrollTop()-25),
        'left':(finalLeft+$mo(window).scrollLeft()-180),
        'margin-top':'0','margin-left':'0','position':'absolute'
    });
    if(pointToSide === 'up' || pointToSide === 'down') {
        $mo("#" + elementy).height(function(index,currentheight) {
            return currentheight+10;
        });
    }

    // To get the target Element over the Overlay and highlight
    $mo('#'+targetE).addClass('mo-target-index');
    // Scroll to the target element

    document.getElementById(targetE).scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'center'
    });
    if (elementy==="mo-card-img") {
        $mo("#mo-card").attr('style',$mo("#mo-card").attr('style') + '; position: absolute !important;');
        $mo("#mo-card-img").css('transform','translate(10px, -100px)');
    }
}

/**
 * Removes the backdrop i.e. the overlay and cards, Resets the pointer number to zero
 */
function resetTour() {
    pointerNumber=0;
    $mo('#mo_tutorial_overlay').fadeOut('2000');
    $mo('#mo_tutorial_overlay').hide();
}

/**
 * When the last element is reached, save a tourTaken variable in BD.
 * So that the tour doesn't start automatically next time.
 */
function tourComplete(sync = false, callback) {
    let args = {
        url: moTour.siteURL,
        type: "POST",
        data: {
            doneTour    :true,
            pageID      :moTour.pageID,
            security    :moTour.tnonce,
            action      :moTour.ajaxAction,
        },
        crossDomain: !0, dataType: "json",
    }
    if (sync) {
        args.success = function(result) {
            console.log("before callback");
            callback(result);
            console.log("after callback");
        }
    }
    $mo.ajax(args);
}

function tourSkip(sync = false, callback) {
    let args = {
        url: moTour.siteURL,
        type: "POST",
        data: {
            skipTour    :true,
            pageID      :moTour.pageID,
            security    :moTour.tnonce,
            action      :moTour.ajaxAction,
        },
        crossDomain: !0, dataType: "json",
    }
    if (sync) {
        args.success = function(result) {
            callback(result);
        }
    }
    $mo.ajax(args);
}

function secondTourComplete(sync = false, callback, num = false) {
    let args = {
        url: moTour.siteURL,
        type: "POST",
        data: {
            doneTour    :true,
            pageID      :moTour.pageID,
            security    :moTour.tnonce,
            action      :moTour.ajaxAction,
            tourNum     : num ? num : 2
        },
        crossDomain: !0,dataType: "json",
    };
    if (sync) {
        args.success = function(result) {
            callback(result);
        };
    }
    $mo.ajax(args);
}

function setTconfDone(sync = false, callback) {
    let args = {
        url: moTour.siteURL,
        type: "POST",
        data: {
            doneTour    :true,
            pageID      :moTour.pageID,
            security    :moTour.tnonce,
            action      :moTour.ajaxAction,
            tconfDone   :true
        },
        crossDomain: !0,dataType: "json",
    };
    if (sync) {
        args.success = function(result) {
            callback(result);
        };
    }
    $mo.ajax(args);
}

/**
 * Function to end tour due to errors
 */
function abruptEnd() {
    $mo('.mo-card').remove();
    resetTour();
    tourComplete();
    return;
}

/**
 * Change card content.
 */
function changeCard(pointerNumber) {
    let tourElement =   moTour.tourData[pointerNumber];
    removeCardCss();
    $mo("#mo-card").addClass('mo-'+tourElement.cardSize)
    $mo("#mo-card-arrow").addClass("mo-point-"+tourElement.pointToSide);
    $mo("#mo-card-arrow-img").addClass("dashicons-arrow-"+tourElement.pointToSide);
    if(tourElement.img.includes("replay.svg")) {
        $mo("#mo-card-arrow").hide();
        $mo("#mo-card-arrow-img").hide();
    } else if(tourElement.img.includes("first_tour_")) {
        elementType = tourElement.img.substring(tourElement.img.indexOf("first_tour_") + "first_tour_".length, tourElement.img.length);
        $mo("#mo-card-arrow").hide();
        $mo("#mo-card-arrow-img").hide();
    } else {
        $mo("#mo-card-arrow").show();
        $mo("#mo-card-arrow-img").show();
    }
    $mo("#mo-card-content").addClass("mo-point-"+tourElement.pointToSide);
    $mo("#mo-card-content-title").html(tourElement.titleHTML);
    $mo("#mo-card-content-html").html(tourElement.contentHTML);
    if (tourElement.img) {
        $mo("#mo-card-content-img").hide();
        $mo("#mo-card-content-img").attr("src", tourElement.img);
        if(!tourElement.img.includes("first_tour_")) {
            $mo("#mo-card-content-img").show();
        }
    } else {
        $mo("#mo-card-content-img").hide();
    }
    let nextChecker =   tourElement.buttonText === "false" || tourElement.buttonText === '' ? false : true;
    if(!nextChecker) {
        $mo('#skippy').val('End Tour'); // skip a completed tour?
        $mo('#mo-card-nextButton').remove();
    } else {
        if(tourElement.buttonText === 'start_mov') {
            $mo('#mo-card-nextButton').val("Next");
        } else {
            $mo('#mo-card-nextButton').val(tourElement.buttonText);
        }
    }

    // Emphasised shadow When not pointing to any element and placed in the center
    if(tourElement.pointToSide=='' || tourElement.pointToSide=='center') {
        $mo('.mo-card').attr('style', 'box-shadow:0px 0px 0px 3px #979393');
    }
    if (elementType==="goattrmap") {
        $mo("#mo-card-img").remove();
    }
    if (elementType==="mapuname") {
        insertImage(moTour.tbase_url + 'tconf-h.png');
        sleep(500).then(() => {
            $mo('#mo_oauth_email_attr').val('username');
        });
    }
    if (elementType !== "mo_save_attrmap") {
        handleCardInteractions(pointerNumber);
    }
}

function removeCardCss() {
    $mo("#mo-card").removeClass("mo-big");
    $mo("#mo-card").removeClass("mo-small");
    $mo("#mo-card").removeClass("mo-medium");
    removeClassStartingWith($mo("#mo-card-arrow"), "mo-point-");
    removeClassStartingWith($mo("#mo-card-arrow-img"), "dashicons-arrow-");
    removeClassStartingWith($mo("#mo-card-content"), "mo-point-");
    $mo("#mo-card").removeAttr("style");
}

function removeClassStartingWith(node, begin) {
    node.removeClass (function (index, className) {
        return (className.match ( new RegExp("\\b"+begin+"\\S+", "g") ) || []).join(' ');
    });
}

function play_fake_config_movie() {
    $mo.ajax({
        url: moTour.siteURL,
        type: "POST",
        data: {
            doneTour     :true,
            pageID       :moTour.pageID,
            security     :moTour.tnonce,
            action       :moTour.ajaxAction,
            noShowRocket :true
        },
        crossDomain: !0,dataType: "json",
        success: function(res) {
            $mo('.mo-card').remove();
            let current_url = window.location.href;
            current_url += '&appId=cognito';
            window.location.replace(current_url);
        }
    });
}

function fillValues(type) {
    switch(type) {
        case 'cid':
        case 'appname':
        case 'cs':
            $mo('#mo_oauth_custom_app_name').val('CognitoApp');
            $mo('#mo_oauth_client_id').val(getRandomString(16));
            $mo('#mo_oauth_client_secret').val(getRandomString(32));
            return false;
        case 'endpoints':
            $mo('#mo_oauth_authorizeurl').val('https://example-cognito-domain.com/oauth2/authorize');
            $mo('#mo_oauth_accesstokenurl').val('https://example-cognito-domain.com/oauth2/token');
            $mo('#mo_oauth_resourceownerdetailsurl').val('https://example-cognito-domain.com/oauth2/userInfo');
            return true;
        case 'testconfig':
            setTconfDone(true, function(res) {
                insertImage(moTour.tbase_url + 'tconf-noh.png');
            });
            return true;
        case 'goattrmap':
            tourComplete(true, function(res) {
                window.location.replace($mo("#tab-attrmapping").attr('href'));
            });
            return true;
        case 'mo_save_app':
            secondTourComplete(true, function(res) {
                $mo("#mo_save_app").click();
            });
            return true;
        case 'mo_save_attrmap':
            tourComplete(true, function(res) {
                $mo("#mo_save_attrmap").click();
            });
            return true;
        case 'mapuname':
            $mo('#mo_oauth_email_attr').val('username');
            $mo('#mo_oauth_email_attr_div').addClass('mo-target-index');
            sleep(100).then(() => {
                // $mo("#mo-card-img").remove();
                secondTourComplete(true, function(res) {
                    window.location.replace($mo("#tab-signinsettings").attr('href'));
                });
            });
        case 'gotosignin':
            return true;
        case 'deleteapp':
            secondTourComplete();
            window.location.replace("admin.php?page=mo_oauth_settings&tab=config&action=delete&app=CognitoApp");
            return true;
        default:
            return;
    }
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

function insertImage(imgUrl) {
    let img = '<div id="mo-card-img" class="mo-card mo-card-img"><img id="explanation-img" src="'+imgUrl+'"></div>'
    $mo(img).insertAfter("#mo-card");
    if(imgUrl.includes("tconf-h.png")) {
        $mo("html, body").animate({ scrollTop: 0 }, "slow", function() {
            getPointerPosition("mo-card","down", "mo-card-img");
            // $mo("#mo-card").removeClass("mo-small");
            // $mo("#mo-card").addClass("mo-medium");
            // $mo("#mo-card").css('cssText',"+=;top: " + $mo("#mo_oauth_email_attr_div").position().top * 2 + "px");
            // let winwidth = $mo(window).width();
            // console.log(winwidth);
            // let factor = 2.5;
            // if (winwidth <= 1600) {
            //     factor = 1.25;
            // } else if(winwidth <= 1400) {
            //     factor = 1.0;
            // }
            // $mo("#mo-card-img").css('left', (document.getElementById("mo-card").getBoundingClientRect().left - $mo("#mo-card-img").width()) / factor + 'px');
        });
    } else {
        getPointerPosition("mo-card","left", "mo-card-img");
    }
    $mo("#mo-card").removeClass("mo-target-index");
}

function isScrolledIntoView(elem) {
    var docViewTop = $mo(window).scrollTop();
    var docViewBottom = docViewTop + $mo(window).height();

    var elemTop = $mo(elem).offset().top;
    var elemBottom = elemTop + $mo(elem).height();

    return ((elemBottom < docViewBottom) && (elemTop < docViewTop));
}
function getRandomString(length) {
    var s = "0123456789abcdef";
    var randgen = "";
    var num_parts = length/4;
    for (let i = 0; i < num_parts; i++) {
        randgen += Array(4).join().split(',').map(function() { return s.charAt(Math.floor(Math.random() * s.length)); }).join('');
        if (i < num_parts-1 ) {
            randgen += "-";
        }
    }
    return randgen;
}