<?php

/**
 * Folklore Archive – Bashkir State University
 */

// Security: prevent direct access to sensitive files
define('BASEDIR', __DIR__);

// Set UTF-8 header
header("Content-type: text/html; charset=utf-8");

// Page title and initial setup
$title = 'Фольклорный архив Башкирского государственного университета';
include("../t/tb.php");

// Helper functions
function sanitizeId($id) {
    return preg_match('/^[0-9]{1,3}$/', $id) ? $id : null;
}

function getContentFile($pageId) {
    $filePath = BASEDIR . "/efolk/folk_id.{$pageId}.html";
    return is_file($filePath) ? file_get_contents($filePath) : null;
}

function loadContData() {
    $data = [];
    $f = fopen("cont.tsv", "r");
    if ($f) {
        while (($line = fgets($f)) !== false) {
            $fields = explode("\t", trim($line));
            if (count($fields) > 1) {
                $data[$fields[0]] = $fields;
            }
        }
        fclose($f);
    }
    return $data;
}

function getCoordinatesForId($pageId, $contData) {
    foreach ($contData as $fields) {
        $id = str_replace('folk_id.', '', $fields[0]);
        if ($id == $pageId) {
            return [
                'coord' => trim($fields[7] ?? ''),
                'name' => $fields[1] ?? ''
            ];
        }
    }
    return ['coord' => '', 'name' => ''];
}

function generateMapScript($centerLat, $centerLon, $placeName = 'Населенный пункт') {
    return "
    <script>
    ymaps.ready(init);
    var map;
    function init() {
        map = new ymaps.Map('map', {
            center: [$centerLat, $centerLon],
            zoom: 6
        });
        map.behaviors.enable('scrollZoom');
        map.controls.add('zoomControl', { left: 10, top: 100 })
            .add('mapTools', { left: 10, top: 10 })
            .add('typeSelector');
        var objects = [{coords: [$centerLat, $centerLon], name: '$placeName'}];
        var collection = new ymaps.GeoObjectCollection();
        for (var i = 0; i < objects.length; i++) {
            collection.add(new ymaps.Placemark(
                objects[i].coords,
                { balloonContent: '<div style=\"padding:10px; width:400px;\"><h3 style=\"margin-top:0px;\">' + objects[i].name + '</h3></div>' },
                { iconImageHref: 'http://maps.google.com/mapfiles/ms/micons/yellow.png', iconImageSize: [30, 32] }
            ));
        }
        map.geoObjects.add(collection);
    }
    </script>";
}

// Handle request
$pageId = $_GET['id'] ?? null;
$pageParam = $_GET['p'] ?? null;
$searchWord = $_GET['w'] ?? null;
$content = '';

