<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Commands;

use Bambamboole\LaravelOpenApi\FunctionalDocBlockExtractor;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * A brief summary of what this class or method does.
 *
 * @functional
 * This is the main description of the functionality. It can span multiple
 * lines, and paragraphs are created automatically.
 *
 * You can use standard Markdown formatting like **bold** and `inline code`.
 *
 * # Section Headings
 *
 * Use Markdown headings (starting with `#`) to structure your document. The
 * script will automatically "demote" them to fit the page structure, so you'll
 * see this render as an `<h2>` on the page.
 *
 * Here is a list of key features:
 * - **Feature One:** Does something important.
 *     - You can have nested bullets.
 *     - The script correctly preserves the indentation.
 * - **Feature Two:** Handles another case.
 *
 * ## Mermaid Diagrams
 *
 * You can embed Mermaid charts for diagrams and flowcharts. The script is
 * smart enough to handle pasted code with different indentation.
 *
 * ```mermaid
 * graph TD
 * A[Start] --> B{Is it valid?};
 * B -->|Yes| C[Process Data];
 * B -->|No| D[Log Error];
 * C --> E[End];
 * D --> E;
 * ```
 *
 * The script will correctly render this as a visual diagram.
 *
 * * @nav Main Section / Sub Section / My Documentation Page
 *
 * @link [https://link-to-relevant-docs.com](https://link-to-relevant-docs.com)
 *
 * @links [A pre-formatted link](https://another-link.com)
 *
 * @uses \Bambamboole\LaravelOpenApi\FunctionalDocBlockExtractor
 *
 * @throws \Exception When something goes wrong.
 */
class GenerateMKDocsCommand extends Command
{
    protected $signature = 'mkdocs:generate {path? : The base path for the docs output directory}';

    public function handle(Filesystem $fs): int
    {
        $docsBaseDir = $this->hasArgument('path') ? $this->argument('path') : config('mkdocs.output');
        $mkdocsConfigPath = $docsBaseDir.'/mkdocs.yml';
        $docsOutputDir = $docsBaseDir.'/generated';

        $parser = (new ParserFactory)->createForNewestSupportedVersion();
        $files = Finder::create()
            ->files()
            ->in(config()->array('mkdocs.paths'))
            ->name('*.php');
        $functionalExtractor = new FunctionalDocBlockExtractor;
        $traverser = new NodeTraverser;
        $traverser->addVisitor($functionalExtractor);
        foreach ($files as $file) {
            try {
                $functionalExtractor->setCurrentFilePath($file->getRealPath());
                $code = $file->getContents();
                $ast = $parser->parse($code);
                $traverser->traverse($ast);
            } catch (\Throwable $e) {
                $this->components->error("Error parsing file: {$file->getRealPath()}\n{$e->getMessage()}");

                return self::FAILURE;
            }
        }

        $documentationNodes = $functionalExtractor->foundDocs;
        $this->components->info('Found '.count($documentationNodes).' documentation nodes.');

        if (empty($documentationNodes)) {
            $this->components->warn('No documentation nodes found. Skipping MKDocs generation.');

            return self::SUCCESS;
        }

        $this->components->info('Building documentation registry...');

        $registry = [];
        $titleMap = [];
        $navPathMap = [];
        foreach ($documentationNodes as $node) {
            $pathSegments = array_map('trim', explode('/', $node['navPath']));
            $pageTitle = array_pop($pathSegments);

            $urlParts = array_map($this->slug(...), $pathSegments);
            $urlParts[] = $this->slug($pageTitle).'.md';

            $registry[$node['owner']] = implode('/', $urlParts);
            $titleMap[$node['owner']] = $pageTitle;
            $navPathMap[$node['owner']] = $node['navPath'];
        }

        $this->components->info('Building reverse dependency index...');

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

        $this->components->info('Building documentation tree...');

        $docTree = [];
        $pathRegistry = [];

        foreach ($documentationNodes as $node) {
            $pathSegments = array_map('trim', explode('/', $node['navPath']));
            $originalPageTitle = array_pop($pathSegments);
            $pageTitle = $originalPageTitle;

            // --- Handle potential conflicts by enumerating file names ---
            $baseFileName = $this->slug($pageTitle);

            $pathForConflictCheck = implode('/', array_map($this->slug(...), $pathSegments)).'/'.$baseFileName.'.md';

            if (isset($pathRegistry[$pathForConflictCheck])) {
                $pathRegistry[$pathForConflictCheck]['count']++;
                $count = $pathRegistry[$pathForConflictCheck]['count'];
                $pageTitle .= " ({$count})"; // Update display title
                $pageFileName = $baseFileName."-({$count}).md"; // Update file name

                $originalNodeInfo = $pathRegistry[$pathForConflictCheck]['nodes'][0];
                // Log conflict information

                $msg = "\n[CONFLICT] Duplicate navigation path '{$node['navPath']}'.\n";
                $msg .= "  - Original: {$originalNodeInfo['sourceFile']}:{$originalNodeInfo['startLine']} ({$originalNodeInfo['owner']})\n";
                $msg .= "  - New:      {$node['sourceFile']}:{$node['startLine']} ({$node['owner']})\n";
                $msg .= "  - Action:   Creating enumerated page '{$pageTitle}'.\n";
                $this->components->warn($msg);

                $pathRegistry[$pathForConflictCheck]['nodes'][] = $node;

            } else {
                $pageFileName = $baseFileName.'.md';
                $pathRegistry[$pathForConflictCheck] = [
                    'count' => 1,
                    'nodes' => [$node],
                ];
            }

            // --- Generate Markdown Content ---
            $markdownContent = "# {$pageTitle}\n\n";
            $markdownContent .= "Source: `{$node['owner']}`\n{:.page-subtitle}\n\n";
            $markdownContent .= $node['description'];

            // --- Add "Building Blocks Used" section ---
            if (! empty($node['uses'])) {
                $markdownContent .= "\n\n## Building Blocks Used\n\n";
                $markdownContent .= "This functionality is composed of the following reusable components:\n\n";

                $mermaidLinks = [];
                $mermaidContent = "graph LR\n";
                $ownerId = $this->slug($node['owner']);
                $ownerNavPath = $navPathMap[$node['owner']] ?? $pageTitle;
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

                        $markdownContent .= "* [{$usedNavPath}]({$relativeUrl})\n";
                        $mermaidContent .= "    {$ownerId} --> {$usedId}[\"{$usedNavPath}\"];\n";
                        $mermaidLinks[] = "click {$usedId} \"{$relativeUrl}\" \"View documentation for {$usedRaw}\"";
                    } else {
                        $markdownContent .= "* {$usedNavPath} (Not documented)\n";
                        $mermaidContent .= "    {$ownerId} --> {$usedId}[\"{$usedNavPath}\"];\n";
                    }
                }

                $markdownContent .= "\n\n### Composition Graph\n\n";
                $markdownContent .= "```mermaid\n";
                $markdownContent .= $mermaidContent;
                $markdownContent .= "    style {$ownerId} fill:#ffe7cd,stroke:#b38000,stroke-width:4px\n";
                if (! empty($mermaidLinks)) {
                    $markdownContent .= '    '.implode("\n    ", $mermaidLinks)."\n";
                }
                $markdownContent .= "```\n";
            }

            // --- Add "Used By Building Blocks" section ---
            $ownerKey = $node['owner'];
            if (isset($usedBy[$ownerKey])) {
                $markdownContent .= "\n\n## Used By Building Blocks\n\n";
                $markdownContent .= "This component is a building block for the following functionalities:\n\n";

                $mermaidLinks = [];
                $mermaidContent = "graph LR\n";
                $ownerId = $this->slug($ownerKey);
                $ownerNavPath = $navPathMap[$ownerKey] ?? $pageTitle;
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

                        $markdownContent .= "* [{$userNavPath}]({$relativeUrl})\n";
                        $mermaidContent .= "    {$userId}[\"{$userNavPath}\"] --> {$ownerId};\n";
                        $mermaidLinks[] = "click {$userId} \"{$relativeUrl}\" \"View documentation for {$user}\"";
                    } else {
                        $markdownContent .= "* {$userNavPath} (Not documented)\n";
                        $mermaidContent .= "    {$userId}[\"{$userNavPath}\"] --> {$ownerId};\n";
                    }
                }

                $markdownContent .= "\n\n### Dependency Graph\n\n";
                $markdownContent .= "```mermaid\n";
                $markdownContent .= $mermaidContent;
                $markdownContent .= "    style {$ownerId} fill:#ffe7cd,stroke:#b38000,stroke-width:4px\n";
                if (! empty($mermaidLinks)) {
                    $markdownContent .= '    '.implode("\n    ", $mermaidLinks)."\n";
                }
                $markdownContent .= "```\n";
            }

            // Add "Further reading" section
            if (! empty($node['links'])) {
                $markdownContent .= "\n\n## Further reading\n\n";
                foreach ($node['links'] as $link) {
                    $trimmedLink = trim($link);
                    if (preg_match('/^\[.*\]\s*\(.*\)$/', $trimmedLink)) {
                        $markdownContent .= "* {$trimmedLink}\n";
                    } elseif (preg_match('/^(\S+)\s+(.*)$/', $trimmedLink, $matches)) {
                        $markdownContent .= "* [{$matches[2]}]({$matches[1]})\n";
                    } else {
                        $markdownContent .= "* [{$trimmedLink}]({$trimmedLink})\n";
                    }
                }
            }

            $currentLevel = &$docTree;

            foreach ($pathSegments as $segment) {
                $fileKey = $this->slug($segment).'.md';
                if (isset($currentLevel[$fileKey]) && is_string($currentLevel[$fileKey])) {
                    $fileContent = $currentLevel[$fileKey];
                    unset($currentLevel[$fileKey]);
                    $currentLevel[$segment] = ['index.md' => $fileContent];
                }
                if (! isset($currentLevel[$segment])) {
                    $currentLevel[$segment] = [];
                }
                $currentLevel = &$currentLevel[$segment];
            }

            if (isset($currentLevel[$originalPageTitle]) && is_array($currentLevel[$originalPageTitle])) {
                // This is a directory, so the original becomes the index.
                // The conflict becomes a sibling page.
                if ($pageFileName === $baseFileName.'.md') {
                    $currentLevel[$originalPageTitle]['index.md'] = $markdownContent;
                } else {
                    $currentLevel[$pageFileName] = $markdownContent;
                }
            } else {
                $currentLevel[$pageFileName] = $markdownContent;
            }
        }

        $fs->deleteDirectory($docsOutputDir);
        $fs->makeDirectory($docsOutputDir, recursive: true);
        $welcomeContent = "# Welcome\n\nThis is the automatically generated functional documentation for the project. \n\nUse the navigation on the left to explore the documented processes.";
        $fs->put($docsOutputDir.'/index.md', $welcomeContent);

        $this->generateFiles($docTree, $docsOutputDir, $fs);

        $navStructure = [];
        foreach ($docTree as $topLevelKey => $value) {
            $dirName = ucwords(str_replace('-', ' ', $topLevelKey));
            $navStructure[] = [$dirName => $this->generateNav($value, $this->slug($topLevelKey).'/')];
        }
        array_unshift($navStructure, ['Home' => 'index.md']);

        $config = config()->array('mkdocs.config', []);
        $config['nav'] = $navStructure;
        $this->dumpAsYaml($config, $mkdocsConfigPath);

        $this->components->info('MKDocs configuration generated successfully.');
        $this->components->info('Documentation files generated successfully.');

        Process::run("docker run --rm -v {$docsBaseDir}:/docs squidfunk/mkdocs-material build");

        return self::SUCCESS;
    }

    private function getPaths(): array
    {
        return config('mkdocs.paths', [
            dirname(__DIR__),
        ]);
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

    private function generateFiles(array $tree, string $currentPath, Filesystem $fs): void
    {
        foreach ($tree as $key => $value) {
            // Use the original key for directory creation, slugify for file paths
            $newPath = $currentPath.'/'.$this->slug($key);
            if (is_array($value)) {
                $fs->makeDirectory($newPath);
                $this->generateFiles($value, $newPath, $fs);
            } else {
                // key is already the correct filename.md
                $fs->put($currentPath.'/'.$key, $value);
            }
        }
    }

    private function generateNav(array $tree, string $pathPrefix = ''): array
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
                    $dirName => $this->generateNav($value, $pathPrefix.Str::slug($key).'/'),
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
        file_put_contents($outputPath, $yamlString);
    }
}
