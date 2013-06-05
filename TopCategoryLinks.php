<?php

/**
 * MediaWiki TopCategoryLinks extension
 *
 * Copyright © 2013+ Vitaliy Filippov
 * http://wiki.4intra.net/TopCategoryLinks
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

/**
 * This extension duplicates category links at the top of the page
 * Just install it via LocalSettings.php and it will do the job
 */

if (!defined('MEDIAWIKI'))
{
    echo "This file is an extension to the MediaWiki software and cannot be used standalone.\n";
    die();
}

$wgHooks['DiffViewHeader'][] = 'efDiffClearFloats';
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'efAddTopCatlinks';
$wgHooks['BeforePageDisplay'][] = 'efTopCatlinksBeforePageDisplay';

$wgResourceModules['ext.TopCatlinks'] = array(
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'TopCategoryLinks',
    'styles' => 'catlinks-top.css',
);

function efTopCatlinksBeforePageDisplay($out, $skin)
{
    if (defined('GOOD_RESOURCELOADER'))
    {
        // GOOD_RESOURCELOADER is a simple patch which doesn't load css dynamically
        $out->addModules('ext.TopCatlinks');
    }
    else
    {
        // Don't use ResourceLoader on unpatched MediaWiki!
        // It's DYNAMICALLY LOADING CSS which leads to flash-of-unstyled-top-catlinks! :(
        $out->addHeadItem('catlinks-top', '<link rel="stylesheet" type="text/css" href="'.$wgExtensionAssetsPath.'/TopCategoryLinks/catlinks-top.css" />');
    }
    return true;
}

function efDiffClearFloats($diff, $old, $new)
{
    global $wgOut;
    $wgOut->addHTML('<div style="clear: both"></div>');
    return true;
}

function efAddTopCatlinks($skin, $tpl)
{
    $l = $tpl->data['catlinks'];
    // Strip out $wgCategoryViewer if it's enabled
    $l = preg_replace('#</div>\s*<br[\s/]*>\s*<hr[\s/]*>.*</div>#is', '</div></div>', $l);
    $tpl->data['bodytext'] = '<div id="catlinks-top">' . $l . '</div>' . $tpl->data['bodytext'];
    return true;
}

// Also in several other extensions
// TODO: Move this 'clearfloats' hack from here to a separate extension
// Clear floats for ArticleViewHeader {
if (!function_exists('articleHeaderClearFloats'))
{
    global $wgHooks;
    $wgHooks['ParserFirstCallInit'][] = 'checkHeaderClearFloats';
    function checkHeaderClearFloats($parser)
    {
        global $wgHooks;
        if (empty($wgHooks['ArticleViewHeader']) || !in_array('articleHeaderClearFloats', $wgHooks['ArticleViewHeader']))
        {
            $wgHooks['ArticleViewHeader'][] = 'articleHeaderClearFloats';
        }
        return true;
    }
    function articleHeaderClearFloats($article, &$outputDone, &$useParserCache)
    {
        global $wgOut;
        $wgOut->addHTML('<div style="clear:both;height:1px"></div>');
        return true;
    }
}
// }