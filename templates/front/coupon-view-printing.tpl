<!DOCTYPE html>
<html lang="{$core.language.iso}" dir="{$core.language.direction}">
    <head>
        {ia_hooker name='smartyFrontBeforeHeadSection'}

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <title>{ia_print_title}</title>
        <meta name="description" content="{$core.page['meta-description']}">
        <meta name="keywords" content="{$core.page['meta-keywords']}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="generator" content="Subrion CMS - Open Source Content Management System">
        <base href="{$smarty.const.IA_URL}">

        <link rel="shortcut icon" href="{if !empty($core.config.site_favicon)}{$core.page.nonProtocolUrl}uploads/{$core.config.site_favicon}{else}{$core.page.nonProtocolUrl}favicon.ico{/if}">

        <style>
            body {
                text-align: center;
            }

            img {
                display: block;
                margin: 0 auto 15px;
            }

            button {
                display: inline-block;
            }
        </style>
    </head>

    <body>
        {ia_image file=$item.image class='img-responsive' title=$item.title}

        <button type="button" onclick="javascript:window.print()">{lang key='print'}</button>
    </body>
</html>