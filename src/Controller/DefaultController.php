<?php

namespace Leankoala\SuluDocBundle\Controller;

use Sulu\Bundle\ContentBundle\Repository\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    public function indexAction($tag, $lang)
    {
        $results = [];

        $tagManager = $this->get('sulu_tag.tag_manager');
        $tag = $tagManager->findByName($tag);

        if ($tag) {
            $languageCode = 'en';

            $exclude = null;
            $webspaceKey = 'example';
            $limitResult = 1;
            $tagNames = 'sulu';
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

            $contents = $repository->getFilteredNodes(
                $filterConfig,
                $lang,
                $webspaceKey,
                true,
                true,
                $exclude !== null ? [$exclude] : []
            );

            $contents = $contents['_embedded']['nodes'];

            foreach ($contents as $content) {
                $results[] = [
                    'title' => $content['title'],
                    'url' => $content['urls'][$lang]
                ];
            }
        }

        $yaml = Yaml::dump($results);

        return new Response($yaml);
    }
}
