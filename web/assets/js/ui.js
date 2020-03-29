function diagGiveHelp() {
    $('.modal').modal('hide');
    $('#modal_givehelp').modal();
}

function diagAddHelp(what) {
    if(/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/.test($('#plz_footer').val()) == false && 
    what == 'footer'){
        alert('Bitte gebe zuerst deine Postleitzahl an!');
    }
    else if(/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/.test($('#plz_splash').val()) == false && 
    what == 'splash'){
        alert('Bitte gebe zuerst deine Postleitzahl an!');
    }
    else {
        $('.modal').modal('hide');
        $('#add_plz').val($('#plz_footer').val());
        $('#modal_addhelp').modal();
    }
}

function showDiag(id){
    $('.modal').modal('hide');
    $('#modal_' + id).modal();
}

function toggleSonstige(){
    console.log($('#add_what').val());
    if($('#add_what').val() == 'sonstiges'){
        $('#add_sonstiges_bereich').show();
    } else {
        $('#add_sonstiges_bereich').hide();
    }
}

function fullsizeMap(){
    $('#close-selection').hide();
    $('#help-container-open').hide();
    $('#map-container-open').css('height', '100%');
    removePolygon();
    zoomPLZInfo();
}

function filterResults(){

    var _filter = $('#get_what').val();
    var _phone = $('#get_phone').is(':checked');
    var _mo = $('#get_montag').is(':checked');
    var _di = $('#get_dienstag').is(':checked');
    var _mi = $('#get_mittwoch').is(':checked');
    var _do = $('#get_donnerstag').is(':checked');
    var _fr = $('#get_freitag').is(':checked');
    var _sa = $('#get_samstag').is(':checked');
    var _so = $('#get_sonntag').is(':checked');
    
    $('.gethelp-item').each(function() {

        var hide = false;

        if($(this).attr('data-phone') != '1' && _phone == true){
            hide = true;
        }

        if($(this).attr('data-mo') != 1 && _mo == true){
            hide = true;
        }
        if($(this).attr('data-di') != 1 && _di == true){
            hide = true;
        }
        if($(this).attr('data-mi') != 1 && _mi == true){
            hide = true;
        }
        if($(this).attr('data-do') != 1 && _do == true){
            hide = true;
        }
        if($(this).attr('data-fr') != 1 && _fr == true){
            hide = true;
        }
        if($(this).attr('data-sa') != 1 && _sa == true){
            hide = true;
        }
        if($(this).attr('data-so') != 1 && _so == true){
            hide = true;
        }

        if(!$(this).attr('data-what').toLowerCase().includes(_filter.toLowerCase())){
            hide = true;
        }
        
        if(hide == true){
            $(this).fadeOut(250);
        }
        else {
            $(this).fadeIn(250);
        }
        
    });
 
}

// remove the error and success messages
setTimeout(() => {
    $('#alert-success').fadeOut(250); 
    $('#alert-error').fadeOut(250);    
}, 4000);
setTimeout(() => {
    $('#alert-phone').fadeOut(250);   
}, 10000);

$(document).ready(function(){
    $('.phone-mask').inputmask("999[9]/999[9][9][9][9][9][9][9]");
})