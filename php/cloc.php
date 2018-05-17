<?php
/**
 * Created by PhpStorm.
 * User: medin
 * Date: 22/11/2017
 * Time: 00:44
 */

/******************************************************************************/
/*                                                                            */
/*                       __        ____                                       */
/*                 ___  / /  ___  / __/__  __ _____________ ___               */
/*                / _ \/ _ \/ _ \_\ \/ _ \/ // / __/ __/ -_|_-<               */
/*               / .__/_//_/ .__/___/\___/\_,_/_/  \__/\__/___/               */
/*              /_/       /_/                                                 */
/*                                                                            */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Titre          : Calcul du nombre de lignes par fichier et total d'un...   */
/*                                                                            */
/* URL            : http://www.phpsources.org/scripts435-PHP.htm              */
/* Auteur         : bud                                                       */
/* Date �dition   : 23 Juil 2008                                              */
/*                                                                            */
/******************************************************************************/

/**
 *** int counter(string $dir)
 ***
 ***        \param $dir: chemin du dossier � parcourir
 **/
function counter($dir)
{
    $handle = opendir($dir);

    $nbLines = 0;

    while( ($file = readdir($handle)) != false )
    {
        if( $file != "." && $file != "..")
        {
            if( !is_dir($dir."/".$file) )
            {
                if( preg_match("#\.(php|html|txt)$#", $file) )
                {
                    $nb = count(file($dir."/".$file));
                    echo $dir,"/",$file," => <strong>",$nb,"</strong><br />n";
                    $nbLines += $nb;
                }
            }
            else
            {
                $nbLines += counter($dir."/".$file);
            }
        }
    }
    closedir($handle);

    return $nbLines;
}

// dossier � parcourir
// '.' signifie que je parcours le dossier o� se trouve mon script
$dir = ".";

$nb = counter($dir);
print("<br />Le projet comporte un total de <strong>".$nb.
    "</strong> lignes<br />\n");