// Case 1: No parameters — show main page with map
if (empty($pageId) && empty($pageParam) && empty($searchWord)) {
    $mainMapScript = generateMapScript(54.746104, 55.948582);
    $content = $mainMapScript . file_get_contents("bashedition.html");
}
// Case 2: Specific entry by ID
elseif (!empty($pageId) && sanitizeId($pageId)) {
    $fileContent = getContentFile($pageId);
    if (!$fileContent) {
        $content = "<p>Извините, этого материала на сайте нет</p>";
    } else {
        // Clean and transform content
        $content = str_replace(
            ["<h1>Фольклорный архив Башкирского государственного университета</h1>", "index.php?go=folk_cont", '<div class="meta">'],
            ["", "bashfolk.php?id=content", '<div class="jumbotron my-5">'],
            $fileContent
        );
        $content .= '<center><div id="map" style="width:700px; height:420px;"></div></center>';

        // Highlight search word if present
        if (!empty($searchWord)) {
            $pattern = "/(\s|«|>)(" . preg_quote($searchWord, '/') . ")(,|\s|!|\.|\?|»|;|<|&)/iu";
            $content = preg_replace($pattern, '\\1<font color="#DD0000"><b>\\2</b></font>\\3', $content);
        }

        // Load coordinates from cont.tsv
        $contData = loadContData();
        $coordInfo = getCoordinatesForId($pageId, $contData);
        $coord = $coordInfo['coord'];
        $name_text = $coordInfo['name'];

        // Generate map
        if (!empty($coord) && strpos($coord, ',') !== false) {
            list($lat, $lon) = explode(',', str_replace(' ', '', $coord));
            $code_map = generateMapScript($lat, $lon, 'Населенный пункт');
            $content = $code_map . $content;
        }

        // Citation block
        $dt = date('d.m.Y');
        $dt_y = date('Y');
        $time_file = date("d.m.Y", filemtime("efolk/folk_id.$pageId.html"));
        $url_eni = "http://nevmenandr.net" . $_SERVER['REQUEST_URI'];
        $lnk = "
        <div class='notice notice-warning'>
            <strong>Ссылка на эту публикацию:</strong>
            <p>{$name_text} [Электронный документ] // Фольклорный архив Башкирского государственного университета: электронное научное издание / под&nbsp;ред. Б.&nbsp;В.&nbsp;Орехова, А.&nbsp;А.&nbsp;Галлямова. [2011–{$dt_y}]. Дата обновления: {$time_file}. URL:&nbsp;{$url_eni} (дата обращения: {$dt}).</p>
        </div>";
        $content .= $lnk;
    }
}
// Case 3: Table of contents (id=content)
elseif ($pageId === 'content') {
    $contData = loadContData();
    $rowsHtml = '';
    foreach ($contData as $fields) {
        $id = str_replace('folk_id.', '', $fields[0]);
        $rowsHtml .= '<tr>
            <td>' . htmlspecialchars($id) . '</td>
            <td><a href="bashfolk.php?id=' . urlencode($id) . '">' . htmlspecialchars($fields[1]) . '</a></td>
            <td>' . htmlspecialchars($fields[2]) . '</td>
            <td>' . htmlspecialchars($fields[3]) . '</td>
            <td>' . htmlspecialchars($fields[4]) . '</td>
            <td>' . htmlspecialchars($fields[6]) . '</td>
        </tr>';
    }

    $content = '
    <div class="row">
        <div class="panel panel-primary filterable">
            <div class="panel-heading">
                <h3 class="panel-title">Оглавление издания</h3>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span> Фильтр</button>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr class="filters">
                        <th><input type="text" class="form-control" placeholder="ID" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Название" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Жанр" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Район" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Год записи" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Ссылка" disabled></th>
                    </tr>
                </thead>
                <tbody>' . $rowsHtml . '</tbody>
            </table>
        </div>
    </div>';
}
// Case 4: Other pages (index or search results)
else {
    if (!empty($pageParam) && preg_match('/^[a-zA-Z0-9_\-]+$/', $pageParam)) {
        $content = file_get_contents("form_index/{$pageParam}.html");
    } elseif (!empty($searchWord) && empty($pageId)) {
        $word = htmlspecialchars($searchWord);
        $fl = '';
        $f = fopen("sniplinks1.dat", "r");
        if ($f) {
            while (($line = fgets($f)) !== false) {
                $fields = explode("\t", trim($line));
                if (isset($fields[1]) && $fields[1] == $searchWord) {
                    $fl = $fields[0];
                    break;
                }
            }
            fclose($f);
        }
        if ($fl && is_file($fl)) {
            $content = file_get_contents($fl);
            $content = str_replace('http://lcph.bashedu.ru/editions/efolk.php?go=folk_id.', 'http://nevmenandr.net/pages/bashfolk.php?id=', $content);
            $content = "<h2>Указатель словоформ. Контексты слова &laquo;$word&raquo;</h2> <p>&nbsp;" . $content;
        } else {
            $content = "<p>No results found for '$word'</p>";
        }
    } else {
        $content = "<p>Запрашиваемая страница не найдена</p>";
    }
}

