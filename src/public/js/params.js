/**
 * PAGE : params.js
 * Logique interactive pour l'administration (paramsEdit.php)
 */

$(document).ready(function(){
    // --- GESTION DES ONGLETS ---
    $('.tab-dj').on('click', function(){
        var target = $(this).data('target');
        $('.tab-dj').removeClass('active');
        $(this).addClass('active');
        $('.pane-dj').removeClass('active');
        $('#' + target).addClass('active');
    });

    // --- TOGGLE MOT DE PASSE ---
    $('.btn-toggle-pwd').click(function(){
        var i = $('#' + $(this).data('target'));
        i.attr('type', i.attr('type') === 'password' ? 'text' : 'password');
        $(this).find('span').toggleClass('glyphicon-eye-open glyphicon-eye-close');
    });

    // --- LECTURE DES LOGS ---
    $('.item-log-dj').click(function(e){
        e.preventDefault();
        var f = $(this).data('file');
        $('.item-log-dj').removeClass('active');
        $(this).addClass('active');
        
        $('#log-view-dj').html('<div class="log-loading-indicator"><span class="glyphicon glyphicon-refresh spin"></span> Lecture...</div>');
        
        $.post('', {action: 'lecture_log', fichier: f}, function(d){ 
            $('#log-view-dj').html(d); 
        });
    });

    // --- CONSOLE SQL ---
    $('#btnRunSqlDj').click(function(){
        $('#sqlResDj').html('Exécution...');
        $.post('', {action: 'execute_sql', sql: $('#sqlQueryDj').val()}, function(d){ 
            $('#sqlResDj').html(d); 
        });
    });

    // --- RÉGÉNÉRATION MÉDIAS ---
    $('#btnRegenMedias').click(function(){
        if (!confirm('Voulez-vous vraiment régénérer tout le catalogue des médias ? Cela peut prendre quelques secondes.')) return;
        var btn = $(this);
        var oldHtml = btn.html();
        btn.prop('disabled', true).html('<span class="glyphicon glyphicon-refresh spin"></span> Régénération...');
        $.post('', {action: 'regenere_medias'}, function(d){
            toastr.success(d);
            btn.prop('disabled', false).html(oldHtml);
        });
    });

    // --- DIAGNOSTIC SYSTÈME ---
    $('#btnRunDiagDj').click(function(){
        $('#diagResDj').html('<div class="diag-loading-indicator"><span class="glyphicon glyphicon-refresh spin"></span> Analyse en cours...</div>');
        $.post('', {action: 'diagnostic_systeme'}, function(d){ 
            $('#diagResDj').html(d); 
        });
    });

    // --- MIGRATIONS BDD ---
    $(document).on('click', '#btnRunMigDj', function(){
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="glyphicon glyphicon-refresh spin"></span> Migration en cours...');
        $.post('', {action: 'run_migrations'}, function(d){
            toastr.success(d);
            $('#btnRunDiagDj').click(); // On rafraîchit le diag pour voir les succès
        });
    });
});
