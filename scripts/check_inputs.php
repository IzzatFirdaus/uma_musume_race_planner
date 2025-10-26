<?php

libxml_use_internal_errors(true);
$html = file_get_contents('http://127.0.0.1:8000/');
$dom = new DOMDocument;
$dom->loadHTML($html);
$inputs = $dom->getElementsByTagName('input');
$labels = $dom->getElementsByTagName('label');
$labelFor = [];
foreach ($labels as $label) {
    $for = $label->getAttribute('for');
    if ($for) {
        $labelFor[$for] = true;
    }
}
foreach ($inputs as $input) {
    $id = $input->getAttribute('id');
    if ($id) {
        if (! isset($labelFor[$id]) && ! $input->getAttribute('aria-label') && ! $input->getAttribute('aria-labelledby')) {
            echo "INPUT WITHOUT LABEL: id={$id}, name=".$input->getAttribute('name')."\n";
        }
    }
}