// Output the final layout
?>
<h1><?= htmlspecialchars($title) ?></h1>
<p>&nbsp;</p>

<? if ($pageId === 'content'): ?>
    <?= $content ?>
<? else: ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <p align="right">ISBN&nbsp;<nobr>978-5-87604-352-8</nobr></p>
            <?= $content ?>
        </div>
    </div>
<? endif; ?>

<script type="text/javascript">
$(document).ready(function(){
    $('.filterable .btn-filter').click(function(){
        var $panel = $(this).parents('.filterable'),
            $filters = $panel.find('.filters input'),
            $tbody = $panel.find('.table tbody');
        if ($filters.prop('disabled') == true) {
            $filters.prop('disabled', false).first().focus();
        } else {
            $filters.val('').prop('disabled', true);
            $tbody.find('.no-result').remove();
            $tbody.find('tr').show();
        }
    });
    $('.filterable .filters input').keyup(function(e){
        if (e.which == 9) return;
        var $input = $(this),
            val = $input.val().toLowerCase(),
            $panel = $input.parents('.filterable'),
            col = $panel.find('.filters th').index($input.parents('th')),
            $rows = $panel.find('.table tbody tr');
        $rows.show().filter(function(){
            var cellText = $(this).find('td').eq(col).text().toLowerCase();
            return cellText.indexOf(val) === -1;
        }).hide();
        if ($rows.filter(':visible').length === 0) {
            $('.table tbody').prepend('<tr class="no-result text-center"><td colspan="'+ $panel.find('.filters th').length +'">No result found</td></tr>');
        } else {
            $('.no-result').remove();
        }
    });
});
</script>
<?php
// Close layout with modified footer
$footer2 = str_replace('</body>', '<script type="text/javascript">window.alert = function(){};</script></body>', $footer2 ?? '');
echo $footer2;
?><?php

/**
 * Folklore Archive – Bashkir State University
 * Professionalized version of bashfolk.php
 */

// Security: prevent direct access to sensitive files
define('BASEDIR', __DIR__);

// Set UTF-8 header
header("Content-type: text/html; charset=utf-8");

// Page title and initial setup
$title = 'Фольклорный архив Башкирского государственного университета';
include("../t/tb.php");

// Helper functions
function sanitizeId($id) {
    return preg_match('/^[0-9]{1,3}$/', $id) ? $id : null;
}

function getContentFile($pageId) {
    $filePath = BASEDIR . "/efolk/folk_id.{$pageId}.html";
    return is_file($filePath) ? file_get_contents($filePath) : null;
}

function loadContData() {
    $data = [];
    $f = fopen("cont.tsv", "r");
    if ($f) {
        while (($line = fgets($f)) !== false) {
            $fields = explode("\t", trim($line));
            if (count($fields) > 1) {
                $data[$fields[0]] = $fields;
            }
        }
        fclose($f);
    }
    return $data;
}

function getCoordinatesForId($pageId, $contData) {
    foreach ($contData as $fields) {
        $id = str_replace('folk_id.', '', $fields[0]);
        if ($id == $pageId) {
            return [
                'coord' => trim($fields[7] ?? ''),
                'name' => $fields[1] ?? ''
            ];
        }
    }
    return ['coord' => '', 'name' => ''];
}

function generateMapScript($centerLat, $centerLon, $placeName = 'Населенный пункт') {
    return "
    <script>
    ymaps.ready(init);
    var map;
    function init() {
        map = new ymaps.Map('map', {
            center: [$centerLat, $centerLon],
            zoom: 6
        });
        map.behaviors.enable('scrollZoom');
        map.controls.add('zoomControl', { left: 10, top: 100 })
            .add('mapTools', { left: 10, top: 10 })
            .add('typeSelector');
        var objects = [{coords: [$centerLat, $centerLon], name: '$placeName'}];
        var collection = new ymaps.GeoObjectCollection();
        for (var i = 0; i < objects.length; i++) {
            collection.add(new ymaps.Placemark(
                objects[i].coords,
                { balloonContent: '<div style=\"padding:10px; width:400px;\"><h3 style=\"margin-top:0px;\">' + objects[i].name + '</h3></div>' },
                { iconImageHref: 'http://maps.google.com/mapfiles/ms/micons/yellow.png', iconImageSize: [30, 32] }
            ));
        }
        map.geoObjects.add(collection);
    }
    </script>";
}

