<?php
namespace loader;

class Psr4ClassLoader
{
    private string $prefix;
    private string $baseDir;

    public function __construct(string $prefix, string $baseDir)
    {
        $this->prefix = $prefix;
        $this->baseDir = $baseDir;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass(string $class): void
    {
        if (strpos($class, $this->prefix) !== 0) {
            return;
        }

        $relativeClass = substr($class, strlen($this->prefix));

        $file = $this->baseDir . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require_once $file;
        }
    }
}