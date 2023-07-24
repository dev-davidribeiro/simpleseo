<?php

defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class PlgContentSimpleseo extends CMSPlugin
{
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        if ($context !== "com_content.article") {
            return;
        }

        /** @var HtmlDocument $doc */
        $doc = Factory::getApplication()->getDocument();

        $friendlyUrl = $this->getFriendlyUrl($row);
        $shortenedDescription = substr($row->introtext, 0, 200) . '...';

        $seoTags = $this->renderSeoTags(
            $friendlyUrl,
            $row->title,
            html_entity_decode($shortenedDescription),
            $this->getImage($row)
        );

        $doc->addCustomTag($seoTags);
    }

    private function renderSeoTags(
        string $url,
        string $title,
        string $description,
        string $img
    ): string {
        $html = <<<HTML
            <meta property="og:url" content="{$url}" />
            <meta property="og:type" content="article" />
            <meta property="og:title" content="{$title}" />
            <meta property="og:description" content="{$description}" />
            <meta property="og:image" content="{$img}" />
        HTML;

        return $html;
    }

    private function getFriendlyUrl($article): string
    {
        $url = RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language);
        $uriBase = str_replace('/', '', Uri::base());
        return $uriBase . Route::_($url);
    }

    private function getImage(object $article): string
    {
        $image = Uri::base() . $this->params->get('default_img');

        if ($article->images) {
            $image = Uri::base() . json_decode($article->images)->image_intro;
        }

        return $image;
    }
}
