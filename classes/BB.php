<?php

Class BB {

    public $bb_search = Array();
    public $bb_replace = Array();

    public function __construct() {
        $this->bb_search = Array();
        $this->bb_search[] = '|\[b\](.*?)\[/b\]|si';
        $this->bb_search[] = '|\[i\](.*?)\[/i\]|si';
        $this->bb_search[] = '|\[s\](.*?)\[/s\]|si';
        $this->bb_search[] = '|\[red\](.*?)\[/red\]|si';
        $this->bb_search[] = '|\[green\](.*?)\[/green\]|si';
        // $this->bb_search[] = '|\[code\]([\s\S]+)\[/code\]|meU';

        $this->bb_replace = Array();
        $this->bb_replace[] = '<b>$1</b>';
        $this->bb_replace[] = '<i>$1</i>';
        $this->bb_replace[] = '<s>$1</s>';
        $this->bb_replace[] = '<span class="red">$1</span>';
        $this->bb_replace[] = '<span class="green">$1</span>';
        // $this->bb_replace[] = '$this->hightlight("$1")';
    }

    function out($text) {
        $text = str_replace(Array('$'), Array('&#036;'), $text);

        if (substr_count($text, '[q]') == substr_count($text, '[/q]')) {
            $text = str_replace(Array('[q]', '[/q]'), Array('<span class="quote">', '</span>'), $text);
        }

        /*
        $text = preg_replace_callback('|\[code\]([\s\S]+)\[/code\]|mU', function ($m) {
                    return BB::hightlight($m[1]);
                }, $text);
        */
        $text = preg_replace($this->bb_search, $this->bb_replace, $text);
        return $text;
    }

    function hightlight($text) {
        return str_replace($text, highlight_string(html_entity_decode($text, ENT_QUOTES), true), $text);
    }

    function board() {
        $html = '<span>
        <span class="open">BB коды</span><div class="close"><div class="bb">
        <span id="bb0" title="[nosmile]">[без смайлов]</span> <span id="bb2" title="[b][/b]">[<b>жирно</b>]</span> <span id="bb3" title="[i][/i]">[<i>курсив</i>]</span> <span id="bb4" title="[s][/s]">[<s>перечёркнуто</s>]</span> <span id="bb5" title="[red][/red]" class="red">[Красный]</span> <span id="bb6" class="green" title="[green][/green]">[Зелень]</span> <span id="bb7" title="[q][/q]">[цитата]</span> <span id="bb1" title="[code][/code]">[<b>Код</b>]</span>
        </div></div></span>';
        return $html;
    }

}
