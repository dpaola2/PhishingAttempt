{if $config.smd_google_name}
<!-- Google Authorship and Publisher Markup -->
<link rel="author" href="https://plus.google.com/u/0/+{$config.smd_google_name}/posts"/>
<link rel="publisher" href="https://plus.google.com/u/0/+{$config.smd_google_name}"/>
{/if}

<!-- Schema.org markup for Google+ -->
{if $pageInfo.name}
<meta itemprop="name" content="{$pageInfo.name}">
{/if}
{if $pageInfo.meta_description}
<meta itemprop="description" content="{$pageInfo.meta_description}">
{/if}
{if $smd_logo}
<meta itemprop="image" content="{$smd_logo}">
{/if}

<!-- Twitter Card data -->
<meta name="twitter:card" content="{if $pageInfo.Controller == 'listing_details'}product{else}summary{/if}">
{if $pageInfo.name}
<meta name="twitter:title" content="{$pageInfo.name}">
{/if}
{if $pageInfo.meta_description}
<meta name="twitter:description" content="{$pageInfo.meta_description}">
{/if}
{if $config.smd_twitter_name}
<meta name="twitter:site" content="{$config.smd_twitter_name}">
{/if}
{if $smd_logo}
<meta name="twitter:image" content="{$smd_logo}">
{/if}
{if $pageInfo.Controller == 'listing_details'}
{if $smd_price}
<meta name="twitter:data1" content="{$smd_price.currency}{$smd_price.value}">
<meta name="twitter:label1" content="Price">
{/if}
{if $smd_second_field}
<meta name="twitter:data2" content="{$smd_second_field.value|escape:'html'}">
<meta name="twitter:label2" content="{$smd_second_field.key}">
{/if}
{/if}

<!-- Open Graph data -->
{if $pageInfo.name}
<meta property="og:title" content="{$pageInfo.name}" />
{/if}
<meta property="og:type" content="{if $pageInfo.Controller == 'listing_details'}product{else}website{/if}" />
{if $pageInfo.meta_description}
<meta property="og:description" content="{$pageInfo.meta_description}" />
{/if}
<meta property="og:url" content="http{if $smarty.server.HTTPS == 'on'}s{/if}://{$smarty.server.HTTP_HOST}{if $smarty.server.REQUEST_URI != "/"}{$smarty.server.REQUEST_URI}{/if}" />

{if is_array($photos) && $photos|@count > 1}
{foreach from=$photos item='photo'}
{if ($photo.Type == 'photo' || $photo.Type == 'main') && $photo.Photo}
<meta property="og:image" content="{if $tpl_settings.type != 'responsive_42'}{$smarty.const.RL_FILES_URL}{/if}{$photo.Photo}" />
{/if}
{/foreach}
{else}
{if $smd_logo}
<meta property="og:image" content="{$smd_logo}" />
{/if}
{/if}

{if $config.site_name}
<meta property="og:site_name" content="{$config.site_name}" />
{/if}
{if $pageInfo.Controller == 'listing_details' && $smd_price && $curConv_rates[$smd_price.currency_code].Code}
<meta property="og:price:amount" content="{$smd_price.og_value}" />
<meta property="og:price:currency" content="{$curConv_rates[$smd_price.currency_code].Code}" />
{/if}