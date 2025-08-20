<?php
declare(strict_types=1);

return [
    'checkboxWrapper' => '{{label}}',
    'formGroup' => '{{input}}',
    'hiddenBlock' => '<div class="hidden">{{content}}</div>',
    'inputContainer' => '{{frontWord}}<span class="input{{required}}">{{content}}</span>{{backWord}}',
    'inputContainerError' => '{{frontWord}}<span class="input error{{required}}">{{content}}{{error}}</span>{{backWord}}',
    'submitContainer' => '{{content}}',
    'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}>{{text}}</label>',
    'inputSubmit' => '<button type="{{type}}"{{attrs}}>{{text}}</button>',
    'error' => '<span class="warning">{{content}}</span>',
    'select' => '<span class="cmn-select"><select name="{{name}}"{{attrs}}>{{content}}</select></span>',
];
