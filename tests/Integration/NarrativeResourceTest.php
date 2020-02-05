<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Qualification;
use App\Repository\FragmentRepository;
use App\Repository\NarrativeRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;

/**
 * Class NarrativeResourceTest
 * @package App\Tests\Integration
 */
class NarrativeResourceTest extends EdoApiTestCase
{
    use FixturesTrait;

    /**
     * @Description = send GET request for one specific fragment
     */
    public function testGetNarrativeWithFragments()
    {
        $uuid = '6284e5ac-09cf-4334-9503-dedf31bafdd0';

        $response = $this->client->request('GET', 'api/narratives/'.$uuid);

        $this->assertResponseIsSuccessful();
        $arrayResponse = $response->toArray();
        $this->assertEquals(2, count($arrayResponse['fragments']));
        $this->assertEquals($arrayResponse['fragments'][1]['title'], 'Fragment title');
        $this->assertEquals($arrayResponse['fragments'][0]['title'], 'Fragment title 2');
        $this->assertEquals($arrayResponse['uuid'], '6284e5ac-09cf-4334-9503-dedf31bafdd0');
        $this->assertEquals($arrayResponse['content'], $arrayResponse['fragments'][0]['content']);
    }

    public function testGetNarrativesCollection()
    {
        // send GET request
        $response =  $this->client->request('GET', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->assertResponseIsSuccessful();
        $arrayResponse = $response->toArray();
        $this->assertCount(2, $arrayResponse['hydra:member']);
        $this->assertEquals('Fragment title 2', $arrayResponse['hydra:member'][0]['title']);
        $this->assertNotNull($arrayResponse['hydra:member'][1]['uuid']);
    }

    public function testGetNarrativeWithIncorrectUuid()
    {
        $uuid = 'cakeIsALie';
        $this->client->request('GET', 'api/narratives/'.$uuid);
        $this->assertResponseStatusCodeSame(500);
    }

    public function testGetNarrativeWithUnkwnonUuid()
    {
        $uuid = '9f6e6490-85f3-4d4e-82fd-e725a884fd8e';
        $this->client->request('GET', 'api/narratives/'.$uuid);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateNarrative()
    {
        $title = 'Title example from phpunit';
        $content = 'This content is generated by testPostNarrative';

        // we use the container for test of Symfony that let us use "private" services
        $container = self::$container;
        $narrativeRepository = $container->get(NarrativeRepository::class);
        $fragmentRepository = $container->get(FragmentRepository::class);

        // at first, we count the number of existing narratives
        $this->assertEquals(2, count($narrativeRepository->findAll()), 'Uncorrect number of narratives');

        // and we count fragments
        $this->assertEquals(3, count($fragmentRepository->findAll()), 'Uncorrect number of fragments');

        $data = [
            'uuid' => '6153ca18-47a9-4b38-ae72-29e8340060cb',
            'title' => $title,
            'content' => $content
        ];

        $response = $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $arrayResponse = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertEquals($title, $arrayResponse['title']);
        $this->assertEquals($content, $arrayResponse['content']);
        // createdAt must be equal to updatedAt because a new narrative is created
        $this->assertEquals($arrayResponse['createdAt'], $arrayResponse['updatedAt']);

        // now, we check in database if everything is correct
        // we are supposed to have one more narrative and one more fragment
        $this->assertEquals(3, count($narrativeRepository->findAll()), 'Uncorrect number of narratives');
        $this->assertEquals(4, count($fragmentRepository->findAll()), 'Uncorrect number of fragments');

        // we check if we get our new narrative with a GET call
        $response =  $this->client->request('GET', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $this->assertResponseIsSuccessful();
        $arrayResponse = $response->toArray();
        $this->assertEquals($title, $arrayResponse['hydra:member'][2]['title']);
        $this->assertEquals($content, $arrayResponse['hydra:member'][2]['content']);
    }

    public function testCreateFragmentWithoutUuid()
    {
        $title = 'Title example from phpunit';
        $content = 'This content is generated by testPostNarrative';

        $data = [
            'title' => $title,
            'content' => $content
        ];

        $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testCreateFragmentWithoutContent()
    {
        $title = 'Title example from phpunit';

        $data = [
            'title' => $title,
        ];

        $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testCreateFragmentWithoutTitle()
    {
        $title = 'Title example from phpunit';
        $content = 'This content is generated by testPostNarrative';


        $data = [
            'title' => $title,
            'content' => $content
        ];

        $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testUpdateNarrative()
    {

    }

    //    public function testPostFragment()
//    {
//        $container = static::$container;
//        $em = $container->get(EntityManagerInterface::class);
//
//        // before we created our fragment, we have 3 fragments in db
//        $this->assertEquals(3, count($em->getRepository(Fragment::class)->findAll()));
//
//        $data = '{"uuid":"57f107f2-a4cb-4b2d-862e-5c5fd8cf853e","code":"1","title":"First post test","content":"My first content with postman"}';
//
//        $response =  $this->client->request('POST', 'api/fragments', [
//            'json' => json_decode($data),
//            'headers' => ['Content-Type' => 'application/json']
//        ]);
//
//        $arrayResponse = $response->toArray();
//        $this->assertResponseIsSuccessful();
//        $this->assertEquals('My first content with postman', $arrayResponse['content']);
//
//        // we check that we have added another fragment
//        $this->assertEquals(4, count($em->getRepository(Fragment::class)->findAll()));
//    }
//
//    /**
//     * @throws \Exception
//     */
//    protected function createFragments()
//    {
//        $fragment = new Fragment();
//        $fragment->setTitle('Title');
//        $fragment->setContent('Some content');
//        $fragment->setCode('1234');
//        $uuid = Uuid::uuid4();
//        $fragment->setUuid($uuid);
//
//        $fragment2 = new Fragment();
//        $fragment2->setTitle('Another Title');
//        $fragment2->setContent('Some other content for text with 1234');
//        $fragment2->setCode('1234');
//        $now = new \DateTime();
//        // we add 30 seconds to the fragment creation date to be sur it is the last one
//        $fragment2->setCreatedAt($now->add(new \DateInterval('PT30S')));
//        $fragment2->setUpdatedAt($now->add(new \DateInterval('PT30S')));
//        // we fix an uuid for this fragment
//        $fragment->setUuid('35be83ef-a35a-4b8f-b59c-4aca2ce461b2');
//
//        $fragment3 = new Fragment();
//        $fragment3->setTitle('Title 2');
//        $fragment3->setContent('Some other content');
//        $fragment3->setCode('12345');
//        $fragment3->setParent($fragment2);
//        $uuid3 = Uuid::uuid4();
//        $fragment2->setUuid($uuid3);
//
//        self::bootKernel();
//        $container = static::$container;
//        $em = $container->get(EntityManagerInterface::class);
//        $em->persist($fragment);
//        $em->persist($fragment2);
//        $em->persist($fragment3);
//        $em->flush();
//    }

}