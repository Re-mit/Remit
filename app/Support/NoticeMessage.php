<?php

namespace App\Support;

class NoticeMessage
{
    /** Render plain text and Markdown-style HTTP(S) links as safe HTML. */
    public static function render($message): string
    {
        $message = (string) $message;
        $pattern = '~\[([^\[\]\r\n]+)\]\((https?://[^\s<>()]+)\)~i';
        preg_match_all($pattern, $message, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $html = '';
        $offset = 0;

        foreach ($matches as $match) {
            $html .= e(substr($message, $offset, $match[0][1] - $offset));
            $url = $match[2][0];

            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $html .= '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline break-all">'.e($match[1][0]).'</a>';
            } else {
                $html .= e($match[0][0]);
            }

            $offset = $match[0][1] + strlen($match[0][0]);
        }

        return $html.e(substr($message, $offset));
    }
}
