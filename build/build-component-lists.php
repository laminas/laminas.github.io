<?php

declare(strict_types=1);

const PROJECT_INFO = [
    'mvc' => [
        'title'    => 'MVC',
        'subtitle' => 'MVC for Enterprise Applications',
        'file'     => 'data/component-list.mvc.json',
    ],
    'components' => [
        'title'    => 'Components',
        'subtitle' => 'Components for Enterprise Applications',
        'file'     => 'data/component-list.components.json',
    ],
];

$section = $argv[1] ?? null;
if (! array_key_exists($section, PROJECT_INFO)) {
    die('Wrong parameter!');
}

const GROUP_TEMPLATE = <<< 'END'
<h4>{name}</h4>
<div class="row row-cols-1 row-cols-md-2">
{packages}
</div>
END;

const CARD_TEMPLATE = <<< 'END'
<div class="col mb-4">
    <div class="card h-100">
        <a href="{url}">
            <div class="card-header">
                {package}
            </div>
            <div class="card-body">
                <h5 class="card-title">{name}</h5>
                <p class="card-text">{description}</p>
            </div>
        </a>
    </div>
</div>

END;

const DECK_TEMPLATE = <<< 'END'
<h3 class="display-4">Documentation of {title}<br>
    <small class="text-muted">{subtitle}</small>
</h3>
<hr>
{content}
END;

function preparePackage(array $package) : string
{
    $card = CARD_TEMPLATE;
    foreach ($package as $key => $value) {
        $search = sprintf('{%s}', $key);
        $card = str_replace($search, $value, $card);
    }
    return $card;
}

function prepareGroup(string $name, array $packages) : string
{
    $htmlBlocks = array_map(static function ($package) {
        return preparePackage($package);
    }, $packages);

    return str_replace(
        [
            '{name}',
            '{packages}',
            '<h4></h4>',
        ],
        [
            $name,
            implode("\n", $htmlBlocks),
            '',
        ],
        GROUP_TEMPLATE
    );
}

function prepareProject(array $project, string $title, string $subtitle) : string
{
    $groupedPackages = [];
    foreach ($project as $package) {
        $groupedPackages[$package['group']][] = $package;
    }
    ksort($groupedPackages);

    $html = '';
    foreach ($groupedPackages as $group => $packages) {
        $html .= prepareGroup($group, $packages);
    }

    return str_replace(
        [
            '{content}',
            '{title}',
            '{subtitle}',
        ],
        [
            $html,
            $title,
            $subtitle
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

$content = prepareProject(
    fetchProject(PROJECT_INFO[$section]['file']),
    PROJECT_INFO[$section]['title'],
    PROJECT_INFO[$section]['subtitle']
);

injectProjectContent($content, sprintf('./%s/index.html', $section));
