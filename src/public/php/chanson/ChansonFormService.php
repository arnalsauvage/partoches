<?php
/**
 * Service de préparation du formulaire de chanson.
 *
 * Responsabilité unique :
 * - Vérifier les droits d'accès.
 * - Initialiser l'entité Chanson depuis la requête (GET id / POST id).
 * - Déterminer le mode (MAJ / INS).
 * - Retourner un tableau normalisé prêt pour le rendu.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";

class ChansonFormService
{
    /**
     * Retourne un tableau de contexte pour le formulaire.
     *
     * @return array{
     *     mode: string,
     *     chanson: Chanson,
     *     id: int
     * }
     * @throws Exception si les droits sont insuffisants
     */
    public static function prepareForm(int $id = 0): array
    {
        if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
            $url = "chanson_voir.php";
            if ($id > 0) {
                $url .= "?id=" . $id;
            }
            redirection($url);
        }

        $chanson = new Chanson();

        if (isset($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        if ($id > 0) {
            $chanson->chercheChanson($id);
            $mode = "MAJ";
        } else {
            $mode = "INS";
            $chanson->setIdUser($_SESSION['id'] ?? 0);
        }

        return [
            "mode" => $mode,
            "chanson" => $chanson,
            "id" => $id,
        ];
    }
}
