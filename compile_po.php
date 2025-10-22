<?php
/**
 * Script PHP pour compiler un fichier .po en .mo
 * Usage: php compile_po.php
 */

class PoToMoConverter
{
    public function compile($poFile, $moFile)
    {
        if (!file_exists($poFile)) {
            throw new Exception("Le fichier .po n'existe pas: $poFile");
        }

        $entries = $this->parsePoFile($poFile);
        $this->writeMoFile($moFile, $entries);
        
        echo "Compilation réussie: $poFile -> $moFile\n";
    }

    private function parsePoFile($poFile)
    {
        $content = file_get_contents($poFile);
        $entries = [];
        
        // Diviser le contenu en blocs
        $blocks = preg_split('/\n\n+/', $content);
        
        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block) || strpos($block, '#') === 0) {
                continue; // Ignorer les commentaires et blocs vides
            }
            
            $msgid = '';
            $msgstr = '';
            
            $lines = explode("\n", $block);
            $currentField = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (preg_match('/^msgid\s+"(.*)"$/', $line, $matches)) {
                    $msgid = $this->unescapeString($matches[1]);
                    $currentField = 'msgid';
                } elseif (preg_match('/^msgstr\s+"(.*)"$/', $line, $matches)) {
                    $msgstr = $this->unescapeString($matches[1]);
                    $currentField = 'msgstr';
                } elseif (preg_match('/^"(.*)"$/', $line, $matches)) {
                    // Continuation d'une ligne précédente
                    if ($currentField === 'msgid') {
                        $msgid .= $this->unescapeString($matches[1]);
                    } elseif ($currentField === 'msgstr') {
                        $msgstr .= $this->unescapeString($matches[1]);
                    }
                }
            }
            
            // Ajouter l'entrée si elle est valide
            if (!empty($msgid) && !empty($msgstr)) {
                $entries[$msgid] = $msgstr;
            }
        }
        
        return $entries;
    }
    
    private function unescapeString($str)
    {
        return stripcslashes($str);
    }
    
    private function writeMoFile($moFile, $entries)
    {
        $keys = array_keys($entries);
        $values = array_values($entries);
        
        // Trier par clé pour la cohérence
        array_multisort($keys, $values);
        
        $keyOffsets = [];
        $valueOffsets = [];
        $kv = '';
        
        // Construire les chaînes de clés et valeurs
        foreach ($keys as $i => $key) {
            $keyOffsets[] = strlen($kv);
            $kv .= $key . "\0";
        }
        
        foreach ($values as $i => $value) {
            $valueOffsets[] = strlen($kv);
            $kv .= $value . "\0";
        }
        
        $keyOffsetsTableOffset = 7 * 4 + 16 * count($keys);
        $valueOffsetsTableOffset = $keyOffsetsTableOffset + 8 * count($keys);
        $kvOffset = $valueOffsetsTableOffset + 8 * count($values);
        
        // En-tête MO
        $mo = pack('V', 0x950412de); // Magic number
        $mo .= pack('V', 0);         // Version
        $mo .= pack('V', count($keys)); // Nombre d'entrées
        $mo .= pack('V', 28);        // Offset de la table des clés
        $mo .= pack('V', $keyOffsetsTableOffset); // Offset de la table des valeurs
        $mo .= pack('V', 0);         // Taille de la table de hachage
        $mo .= pack('V', 0);         // Offset de la table de hachage
        
        // Table des longueurs et offsets des clés
        foreach ($keys as $i => $key) {
            $mo .= pack('V', strlen($key));
            $mo .= pack('V', $kvOffset + $keyOffsets[$i]);
        }
        
        // Table des longueurs et offsets des valeurs
        foreach ($values as $i => $value) {
            $mo .= pack('V', strlen($value));
            $mo .= pack('V', $kvOffset + $valueOffsets[$i]);
        }
        
        // Données des clés et valeurs
        $mo .= $kv;
        
        if (file_put_contents($moFile, $mo) === false) {
            throw new Exception("Impossible d'écrire le fichier .mo: $moFile");
        }
    }
}

// Utilisation
try {
    $converter = new PoToMoConverter();
    
    // Chemin vers les fichiers
    $poFile = __DIR__ . '/locales/fr_FR.po';
    $moFile = __DIR__ . '/locales/fr_FR.mo';
    
    $converter->compile($poFile, $moFile);
    
    echo "✅ Compilation terminée avec succès!\n";
    echo "Fichier généré: $moFile\n";
    echo "Taille: " . filesize($moFile) . " octets\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>