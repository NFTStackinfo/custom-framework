<?php

namespace Serializers;

class PagingSerializer {
    /* @param null|string $next
     * @param array $items
     * @return array
     */
    public static function detail($next, $items) {
        return [
            'next' => $next,
            'items' => $items,
        ];
    }
}
