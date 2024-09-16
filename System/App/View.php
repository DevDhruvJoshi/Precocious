<?php

namespace System\App;

use System\Preload\SystemExc;

class View {

    protected $File = '';
    protected $FilePath = '';
    public $Content = '';
    protected $Data = [];
    protected $ReturnContent = false;

    function __construct(string $File, array $Data = [], bool $ReturnContent = false, bool $IsForSytem = false) {

        try {
            sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
            sdd(' call From-' . FuncCallFrom());
            $this->File = !empty($File) ? (trim(trim(trim($File), '/'), '.php') . '.php' ) : throw new SystemExc('View are Not calling', 1404);

            is_dir(dirname($this->FilePath = (($IsForSytem == true ? System : (App) ) . 'View' . DS . $this->File))) ?: throw new SystemExc(('File path id invalid @ ' . $this->FilePath), 1404);
            !file_exists($this->FilePath) ? throw new SystemExc(('View Not Found @ ' . $this->FilePath), 1404) : null;
            $this->Data = $Data;
            $this->ReturnContent = $ReturnContent;
        } catch (SystemExc $E) {
            $E->Response($E);
            //Response($E->getCode(), $E->getMessage(), isset($D) ? $D : [], $E);
            //throw new SystemExc($E->getMessage() ?: 'View Class - Msg not defined', $E->getCode(), $E);
        } finally {
            sdd('finaly run View');
        }
    }

    /**
     * Safely escape/encode the provided data.
     */
    public function HTML($Data) {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
        return htmlentities(((string) $Data));
        return; //htmlentities(htmlspecialchars((string) $Data, ENT_QUOTES, 'UTF-8'));
    }

    function Render() {
        return html_entity_decode($this->Content());
    }

    function Content() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
        if (file_exists($this->FilePath)) {
            extract($this->Data);
            ob_start();
            include $this->FilePath;
            $this->Content = $this->ReturnContent == true ? $this->HTML(ob_get_contents()) : ob_get_contents();
            ob_end_clean();
        }
        return $this->Content;
    }

    function __destruct() {
        sdd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        sdd(' call From-' . FuncCallFrom());
    }
}
