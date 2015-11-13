function openInbox(){
    var dataUrl     = $( "#inbox-button" ).attr("data-url");

    $.ajax({
        type    : "POST",
        url     : dataUrl,
        async   : false,
        success: function( data ){
            $( "#sysk-body" ).html( data["responseHtml"] );
            if( data["widgetTitle"] ){
                $( "#sysk-body" ).attr( "title", data["widgetTitle"] );
            }

            $( "#sysk-body" ).dialog({
                autoOpen    : true, 
                show        : {
                                effect: "blind",
                                duration: 500
                            },
                hide        : {
                                effect: "blind",
                                duration: 500
                            },
                resizable   : false,
                height      : 'auto',
                width       : 'auto',
                resize      : true,
                modal       : true, 
                open: function() {
                    $('.ui-widget-overlay').addClass('sysk-overlay');
                }
            });
        },
        error: function(data) {
            $( "#sysk-body" ).dialog( "close" );
            $( "#sysk-body" ).html( "" );
        }
    });
}

function openOutbox(){
    var dataUrl     = $( "#outbox-button" ).attr("data-url");
    
    $.ajax({
        type    : "POST",
        url     : dataUrl,
        async   : false,
        success: function( data ){
            $( "#sysk-body" ).html( data["responseHtml"] );
            if( data["widgetTitle"] ){
                $( "#sysk-body" ).attr( "title", data["widgetTitle"] );
            }

            $( "#sysk-body" ).dialog({
                autoOpen    : true, 
                show        : {
                                effect: "blind",
                                duration: 500
                            },
                hide        : {
                                effect: "blind",
                                duration: 500
                            },
                resizable   : false,
                height      : 'auto',
                width       : 'auto',
                dialogClass : "myClass",
                resize      : true,
                modal       : true, 
                open: function() {
                    $('.ui-widget-overlay').addClass('sysk-overlay');
                }
            });
        },
        error: function(data) {
            $( "#sysk-body" ).dialog( "close" );
            $( "#sysk-body" ).html( "" );
        }
    });
}

function senderSYSKstep1(){
    $( "#syskUserChoice" ).show();
    $( "#syskTypeChoice" ).hide();
    $( "#syskPositifChoice" ).hide();
    $( "#syskNegatifChoice" ).hide();
}

function senderSYSKstep2( element ){
    var  dataUrl = $("#validationUrl").val();

    var selected = "";
    $("input[type='radio'][name='form[syskUser]']").each( function(){
        if( $( this ).is(":checked") ){
            selected = $( this ).val();
        }
    });

    $.ajax({
        type    : "POST",
        data    : { "requestedId" : selected },
        url     : dataUrl,
        async   : false,
        success: function( data ){
            $( "#sysk-body" ).html( data["responseHtml"] );
            if( data["status"] == "success" )
            {
                if( data["allowed"] == true ){
                    $( "#irritantAllowedRatio" ).removeClass( "hideClass" );
                    $( "#irritantInsuficcientRatio" ).addClass( "hideClass" );
                    $( "#negativeButton" ).removeClass( "hideClass" );
                }else{
                    $( "#irritantAllowedRatio" ).addClass( "hideClass" );
                    $( "#irritantInsuficcientRatio" ).removeClass( "hideClass" );
                    $( "#negativeButton" ).addClass( "hideClass" );
                }
            }else{
                $( "#sysk-body" ).html( data["responseHtml"] );
            }

        },
        error: function(data) {
            $( "#irritantAllowedRatio" ).addClass( "hideClass" );
            $( "#irritantInsuficcientRatio" ).removeClass( "hideClass" );
            $( "#negativeButton" ).addClass( "hideClass" );
        }
    });

    var parentTd = $( element ).parent();
    var label = $( parentTd ).find( ".elementLb" ).find( "label" );
    $( "#syskTypeChoice" ).find( ".receptorLabel" ).html( $(label).html() );
    $( "#syskUserChoice" ).hide();
    $( "#syskTypeChoice" ).show();

    if( $( "#form_negatifComment" ).val() == "NEGATIVE" ){
        $( "#syskPositifChoice" ).hide();
        $( "#syskNegatifChoice" ).show();
    }else{
        $( "#syskPositifChoice" ).show();
        $( "#syskNegatifChoice" ).hide();
    }
}

