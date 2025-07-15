<?foreach($arOptions['VIDEO']['VIDEO_FILE'] as $arVideo):?>
    <div class="grid-list__item ">
        <div class="video_from_file <?=$skeletonClass?>">
            <video class="video-js hidden" controls="controls" name="media" data-src="<?=$arVideo['path'];?>"></video>
        </div>
    </div>
<?endforeach;?>