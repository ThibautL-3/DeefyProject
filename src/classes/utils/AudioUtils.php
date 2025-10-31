<?php
namespace iutnc\deefy\utils;

use getID3;

class AudioUtils {
    /* Analyse un fichier audio et retourne un tableau avec certaines infos : durée (en secondes) */
    public static function getAudioInfo(string $filePath): array {
        $result = [
            'duree' => null,
        ];

        if (!file_exists($filePath)) {
            return $result;
        }

        $getID3 = new getID3();
        $info = $getID3->analyze($filePath);

        if (isset($info['playtime_seconds'])) {
            $result['duree'] = (int) round($info['playtime_seconds']);
        }

        return $result;
    }
}
