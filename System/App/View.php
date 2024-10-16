<?php
namespace System\App;

use System\Preload\SystemExc;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View
{
    protected $File = '';
    protected $FilePath = '';
    public $Content = '';
    protected $Data = [];
    protected $ReturnContent = false;
    protected $Cache = []; // Internal cache
    const ERROR_CODE = 1404;
    protected $Twig;
    protected $UseTwig; // Flag to enable/disable Twig

    function __construct(string $File, array $Data = [], bool $ReturnContent = false, bool $IsForSystem = false, bool $UseTwig = true)
    {
        $this->File = $this->PrepareFileName($File);
        $this->Data = $Data;
        $this->ReturnContent = $ReturnContent;
        $this->FilePath = $this->GetFilePath($this->File, $IsForSystem);
        $this->UseTwig = false;//$UseTwig; // Enable or disable Twig

        // Validate if the file exists at the given path
        if (!$this->IsFileValid($this->FilePath)) {
            throw new SystemExc('View Not Found: The specified view file was not found at the path: ' . $this->FilePath . '. Please ensure the file exists and the path is correct.', self::ERROR_CODE);
        }

        // Initialize Twig if enabled
        if ($this->UseTwig) {
            $this->InitializeTwig();
        }
    }

    private function InitializeTwig()
    {
        $Loader = new FilesystemLoader(dirname($this->FilePath));
        $this->Twig = new Environment($Loader, [
            'cache' => false, // Set to true for production
            'debug' => true,  // Enable for development
        ]);
    }

    private function IsFileValid(string $FilePath): bool
    {
        return file_exists($FilePath);
    }

    private function PrepareFileName(string $File): string
    {
        if (empty($File)) {
            throw new SystemExc('View Not Called: No view file name provided. Please provide a valid view file name.', self::ERROR_CODE);
        }
        return trim(trim(trim($File), '/'), '.php') . ($this->UseTwig ? '.twig' : '.php'); // Conditional extension
    }

    private function GetFilePath(string $File, bool $IsForSystem): string
    {
        $BasePath = $IsForSystem ? System : App;
        return $BasePath . 'View' . DS . $File;
    }

    public function Html($Data): string
    {
        return is_array($Data) ? array_map([$this, 'Html'], $Data) : htmlentities((string) $Data);
    }

    public function Render(): string
    {
        return html_entity_decode($this->Content());
    }

    public function Content(): string
    {
        $CacheKey = 'view_' . md5($this->FilePath);
    
        // Check if cached content exists and validate it
        if (isset($this->Cache[$CacheKey])) {
            if (filemtime($this->FilePath) > $this->Cache[$CacheKey]['time']) {
                unset($this->Cache[$CacheKey]); // Invalidate cache
            } else {
                return $this->Cache[$CacheKey]['content'];
            }
        }
    
        // Try to render the content
        try {
            if ($this->IsFileValid($this->FilePath)) {
                if ($this->UseTwig) {
                    try {
                        // Render the Twig template
                        $Output = $this->Twig->render(basename($this->FilePath), $this->Data);
                    } catch (\Twig\Error\Error $e) {
                        error_log('Twig Rendering Error: ' . $e->getMessage());
                        $Output = ''; // Set empty if an error occurs
                    }
                } else {
                    ob_start();
                    extract($this->Data);
                    include $this->FilePath; // Passing $this->Data explicitly if needed
                    var_dump($CacheKey);
                    var_dump($this->Data);
                    var_dump( isset($this->Cache[$CacheKey]) ? $this->Cache[$CacheKey]['content'] : 'Error: Unable to render the view. Please try again later.');

                    $Output = ob_get_clean();
                }
    
                // Cache the output with the current file modification time
                $this->Cache[$CacheKey] = [
                    'content' => $this->ReturnContent ? $this->Html($Output) : $Output,
                    'time' => filemtime($this->FilePath)
                ];
    
                return $this->Cache[$CacheKey]['content'];
            }
            throw new SystemExc('File Not Found: The view file does not exist at the path: ' . $this->FilePath . '. Please verify the path and try again.', self::ERROR_CODE);
        } catch (SystemExc $E) {
            $this->HandleError($E);
            // If there's an error, render the cached content if available, else return a default error message
            var_dump( isset($this->Cache[$CacheKey]) ? $this->Cache[$CacheKey]['content'] : 'Error: Unable to render the view. Please try again later.');
        }
    }
    

    private function HandleError(SystemExc $E)
    {
        error_log($E->getMessage());
        $E->Response($E);
    }

    function __destruct()
    {
        // Optional: Clean-up code if needed
    }
}
