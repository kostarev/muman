<?php

Class Smiles {

    public $smiles_array = Array();
    public $smiles_dir;
    public $smile_files_array = Array();
    protected $smiles_search, $smiles_replace, $smiles_search_alt;

    public function __construct() {
        $this->smiles_dir = 'open/smiles';
        $this->smiles_array = file(D . '/' . $this->smiles_dir . '/_define.ini');
        $arr = scandir(D . '/' . $this->smiles_dir);

        foreach ($arr AS $val) {
            if (pathinfo($val, PATHINFO_EXTENSION) == 'gif') {
                $this->smile_files_array[] = $val;
            }
        }

        foreach ($this->smiles_array AS $key => $val) {
            $search_arr = explode(',', $val);
            foreach ($search_arr AS $k => $v) {
                $this->smiles_search[] = trim($v);
                $this->smiles_replace[] = '<img src="' . H . '/' . $this->smiles_dir . '/' . $this->smile_files_array[$key] . '" alt="{alt-' . $key . '}"/>';
                $this->smiles_search_alt[] = "{alt-$key}";
            }
        }
    }

    function out($text) {
        $text = str_replace($this->smiles_search, $this->smiles_replace, $text);
        $text = str_replace($this->smiles_search_alt, $this->smiles_search, $text);
        return $text;
    }

    function board() {
        $html = '<span class="smiles"><span class="open">Смайлы</span><div class="close">';
        foreach ($this->smiles_array AS $key => $val) {
            $search_arr = explode(',', $val);
            $html .= '<img id="sm' . $key . '" src="' . H . '/' . $this->smiles_dir . '/' . $this->smile_files_array[$key] . '" alt="' . trim($search_arr[0]) . '" />';
        }
        $html .= '</div></span>';
        return $html;
    }

}

?>
