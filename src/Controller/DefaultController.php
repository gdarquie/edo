<?php

namespace App\Controller;

use Elasticsearch\ClientBuilder;
use Shivas\VersioningBundle\Service\VersionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(VersionManager $manager)
    {
        $version = $manager->getVersion();
        $payload = $version;

        return new JsonResponse($payload);
    }

    /**
     * @Route("/elastic", name="elastica")
     */
    public function testElastica()
    {
        $params['hosts'] = array (
            $_ENV["BONSAI_URL"],
        );

        $builder = ClientBuilder::create();
        $builder->setHosts([$_ENV["BONSAI_URL"]]);
        $client = $builder->build();

        $params = [
            'index' => 'ficti',
            'body'  => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        $response = $client->indices()->create($params);

        dd($response);
        $params = [
            'index' => 'my_index',
            'body'  => ['testField' => 'abc']
        ];

        $response = $client->index($params);
        dd($response);


        //https://utxvelwd3h:13lq3vlj90@ficti-652449611.eu-central-1.bonsaisearch.net:443
        $elasticaClient = new \Elastica\Client(array(
            'url' => 'https://u6p5eo0soj:sjniv36qc0@ficti-1334407937.eu-central-1.bonsaisearch.net:443',
        ));
        $index = $elasticaClient->getIndex('ficti');

// Create the index new
        $index->create(
            array(
                'number_of_shards' => 4,
                'number_of_replicas' => 1,
                'analysis' => array(
                    'analyzer' => array(
                        'default_index' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('lowercase', 'mySnowball')
                        ),
                        'default_search' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('standard', 'lowercase', 'mySnowball')
                        )
                    ),
                    'filter' => array(
                        'mySnowball' => array(
                            'type' => 'snowball',
                            'language' => 'German'
                        )
                    )
                )
            ),
            true
        );


        $search = new \Elastica\Search($elasticaClient);

        $query = new \Elastica\Query([
            'query' => [
                'term' => ['_all' => 'search term'],
            ],
        ]);

        $search->setQuery($query);
        $numberOfEntries = $search->count();
        dd($numberOfEntries);
    }

}
