<?php
namespace iutnc\deefy\audio\lists;

class Playlist extends AudioList
{
    public function addPiste($piste): void { $this->pistes[] = $piste; $this->recalc(); }
    public function removePiste(int $index): void { if (isset($this->pistes[$index])) { array_splice($this->pistes, $index, 1); $this->recalc(); } }
    public function addPistes(array $pistesToAdd): void {
        $existingFiles = array_map(fn($p) => $p->__get('fichier'), $this->pistes);
        foreach ($pistesToAdd as $p) if (!in_array($p->__get('fichier'), $existingFiles)) { $this->pistes[] = $p; $existingFiles[] = $p->__get('fichier'); }
        $this->recalc();
    }
}
