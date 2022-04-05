<?php
include_once("document.php");

if (!function_exists('mime_content_type')) {
// Copié sur les commentaires de la fonction mime_content_type sur la doc de php
    function mime_content_type($filename)
    {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}

if ((isset ($_GET ['doc'])) && (is_numeric($_GET ['doc']))) {
    $idDoc = $_GET ['doc'];
    $doc = chercheDocument($idDoc);
    // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
    $fichier = "../../data/" . $doc [5] . "s/" . $doc [6] . "/" . composeNomVersion($doc [1], $doc [4]);
//    header ( "Location: $fichier" );

    //tester si le fichier existe
    if ((!file_exists($fichier))) {
        //ce n'est pas le cas, on envoie l'header 404
        header("HTTP/1.0 404 Not Found");
        echo file_get_contents("404/404.htm");
        echo " <h1>Fichier non trouvé</h1>\n";
        echo " Le document $idDoc $fichier n'a jamais existé, n'existe pas, et n'existera jamais sur ce site !<br>\n";
        echo " Ou alors y 'a longtemps,<br>\n";
        echo " Ou bien j'ai oublié,<br>\n";
        echo " Ou y sentait pas bon...<br>\n";
        echo " Enfin, pour le moment, le document n'existe pas... désolé.<br>\n";
        //puis on quitte le script
        die;
    }
//on indique le mime (type) du fichier
    header('Content-type: ' . mime_content_type($fichier));
//on indique le nom du fichier:
    header('Content-Disposition: attachment; filename="' . composeNomVersion($doc [1], $doc [4]));
//on envoie le fichier source
    readfile($fichier);
    augmenteHits("document", $idDoc);
}
