<?php foreach ($data as $item): ?>
    <?php
    $item->params = json_decode($item->params, false);
    $lastSize     = end($item->params);
    $targetDir    = getenv('flickr_storage').'/'.$item->owner;

    $fileName = basename($lastSize->source);
    $fileName = explode('?', $fileName);
    $fileName = $fileName[0];
    $saveTo   = $targetDir.'/'.$fileName;
    $url      = 'http://xgallery.soulevil.com/'.$item->owner.'/'.$fileName;
    if (!file_exists($saveTo)) {
        continue;
    }
    ?>
    <li>
        <a href="<?php echo $url; ?>"><?php echo(empty($item->title) ? $fileName : $item->title); ?></a>
        <ul>
            <li><?php echo $lastSize->width . 'x' . $lastSize->height; ?></li>
        </ul>
    </li>
<?php endforeach; ?>
