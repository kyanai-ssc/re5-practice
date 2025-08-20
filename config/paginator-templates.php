<?php
declare(strict_types=1);

return [
    'first' => '<li class="jump-pager first"><a href="{{url}}" class="js_paginator_page" data-url="{{url}}"><span>{{text}}</span></a></li>',
    'last' => '<li class="jump-pager last"><a href="{{url}}" class="js_paginator_page" data-url="{{url}}"><span>{{text}}</span></a></li>',
    'number' => '<li class="num-pager"><a href="{{url}}" class="js_paginator_page" data-url="{{url}}"><span>{{text}}</span></a></li>',
    'prevActive' => '<li class="btn-pager prev"><button type="button" class="js_paginator_page is-prev" data-url="{{url}}" rel="next" value="{{text}}"></button></li>',
    'nextActive' => '<li class="btn-pager next"><button type="button" class="js_paginator_page is-next" data-url="{{url}}" rel="next" value="{{text}}"></button></li>',
    'current' => '<li class="select num-pager"><span>[{{text}}]</span></li>',
    'counterPages' => '<div class="search-counter"><span class="total-counter">{{count}}<span class="txt">件</span></span></div>',
    'sort' => '<button type="button" class="js_paginator_sort" href="{{url}}" data-url="{{url}}" value="{{text}}">{{text}}</button>',
    'sortAsc' => '<button type="button" class="is-upper asc js_paginator_sort" href="{{url}}" data-url="{{url}}">{{text}}</button>',
    'sortDesc' => '<button type="button" class="is-lower asc js_paginator_sort" href="{{url}}" data-url="{{url}}">{{text}}</button>',
    'sortAscLocked' => '<button type="button" class="is-upper asc locked js_paginator_sort" href="{{url}}" data-url="{{url}}">{{text}}</button>',
    'sortDescLocked' => '<button type="button" class="is-upper asc locked js_paginator_sort" href="{{url}}" data-url="{{url}}">{{text}}</button>',
];
