<?php

namespace Xfrocks\Api\XF\ApiOnly\Template;

use Xfrocks\Api\Listener;

class Templater extends XFCP_Templater
{
    public function clearRequiredExternalsForApi()
    {
        $this->includeCss = [];
        $this->inlineCss = [];
        $this->includeJs = [];
        $this->inlineJs = [];
    }

    /**
     * @param string $username
     * @return array
     */
    public function getDefaultAvatarStylingForApi($username)
    {
        return $this->getDefaultAvatarStyling($username);
    }

    public function getRequiredExternalsAsHtmlForApi()
    {
        $html = '';

        $includedCss = $this->getIncludedCss();
        if (count($includedCss) > 0) {
            $html .= '<link rel="stylesheet" href="'
                . htmlspecialchars($this->getCssLoadUrl($includedCss))
                . '" />';
        }

        $inlineCss = $this->getInlineCss();
        if (count($inlineCss) > 0) {
            foreach ($inlineCss as $inline) {
                $html .= "<style>$inline</style>";
            }
        }

        $includedJs = $this->getIncludedJs();
        if (count($includedJs) > 0) {
            foreach ($includedJs as $js) {
                $html .= '<script src="' . htmlspecialchars($js) . '"></script>';
            }
        }

        $inlineJs = $this->getInlineJs();
        if (count($inlineJs) > 0) {
            foreach ($inlineJs as $inline) {
                $html .= "<script>$inline</script>";
            }
        }

        return $html;
    }

    public function renderTemplate($template, array $params = [], $addDefaultParams = true)
    {
        $output = parent::renderTemplate($template, $params, $addDefaultParams);

        if ($template === 'public:bb_code_tag_attach' && isset($params['attachment'])) {
            /** @var \XF\Entity\Attachment $attachment */
            $attachment = $params['attachment'];
            if ($attachment->has_thumbnail) {
                $escape = false;
                $linkPublic = $this->escape($this->fnLinkType(
                    $this,
                    $escape,
                    'public',
                    'full:attachments',
                    $attachment,
                    ['hash' => $attachment->temp_hash]
                ));
                $linkApi = $this->escape($this->fnLinkType(
                    $this,
                    $escape,
                    Listener::$routerType,
                    'attachments',
                    $attachment,
                    ['hash' => $attachment->temp_hash]
                ));

                $output = str_replace($linkPublic, $linkApi, $output);

                $data = $attachment->Data;
                if ($data !== null) {
                    $output = self::_addDimensionsBySrc($output, "src=\"$linkApi\"", $data->height, $data->width);

                    /** @var mixed $mixed */
                    $mixed = $attachment;
                    $hasGetThumbnailUrlFull = is_callable([$mixed, 'getThumbnailUrlFull']);
                    $thumbnailUrl = $hasGetThumbnailUrlFull ? $attachment->thumbnail_url_full : $attachment->thumbnail_url;
                    $srcThumbnail = sprintf('src="%s"', $this->escape($thumbnailUrl));
                    $output = self::_addDimensionsBySrc(
                        $output,
                        $srcThumbnail,
                        $data->thumbnail_height,
                        $data->thumbnail_width
                    );
                }
            }
        }

        return $output;
    }

    private static function _addDimensionsBySrc($html, $src, $height, $width)
    {
        if (substr_count($html, $src) !== 1) {
            return $html;
        }

        $html = str_replace($src, $src . " width=\"$width\"", $html);
        $html = str_replace($src, $src . " height=\"$height\"", $html);

        return $html;
    }
}

if (false) {
    // @codingStandardsIgnoreLine
    class XFCP_Templater extends \XF\Template\Templater
    {
        // extension hint
    }
}