// Handle request
$pageId = $_GET['id'] ?? null;
$pageParam = $_GET['p'] ?? null;
$searchWord = $_GET['w'] ?? null;
$content = '';

// Case 1: No parameters — show main page with map
if (empty($pageId) && empty($pageParam) && empty($searchWord)) {
    $mainMapScript = generateMapScript(54.746104, 55.948582);
    $content = $mainMapScript . file_get_contents("bashedition.html");
}
// Case 2: Specific entry by ID
elseif (!empty($pageId) && sanitizeId($pageId)) {
    $fileContent = getContentFile($pageId);
    if (!$fileContent) {
        $content = "<p>Извините, этого материала на сайте нет</p>";
    } else {
        // Clean and transform content
        $content = str_replace(
            ["<h1>Фольклорный архив Башкирского государственного университета</h1>", "index.php?go=folk_cont", '<div class="meta">'],
            ["", "bashfolk.php?id=content", '<div class="jumbotron my-5">'],
            $fileContent
        );
        $content .= '<center><div id="map" style="width:700px; height:420px;"></div></center>';

        // Highlight search word if present
        if (!empty($searchWord)) {
            $pattern = "/(\s|«|>)(" . preg_quote($searchWord, '/') . ")(,|\s|!|\.|\?|»|;|<|&)/iu";
            $content = preg_replace($pattern, '\\1<font color="#DD0000"><b>\\2</b></font>\\3', $content);
        }

        // Load coordinates from cont.tsv
        $contData = loadContData();
        $coordInfo = getCoordinatesForId($pageId, $contData);
        $coord = $coordInfo['coord'];
        $name_text = $coordInfo['name'];

        // Generate map
        if (!empty($coord) && strpos($coord, ',') !== false) {
            list($lat, $lon) = explode(',', str_replace(' ', '', $coord));
            $code_map = generateMapScript($lat, $lon, 'Населенный пункт');
            $content = $code_map . $content;
        }

        // Citation block
        $dt = date('d.m.Y');
        $dt_y = date('Y');
        $time_file = date("d.m.Y", filemtime("efolk/folk_id.$pageId.html"));
        $url_eni = "http://nevmenandr.net" . $_SERVER['REQUEST_URI'];
        $lnk = "
        <div class='notice notice-warning'>
            <strong>Ссылка на эту публикацию:</strong>
            <p>{$name_text} [Электронный документ] // Фольклорный архив Башкирского государственного университета: электронное научное издание / под&nbsp;ред. Б.&nbsp;В.&nbsp;Орехова, А.&nbsp;А.&nbsp;Галлямова. [2011–{$dt_y}]. Дата обновления: {$time_file}. URL:&nbsp;{$url_eni} (дата обращения: {$dt}).</p>
        </div>";
        $content .= $lnk;
    }
}
// Case 3: Table of contents (id=content)
elseif ($pageId === 'content') {
    $contData = loadContData();
    $rowsHtml = '';
    foreach ($contData as $fields) {
        $id = str_replace('folk_id.', '', $fields[0]);
        $rowsHtml .= '<tr>
            <td>' . htmlspecialchars($id) . '</td>
            <td><a href="bashfolk.php?id=' . urlencode($id) . '">' . htmlspecialchars($fields[1]) . '</a></td>
            <td>' . htmlspecialchars($fields[2]) . '</td>
            <td>' . htmlspecialchars($fields[3]) . '</td>
            <td>' . htmlspecialchars($fields[4]) . '</td>
            <td>' . htmlspecialchars($fields[6]) . '</td>
        </tr>';
    }

    $content = '
    <div class="row">
        <div class="panel panel-primary filterable">
            <div class="panel-heading">
                <h3 class="panel-title">Оглавление издания</h3>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span> Фильтр</button>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr class="filters">
                        <th><input type="text" class="form-control" placeholder="ID" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Название" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Жанр" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Район" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Год записи" disabled></th>
                        <th><input type="text" class="form-control" placeholder="Ссылка" disabled></th>
                    </tr>
                </thead>
                <tbody>' . $rowsHtml . '</tbody>
            </table>
        </div>
    </div>';
}
// Case 4: Other pages (index or search results)
else {
    if (!empty($pageParam) && preg_match('/^[a-zA-Z0-9_\-]+$/', $pageParam)) {
        $content = file_get_contents("form_index/{$pageParam}.html");
    } elseif (!empty($searchWord) && empty($pageId)) {
        $word = htmlspecialchars($searchWord);
        $fl = '';
        $f = fopen("sniplinks1.dat", "r");
        if ($f) {
            while (($line = fgets($f)) !== false) {
                $fields = explode("\t", trim($line));
                if (isset($fields[1]) && $fields[1] == $searchWord) {
                    $fl = $fields[0];
                    break;
                }
            }
            fclose($f);
        }
        if ($fl && is_file($fl)) {
            $content = file_get_contents($fl);
            $content = str_replace('http://lcph.bashedu.ru/editions/efolk.php?go=folk_id.', 'http://nevmenandr.net/pages/bashfolk.php?id=', $content);
            $content = "<h2>Указатель словоформ. Контексты слова &laquo;$word&raquo;</h2> <p>&nbsp;" . $content;
        } else {
            $content = "<p>No results found for '$word'</p>";
        }
    } else {
        $content = "<p>Запрашиваемая страница не найдена</p>";
    }
}

