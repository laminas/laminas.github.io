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
<h4 id="{anchor}">{name}</h4>
<div class="row row-cols-1 row-cols-md-2">
{packages}
</div>
END;

const CARD_TEMPLATE = <<< 'END'
<div class="col mb-4">
    <div class="card h-100">
        <div class="card-header">
            <a href="{url}" class="stretched-link">
                {package}
            </a>
        </div>
        <div class="card-body">
            <h5 class="card-title">{name}</h5>
            <p class="card-text">{description}</p>
        </div>
    </div>
</div>

END;

const DECK_TEMPLATE = <<< 'END'
<h3 class="display-4">Documentation of {title}<br>
    <small class="text-muted">{subtitle}</small>
</h3>
<hr>
{toc}
{content}
END;

const TOC = <<< 'END'
<div class="toc">
    <h6 class="toc__headline">On this page</h6>
    <ul class="toc__list">{items}</ul>
</div>
END;

const TOC_ITEM = <<< 'END'
<li class="toc__entry">
    <a href="#{anchor}" class="toc__link nav-link">{name}</a>
</li>
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
            '{anchor}',
            '<h4 id=""></h4>',
        ],
        [
            $name,
            implode("\n", $htmlBlocks),
            filterAnchorName($name),
            '',
        ],
        GROUP_TEMPLATE
    );
}

function prepareProject(array $project, string $title, string $subtitle) : string
{
    $groupedPackages = groupPackages($project);

    $html = '';
    foreach ($groupedPackages as $group => $packages) {
        $html .= prepareGroup($group, $packages);
    }

    return str_replace(
        [
            '{content}',
            '{title}',
            '{subtitle}',
            '{toc}',
        ],
        [
            $html,
            $title,
            $subtitle,
            createTableOfContents($project)
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

function groupPackages(array $project) : array
{
    $groupedPackages = [];
    foreach ($project as $package) {
        $groupedPackages[$package['group']][] = $package;
    }
    ksort($groupedPackages);

    return $groupedPackages;
}

function createTableOfContents(array $project) : string
{
    $groupedPackages = groupPackages($project);

    $html = '';
    foreach ($groupedPackages as $group => $packages) {
        if (empty($group)) {
            continue;
        }

        $html .= prepareTocItem($group);
    }

    return str_replace(
        '{items}',
        $html,
        TOC
    );
}

function prepareTocItem(string $name) : string
{
    return str_replace(
        [
            '{anchor}',
            '{name}',
        ],
        [
            filterAnchorName($name),
            $name,
        ],
        TOC_ITEM
    );
}

function filterAnchorName(string $name) : string
{
    return str_replace(' ', '-', strtolower($name));
}

chdir(dirname(__DIR__));

$content = prepareProject(
    fetchProject(PROJECT_INFO[$section]['file']),
    PROJECT_INFO[$section]['title'],
    PROJECT_INFO[$section]['subtitle']
);

injectProjectContent($content, sprintf('./%s/index.html', $section));
