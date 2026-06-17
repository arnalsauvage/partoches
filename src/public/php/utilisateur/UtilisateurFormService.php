<?php
/**
 * Service de préparation du formulaire Utilisateur.
 */
class UtilisateurFormService
{
    public static function prepareData(int $id): array
    {
        $donnee = Utilisateur::chercheUtilisateur($id);
        $mode = ($id > 0) ? "MAJ" : "INS";

        if ($id > 0) {
            if (!$donnee || (($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) && $_SESSION['user'] != $donnee[1])) {
                redirection("utilisateur_liste.php?msg=AUTH_DENIED");
            }
            $donnee[2] = Chiffrement::decrypt($donnee[2]);
        } else {
            if ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
                redirection("utilisateur_liste.php?msg=AUTH_DENIED");
            }
            $donnee = [0, "", "", "", "", "", "http://", "@", "Devise ou citation...", "1970-01-01", 0, 0];
        }

        return [
            'mode' => $mode,
            'donnee' => $donnee,
            'id' => $id
        ];
    }
}
