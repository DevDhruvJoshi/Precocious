<?php
namespace System\App;

use System\Preload\SystemExc;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View {
    protected $File = '';
    protected $FilePath = '';
    public $Content = '';
    protected $Data = [];
    protected $ReturnContent = false;
    protected $Cache = []; // Internal cache
    const ERROR_CODE = 1404;
    protected $Twig;
    protected $UseTwig; // Flag to enable/disable Twig

    function __construct(string $File, array $Data = [], bool $ReturnContent = false, bool $IsForSystem = false, bool $UseTwig = true) {
        $this->File = $this->PrepareFileName($File);
        $this->Data = $Data;
        $this->ReturnContent = $ReturnContent;
        $this->FilePath = $this->GetFilePath($this->File, $IsForSystem);
        $this->UseTwig = $UseTwig; // Set the flag

        if (!$this->isFileValid($this->FilePath)) {
            throw new SystemExc('View Not Found @ ' . $this->FilePath, self::ERROR_CODE);
        }

        if ($this->UseTwig) {
            $this->initializeTwig();
        }
    }

    private function initializeTwig() {
        $loader = new FilesystemLoader(dirname($this->FilePath));
        $this->Twig = new Environment($loader, [
            'cache' => false, // Set to true for production
            'debug' => true,  // Enable for development
        ]);
    }

    private function isFileValid(string $FilePath): bool {
        return file_exists($FilePath);
    }

    private function PrepareFileName(string $File): string {
        if (empty($File)) {
            throw new SystemExc('View not calling', self::ERROR_CODE);
        }
        return trim(trim(trim($File), '/'), '.php') . ($this->UseTwig ? '.twig' : '.php'); // Conditional extension
    }

    private function GetFilePath(string $File, bool $IsForSystem): string {
        $BasePath = $IsForSystem ? System : App;
        return $BasePath . 'View' . DS . $File;
    }

    public function HTML($Data): string {
        return is_array($Data) ? array_map([$this, 'HTML'], $Data) : htmlentities((string)$Data);
    }

    public function Render(): string {
        return html_entity_decode($this->Content());
    }

    public function Content(): string {
        $CacheKey = 'view_' . md5($this->FilePath);

        // Check if cached content exists and validate it
        if (isset($this->Cache[$CacheKey])) {
            if (filemtime($this->FilePath) > $this->Cache[$CacheKey]['time']) {
                unset($this->Cache[$CacheKey]); // Invalidate cache
            } else {
                return $this->Cache[$CacheKey]['content'];
            }
        }

        try {
            if ($this->isFileValid($this->FilePath)) {
                if ($this->UseTwig) {
                    // Render the Twig template
                    $Output = $this->Twig->render(basename($this->FilePath), $this->Data);
                } else {
                    // Include the PHP file directly
                    ob_start();
                    include $this->FilePath; // Passing $this->Data explicitly if needed
                    $Output = ob_get_clean();
                }

                // Cache the output with the current file modification time
                $this->Cache[$CacheKey] = [
                    'content' => $this->ReturnContent ? $this->HTML($Output) : $Output,
                    'time' => filemtime($this->FilePath)
                ];

                return $this->Cache[$CacheKey]['content'];
            }
            throw new SystemExc('File does not exist at path: ' . $this->FilePath, self::ERROR_CODE);
        } catch (SystemExc $E) {
            $this->HandleError($E);
            return ''; // Return empty content if an error occurs
        }
    }

    private function HandleError(SystemExc $E) {
        error_log($E->getMessage());
        $E->Response($E);
    }

    function __destruct() {
        // Optional: Clean-up code if needed
    }
}
