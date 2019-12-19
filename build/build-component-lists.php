<?php

declare(strict_types=1);

const PROJECT_INFO = [
    'laminas' => [
        'title' => 'Components and MVC',
        'file' => 'data/component-list.laminas.json',
    ],
    'mezzio' => [
        'title' => 'Mezzio: PSR-15 Middleware in Minutes',
        'file' => 'data/component-list.mezzio.json',
    ],
];

const CARD_TEMPLATE = <<< 'END'
    <div class="col mb-4">
        <div class="card h-100">
            <div class="card-header bg-{project} text-white">
                {package}
            </div>
            <div class="card-body">
                <h5 class="card-title"><a href="{url}">{name}</a></h5>
                <p class="card-text">{description}</p>
            </div>
        </div>
    </div>

END;

const DECK_TEMPLATE = <<< 'END'
<h3 class="text-{project}">{title}</h3>
<div class="row row-cols-1 row-cols-md-3">
{packages}
</div>


END;

function preparePackage(array $package, string $project) : string
{
    $card = str_replace('{project}', $project, CARD_TEMPLATE);
    foreach ($package as $key => $value) {
        $search = sprintf('{%s}', $key);
        $card = str_replace($search, $value, $card);
    }
    return $card;
}

function prepareProject(string $name, string $title, array $project) : string
{
    $packages = array_map(function ($package) use ($name) {
        return preparePackage($package, $name);
    }, $project);

    return str_replace(
        [
            '{project}',
            '{title}',
            '{packages}',
        ],
        [
            $name,
            $title,
            implode("\n", $packages),
        ],
        DECK_TEMPLATE
    );
}

function fetchProject(string $file) : array
{
    $contents = file_get_contents($file);
    return json_decode($contents, true);
}

function injectProjectContent(string $content, string $file) : void
{
    $homepage = file_get_contents($file);
    $replacement = preg_replace(
        '#(?<start>\<\!-- START COMPONENT LISTS --\>).*?(?<end>\<\!-- END COMPONENT LISTS --\>)#s',
        '$1' . $content . '$2',
        $homepage
    );
    file_put_contents($file, $replacement);
}

chdir(dirname(__DIR__));

$content = '';
foreach (PROJECT_INFO as $project => $projectInfo) {
    $content .= prepareProject($project, $projectInfo['title'], fetchProject($projectInfo['file']));
}

injectProjectContent($content, './index.html');
