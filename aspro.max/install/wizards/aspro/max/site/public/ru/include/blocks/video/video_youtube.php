<?foreach($arOptions['VIDEO']['VIDEO_YOUTUBE'] as $iframe):?>
    <div class="grid-list__item">
        <div class="video_body iframe ui-card">
            <?=str_replace('src', 'class="'.$skeletonClass.'" data-src', $iframe);?>
        </div>
    </div>
<?endforeach;?>