// Output the final layout
?>
<h1><?= htmlspecialchars($title) ?></h1>
<p>&nbsp;</p>

<? if ($pageId === 'content'): ?>
    <?= $content ?>
<? else: ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <p align="right">ISBN&nbsp;<nobr>978-5-87604-352-8</nobr></p>
            <?= $content ?>
        </div>
    </div>
<? endif; ?>

<script type="text/javascript">
$(document).ready(function(){
    $('.filterable .btn-filter').click(function(){
        var $panel = $(this).parents('.filterable'),
            $filters = $panel.find('.filters input'),
            $tbody = $panel.find('.table tbody');
        if ($filters.prop('disabled') == true) {
            $filters.prop('disabled', false).first().focus();
        } else {
            $filters.val('').prop('disabled', true);
            $tbody.find('.no-result').remove();
            $tbody.find('tr').show();
        }
    });
    $('.filterable .filters input').keyup(function(e){
        if (e.which == 9) return;
        var $input = $(this),
            val = $input.val().toLowerCase(),
            $panel = $input.parents('.filterable'),
            col = $panel.find('.filters th').index($input.parents('th')),
            $rows = $panel.find('.table tbody tr');
        $rows.show().filter(function(){
            var cellText = $(this).find('td').eq(col).text().toLowerCase();
            return cellText.indexOf(val) === -1;
        }).hide();
        if ($rows.filter(':visible').length === 0) {
            $('.table tbody').prepend('<tr class="no-result text-center"><td colspan="'+ $panel.find('.filters th').length +'">No result found</td></tr>');
        } else {
            $('.no-result').remove();
        }
    });
});
</script>
<?php
// Close layout with modified footer
$footer2 = str_replace('</body>', '<script type="text/javascript">window.alert = function(){};</script></body>', $footer2 ?? '');
echo $footer2;
?>
