<?php

class View {

    protected $File = '';
    protected $FilePath = '';
    public $Content = '';
    protected $Data = [];
    protected $ReturnContent = false;

    function __construct(string $File, array $Data = [], bool $ReturnContent = false) {

        try {
            dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
            dd(' call From-' . FuncCallFrom());
            $this->File = !empty($File) ? (trim(trim(trim($File), '/'), '.php') . '.php' ) : throw new Exc('View are Not calling', 1404);
            !file_exists($this->FilePath = (App . 'Views' . DS . $this->File)) ? throw new Exc(('View Not Found @ ' . $this->FilePath), 1404) : null;
            $this->Data = $Data;
            $this->ReturnContent = $ReturnContent;
        } catch (Exc $E) {
            Response($E->getCode(), $E->getMessage(), isset($D) ? $D : [], $E);
        } finally {
            dd('finaly run View');
        }
    }

    /**
     * Safely escape/encode the provided data.
     */
    public function HTML($Data) {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
        return htmlentities( ((string) $Data));
        return; //htmlentities(htmlspecialchars((string) $Data, ENT_QUOTES, 'UTF-8'));
    }

    function Render() {
        return html_entity_decode($this->Content());
    }

    function Content() {
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
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
        dd(' call ' . __CLASS__ . '@' . __FUNCTION__ . ' Line @' . __LINE__);
        dd(' call From-' . FuncCallFrom());
    }

}
