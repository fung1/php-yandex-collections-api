<?php

namespace YandexCollectionsAPI\Data;

class Content {

    public $sourceType;
    public $sourceUrl;
    const SOURCE_IMAGE = 'image';
    const SOURCE_VIDEO = 'video';

    public function __construct(string $sourceType, ?string $sourceUrl = NULL) {
        $this->sourceType = $sourceType;
        $this->sourceUrl = $sourceUrl;
    }

}
