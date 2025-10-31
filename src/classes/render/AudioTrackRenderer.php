<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AudioTrack;

abstract class AudioTrackRenderer implements Renderer
{
    protected AudioTrack $track;

    public function __construct(AudioTrack $track) { $this->track = $track; }

    public function render(int $selector): string
    {
        switch ($selector) {
            case self::COMPACT: return $this->renderCompact();
            case self::LONG: return $this->renderLong();
            default: return $this->renderCompact();
        }
    }

    abstract protected function renderCompact(): string;
    abstract protected function renderLong(): string;

    protected function audioTag(): string
    {
        $f = htmlspecialchars("audio/" . $this->track->__get('fichier'));
        return "<audio controls src=\"{$f}\"></audio>";
    }
}
