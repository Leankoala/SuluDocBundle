<?php

namespace Leankoala\SuluDocBundle\Controller;

use Leankoala\SuluDocBundle\Twig\SectionExtension;
use Sulu\Bundle\ContentBundle\Document\PageDocument;
use Sulu\Bundle\ContentBundle\Repository\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class TagController extends Controller
{
    const SECTION_HEADING_KEY = 'section-heading';

    public function indexAction(Request $request, $tag, $lang)
    {
        $results = [];

        $contentMapper = $this->get('sulu.content.mapper');
        $structureResolver = $this->get('sulu_website.resolver.structure');
        $tagManager = $this->get('sulu_tag.tag_manager');

        $tag = $tagManager->findByName($tag);

        if ($tag) {
            $exclude = null;
            $webspaceKey = 'docs';
            // $webspaceKey = 'example';
            
            $resolvedTags = [$tag->getId()];

            $filterConfig = [
                'dataSource' => null,
                'tags' => $resolvedTags,
                'tag' => 'or',
                'sortBy' => 'name',
                'sortMethod' => 'asc',
            ];

            /** @var NodeRepository $repository */
            $repository = $this->get('sulu_content.node_repository');

            $nodes = $repository->getFilteredNodes(
                $filterConfig,
                $lang,
                $webspaceKey,
                true,
                true,
                $exclude !== null ? [$exclude] : []
            );

            $nodes = $nodes['_embedded']['nodes'];

            foreach ($nodes as $node) {

                $contentStructure = $contentMapper->load($node['uuid'], $webspaceKey, $lang);
                $content = $structureResolver->resolve($contentStructure);

                $withBlocks = false;

                $domain = $request->getSchemeAndHttpHost();

                //dump($content['except']);
                $plainCategories = $content['extension']['excerpt']['categories'];
                $categories = [];

                foreach ($plainCategories as $category) {
                    $categories[] = $category['name'];
                }

                if (array_key_exists('blocks', $content['content'])) {
                    $blocks = $content['content']['blocks'];
                    foreach ($blocks as $block) {
                        if ($block['type'] == self::SECTION_HEADING_KEY) {
                            $results[] = [
                                'title' => $block['text'],
                                'url' => $domain . $node['urls'][$lang] . '#' . SectionExtension::getSlug($block['text']),
                                'categories' => $categories
                            ];
                            $withBlocks = true;
                        }
                    }
                }

                /** @var PageDocument $node */
                if (!$withBlocks) {
                    $results[] = [
                        'title' => $node['title'],
                        'url' => $domain . $node['urls'][$lang],
                        'categories' => $categories
                    ];
                }
            }
        }


        $json = json_encode($results);

        return new JsonResponse($results);
    }
}
