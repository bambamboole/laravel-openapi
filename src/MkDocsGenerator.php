<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class MkDocsGenerator
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(array $documentationNodes, string $docsBaseDir): void
    {
        $docsOutputDir = $docsBaseDir.'/generated';

        // Build documentation registry and maps
        $registry = $this->buildRegistry($documentationNodes);
        $navPathMap = $this->buildNavPathMap($documentationNodes);
        $usedBy = $this->buildUsedByMap($documentationNodes);

        // Generate the document tree
        $docTree = $this->generateDocTree($documentationNodes, $registry, $navPathMap, $usedBy);

        // Prepare output directory
        $this->filesystem->deleteDirectory($docsOutputDir);
        $this->filesystem->makeDirectory($docsOutputDir, recursive: true);

        // Create welcome page
        $welcomeContent = "# Welcome\n\nThis is the automatically generated functional documentation for the project. \n\nUse the navigation on the left to explore the documented processes.";
        $this->filesystem->put($docsOutputDir.'/index.md', $welcomeContent);

        // Generate files
        $this->generateFiles($docTree, $docsOutputDir);

        // Generate navigation structure
        $navStructure = $this->generateNavStructure($docTree);
        array_unshift($navStructure, ['Home' => 'index.md']);

        // Generate config
        $config = config()->array('mkdocs.config', []);
        $config['nav'] = $navStructure;
        $this->dumpAsYaml($config, $docsBaseDir.'/mkdocs.yml');
    }

    private function buildRegistry(array $documentationNodes): array
    {
        $registry = [];
        foreach ($documentationNodes as $node) {
            $pathSegments = array_map('trim', explode('/', $node['navPath']));
            $pageTitle = array_pop($pathSegments);

            $urlParts = array_map([$this, 'slug'], $pathSegments);
            $urlParts[] = $this->slug($pageTitle).'.md';

            $registry[$node['owner']] = implode('/', $urlParts);
        }

        return $registry;
    }

    private function buildNavPathMap(array $documentationNodes): array
    {
        $navPathMap = [];
        foreach ($documentationNodes as $node) {
            $navPathMap[$node['owner']] = $node['navPath'];
        }

        return $navPathMap;
    }

    private function buildUsedByMap(array $documentationNodes): array
    {
        $usedBy = [];
        foreach ($documentationNodes as $node) {
            foreach ($node['uses'] as $used) {
                $lookupKey = ltrim(trim($used), '\\');
                if (! isset($usedBy[$lookupKey])) {
                    $usedBy[$lookupKey] = [];
                }
                $usedBy[$lookupKey][] = $node['owner'];
            }
        }

        return $usedBy;
    }

    private function generateDocTree(array $documentationNodes, array $registry, array $navPathMap, array $usedBy): array
    {
        $docTree = [];
        $pathRegistry = [];

        foreach ($documentationNodes as $node) {
            $pathSegments = array_map('trim', explode('/', $node['navPath']));
            $originalPageTitle = array_pop($pathSegments);
            $pageTitle = $originalPageTitle;

            // Handle potential conflicts by enumerating file names
            $baseFileName = $this->slug($pageTitle);
            $pathForConflictCheck = implode('/', array_map([$this, 'slug'], $pathSegments)).'/'.$baseFileName.'.md';

            // Determine the final page filename and title based on conflicts
            [$pageFileName, $pageTitle] = $this->resolveFileNameConflict(
                $pathRegistry,
                $pathForConflictCheck,
                $baseFileName,
                $pageTitle,
                $node
            );

            // Generate the markdown content
            $markdownContent = $this->generateMarkdownContent($node, $pageTitle, $registry, $navPathMap, $usedBy);

            // Build the path in the document tree
            $docTree = $this->addToDocTree($docTree, $pathSegments, $originalPageTitle, $pageFileName, $markdownContent);
        }

        return $docTree;
    }

    private function resolveFileNameConflict(
        array &$pathRegistry,
        string $pathForConflictCheck,
        string $baseFileName,
        string $pageTitle,
        array $node
    ): array {
        if (isset($pathRegistry[$pathForConflictCheck])) {
            $pathRegistry[$pathForConflictCheck]['count']++;
            $count = $pathRegistry[$pathForConflictCheck]['count'];
            $updatedPageTitle = $pageTitle." ({$count})"; // Update display title
            $updatedFileName = $baseFileName."-({$count}).md"; // Update file name

            $originalNodeInfo = $pathRegistry[$pathForConflictCheck]['nodes'][0];
            // Handle conflict information - would need a logger here

            $pathRegistry[$pathForConflictCheck]['nodes'][] = $node;

            return [$updatedFileName, $updatedPageTitle];
        } else {
            $pathRegistry[$pathForConflictCheck] = [
                'count' => 1,
                'nodes' => [$node],
            ];

            return [$baseFileName.'.md', $pageTitle];
        }
    }

    private function addToDocTree(array $docTree, array $pathSegments, string $originalPageTitle, string $pageFileName, string $markdownContent): array
    {
        // Create a copy to avoid modifying the original
        $result = $docTree;
        $path = [];

        // Build the full path to the target location
        foreach ($pathSegments as $segment) {
            $path[] = $segment;
        }

        // Update the tree with the new content
        $result = $this->setInNestedArray($result, $path, $originalPageTitle, $pageFileName, $markdownContent);

        return $result;
    }

    private function setInNestedArray(array $array, array $path, string $originalPageTitle, string $pageFileName, string $markdownContent): array
    {
        if (empty($path)) {
            // We've reached the target level, add the content here
            if (isset($array[$originalPageTitle]) && is_array($array[$originalPageTitle])) {
                // This is a directory, so the original becomes the index
                if ($pageFileName === $this->slug($originalPageTitle).'.md') {
                    $array[$originalPageTitle]['index.md'] = $markdownContent;
                } else {
                    $array[$pageFileName] = $markdownContent;
                }
            } else {
                $array[$pageFileName] = $markdownContent;
            }

            return $array;
        }

        $segment = array_shift($path);
        $fileKey = $this->slug($segment).'.md';

        // Initialize segment if it doesn't exist
        if (! isset($array[$segment])) {
            $array[$segment] = [];
        } elseif (isset($array[$fileKey]) && is_string($array[$fileKey])) {
            // If we have a file with the same name as a directory we need to create
            $fileContent = $array[$fileKey];
            unset($array[$fileKey]);
            $array[$segment] = ['index.md' => $fileContent];
        }

        // Recurse into the next level
        $array[$segment] = $this->setInNestedArray($array[$segment], $path, $originalPageTitle, $pageFileName, $markdownContent);

        return $array;
    }

    private function generateMarkdownContent(array $node, string $pageTitle, array $registry, array $navPathMap, array $usedBy): string
    {
        $markdownContent = "# {$pageTitle}\n\n";
        $markdownContent .= "Source: `{$node['owner']}`\n{:.page-subtitle}\n\n";
        $markdownContent .= $node['description'];

        // Add "Building Blocks Used" section
        if (! empty($node['uses'])) {
            $markdownContent .= $this->generateUsedComponentsSection($node, $registry, $navPathMap);
        }

        // Add "Used By Building Blocks" section
        $ownerKey = $node['owner'];
        if (isset($usedBy[$ownerKey])) {
            $markdownContent .= $this->generateUsedBySection($ownerKey, $usedBy, $registry, $navPathMap);
        }

        // Add "Further reading" section
        if (! empty($node['links'])) {
            $markdownContent .= $this->generateLinksSection($node['links']);
        }

        return $markdownContent;
    }

    private function generateUsedComponentsSection(array $node, array $registry, array $navPathMap): string
    {
        $content = "\n\n## Building Blocks Used\n\n";
        $content .= "This functionality is composed of the following reusable components:\n\n";

        $mermaidLinks = [];
        $mermaidContent = "graph LR\n";
        $ownerId = $this->slug($node['owner']);
        $ownerNavPath = $navPathMap[$node['owner']] ?? '';
        $mermaidContent .= "    {$ownerId}[\"{$ownerNavPath}\"];\n";

        $sourcePath = $registry[$node['owner']] ?? '';

        foreach ($node['uses'] as $used) {
            $usedRaw = trim($used);
            $lookupKey = ltrim($usedRaw, '\\');
            $usedId = $this->slug($usedRaw);
            $usedNavPath = $navPathMap[$lookupKey] ?? $usedRaw;

            if (isset($registry[$lookupKey])) {
                $targetPath = $registry[$lookupKey];
                $relativeFilePath = $this->makeRelativePath($targetPath, $sourcePath);
                $relativeUrl = $this->toCleanUrl($relativeFilePath);

                $content .= "* [{$usedNavPath}]({$relativeUrl})\n";
                $mermaidContent .= "    {$ownerId} --> {$usedId}[\"{$usedNavPath}\"];\n";
                $mermaidLinks[] = "click {$usedId} \"{$relativeUrl}\" \"View documentation for {$usedRaw}\"";
            } else {
                $content .= "* {$usedNavPath} (Not documented)\n";
                $mermaidContent .= "    {$ownerId} --> {$usedId}[\"{$usedNavPath}\"];\n";
            }
        }

        $content .= "\n\n### Composition Graph\n\n";
        $content .= "```mermaid\n";
        $content .= $mermaidContent;
        $content .= "    style {$ownerId} fill:#ffe7cd,stroke:#b38000,stroke-width:4px\n";
        if (! empty($mermaidLinks)) {
            $content .= '    '.implode("\n    ", $mermaidLinks)."\n";
        }
        $content .= "```\n";

        return $content;
    }

    private function generateUsedBySection(string $ownerKey, array $usedBy, array $registry, array $navPathMap): string
    {
        $content = "\n\n## Used By Building Blocks\n\n";
        $content .= "This component is a building block for the following functionalities:\n\n";

        $mermaidLinks = [];
        $mermaidContent = "graph LR\n";
        $ownerId = $this->slug($ownerKey);
        $ownerNavPath = $navPathMap[$ownerKey] ?? $ownerKey;
        $mermaidContent .= "    {$ownerId}[\"{$ownerNavPath}\"];\n";

        $sourcePath = $registry[$ownerKey] ?? '';

        foreach ($usedBy[$ownerKey] as $user) {
            $userKey = ltrim(trim($user), '\\');
            $userId = $this->slug($userKey);
            $userNavPath = $navPathMap[$userKey] ?? $userKey;

            if (isset($registry[$userKey])) {
                $targetPath = $registry[$userKey];
                $relativeFilePath = $this->makeRelativePath($targetPath, $sourcePath);
                $relativeUrl = $this->toCleanUrl($relativeFilePath);

                $content .= "* [{$userNavPath}]({$relativeUrl})\n";
                $mermaidContent .= "    {$userId}[\"{$userNavPath}\"] --> {$ownerId};\n";
                $mermaidLinks[] = "click {$userId} \"{$relativeUrl}\" \"View documentation for {$user}\"";
            } else {
                $content .= "* {$userNavPath} (Not documented)\n";
                $mermaidContent .= "    {$userId}[\"{$userNavPath}\"] --> {$ownerId};\n";
            }
        }

        $content .= "\n\n### Dependency Graph\n\n";
        $content .= "```mermaid\n";
        $content .= $mermaidContent;
        $content .= "    style {$ownerId} fill:#ffe7cd,stroke:#b38000,stroke-width:4px\n";
        if (! empty($mermaidLinks)) {
            $content .= '    '.implode("\n    ", $mermaidLinks)."\n";
        }
        $content .= "```\n";

        return $content;
    }

    private function generateLinksSection(array $links): string
    {
        $content = "\n\n## Further reading\n\n";
        foreach ($links as $link) {
            $trimmedLink = trim($link);
            if (preg_match('/^\[.*\]\s*\(.*\)$/', $trimmedLink)) {
                $content .= "* {$trimmedLink}\n";
            } elseif (preg_match('/^(\S+)\s+(.*)$/', $trimmedLink, $matches)) {
                $content .= "* [{$matches[2]}]({$matches[1]})\n";
            } else {
                $content .= "* [{$trimmedLink}]({$trimmedLink})\n";
            }
        }

        return $content;
    }

    private function generateFiles(array $tree, string $currentPath): void
    {
        foreach ($tree as $key => $value) {
            // Use the original key for directory creation, slugify for file paths
            $newPath = $currentPath.'/'.$this->slug($key);
            if (is_array($value)) {
                $this->filesystem->makeDirectory($newPath);
                $this->generateFiles($value, $newPath);
            } else {
                // key is already the correct filename.md
                $this->filesystem->put($currentPath.'/'.$key, $value);
            }
        }
    }

    private function generateNavStructure(array $tree, string $pathPrefix = ''): array
    {
        $nav = [];
        foreach ($tree as $key => $value) {
            if ($key === 'index.md') {
                continue;
            }

            $title = ucwords(str_replace(['-', '-(', ')'], [' ', ' (', ')'], pathinfo($key, PATHINFO_FILENAME)));
            $filePath = $pathPrefix.$key;

            if (is_array($value)) {
                $dirName = ucwords(str_replace('-', ' ', $key));
                $nav[] = [
                    $dirName => $this->generateNavStructure($value, $pathPrefix.Str::slug($key).'/'),
                ];
            } else {
                $nav[] = [$title => $filePath];
            }
        }

        return $nav;
    }

    private function dumpAsYaml(array $data, string $outputPath): void
    {
        $yamlString = Yaml::dump($data, 6, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        $yamlString = preg_replace("/'(!!python[^']*)'/", '$1', $yamlString);
        $this->filesystem->put($outputPath, $yamlString);
    }

    private function slug(string $seg): string
    {
        return Str::slug($seg, dictionary: ['::' => '-']);
    }

    private function makeRelativePath(string $path, string $base): string
    {
        if (strpos($path, dirname($base)) === 0) {
            return './'.substr($path, strlen(dirname($base).'/'));
        }

        return $path;
    }

    private function toCleanUrl(string $path): string
    {
        $url = preg_replace('/\.md$/', '', $path);
        if (basename($url) === 'index') {
            $url = dirname($url);
        }

        return ($url === '.' || $url === '') ? '' : rtrim($url, '/').'/';
    }
}