function senderSYSKstep3Plus(){
    $( "#syskNegatifChoice" ).hide();
    $( "#syskPositifChoice" ).show();
}

function senderSYSKstep3Minus(){
    $( "#syskPositifChoice" ).hide();
    $( "#syskNegatifChoice" ).show();
}

function negativeTokenSelector(){
    var selectedToken = $( "#form_negatifComment" ).val()
    $( ".irritant-messages" ).find("li").each( function(){
        if( $( this ).attr("class") == "irritant-message-"+selectedToken ){
            $( this ).show();
        }else{
            $( this ).hide();
        }
    });
}

function validateAndSendSYSK(){
    var dataUrl = $( "#syskForm" ).attr("data-action");

    $.ajax({
        type    : "POST",
        url     : dataUrl,
        data    : $( "#syskForm" ).serialize(),
        async   : false,
        success: function( data ){
            $( "#sysk-body" ).html( data["responseHtml"] );
            $( ".tkn-counter" ).each( function(){
                if( data["userTokens"] == 1 ){
                    $( this ).html( "<strong>"+data["userTokens"]+"</strong> token" );
                }else{
                    $( this ).html( "<strong>"+data["userTokens"]+"</strong> tokens" );
                }
            });
        },
        error: function(data) {
            $( "#sysk-body" ).dialog( "close" );
            $( "#sysk-body" ).html( "" );
        }
    });
}

function openRandomSYSK(){
    var dataUrl = $( "#spend1token" ).attr("data-url");

    $.ajax({
        type    : "POST",
        url     : dataUrl,
        async   : false,
        success: function( data ){
            $( ".receiver-column" ).each( function(){
                $( this ).html( data["receivedHtml"] );
            });

            $( ".tkn-counter" ).each( function(){
                if( data["userTokens"] == 1 ){
                    $( this ).html( "<strong>"+data["userTokens"]+"</strong> token" );
                }else{
                    $( this ).html( "<strong>"+data["userTokens"]+"</strong> tokens" );
                }
            });

            $( "#sysk-body" ).html( data["responseHtml"] );
            if( data["widgetTitle"] ){
                $( "#sysk-body" ).attr( "title", data["widgetTitle"] );
            }
        },
        error: function(data) {
            $( "#sysk-body" ).dialog( "close" );
            $( "#sysk-body" ).html( "" );
        }
    });
}

function openAllSYSK(){
    var dataUrl = $( "#spend3tokens" ).attr("data-url");

    $.ajax({
        type    : "POST",
        url     : dataUrl,
        async   : false,
        success: function( data ){
            $( ".receiver-column" ).each( function(){
                $( this ).html( data["receivedHtml"] );
            });

            $( ".tkn-counter" ).each( function(){
                if( data["userTokens"] == 1 ){
                    $( this ).html( "<strong>"+data["userTokens"]+"</strong> token" );
                }else{
                    $( this ).html( "<strong>"+data["userTokens"]+"</strong> tokens" );
                }
            });

            $( "#sysk-body" ).html( data["responseHtml"] );
            if( data["widgetTitle"] ){
                $( "#sysk-body" ).attr( "title", data["widgetTitle"] );
            }
        },
        error: function(data) {
            $( "#sysk-body" ).dialog( "close" );
            $( "#sysk-body" ).html( "" );
        }
    });
}

function goToSyskPage( element ){
    var dataUrl = $( element ).attr("data-url");

    $.ajax({
        type    : "POST",
        url     : dataUrl,
        async   : false,
        success: function( data ){
            console.log( data );
            if( data["status"] == "success" ){
                $("#sysk_errors").html("");
                $("#sysk-historyTable").html(data["responseHtml"]);
            }else{
                $("#sysk_errors").html(data["responseHtml"]);
            }
        },
        error: function(data) {
            $("#sysk_errors").html(data["responseHtml"]);
        }
    });
}