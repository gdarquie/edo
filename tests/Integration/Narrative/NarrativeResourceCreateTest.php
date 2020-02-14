<?php

declare(strict_types=1);

namespace App\Tests\Integration\Narrative;

/**
 * Class NarrativeResourceCreateTest
 * @package App\Tests\Integration\Narrative
 */
class NarrativeResourceCreateTest extends AbstractNarrativeResource
{
    public function testCreateNarrative()
    {
        $title = 'Title example from phpunit';
        $content = 'This content is generated by testPostNarrative';

        // at first, we count the number of existing narratives
        $this->assertEquals(2, count($this->narrativeRepository->findAll()), 'Uncorrect number of narratives');

        // and we count fragments
        $this->assertEquals(3, count($this->fragmentRepository->findAll()), 'Uncorrect number of fragments');

        $data = [
            'uuid' => '6153ca18-47a9-4b38-ae72-29e8340060cb',
            'title' => $title,
            'content' => $content,
            'fiction_uuid' => '1b7df281-ae2a-40bf-ad6a-ac60409a9ce6'
        ];

        $response = $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $arrayResponse = $response->toArray();
        $this->assertResponseIsSuccessful("Narrative hasn't been created correctly");
        $this->assertEquals($title, $arrayResponse['title']);
        $this->assertEquals($content, $arrayResponse['content']);
        // createdAt must be equal to updatedAt because a new narrative is created
        $this->assertEquals($arrayResponse['created_at'], $arrayResponse['updated_at']);

        // now, we check in database if everything is correct
        // we are supposed to have one more narrative and one more fragment
        $this->assertEquals(3, count($this->narrativeRepository->findAll()), 'Uncorrect number of narratives');
        $this->assertEquals(4, count($this->fragmentRepository->findAll()), 'Uncorrect number of fragments');

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
            'content' => $content,
            'fiction_uuid' => '1b7df281-ae2a-40bf-ad6a-ac60409a9ce6'
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
            'fiction_uuid' => '1b7df281-ae2a-40bf-ad6a-ac60409a9ce6'
        ];

        $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testCreateFragmentWithoutTitle()
    {
        $content = 'This content is generated by testPostNarrative';


        $data = [
            'content' => $content,
            'fiction_uuid' => '1b7df281-ae2a-40bf-ad6a-ac60409a9ce6'
        ];

        $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testCreateFragmentWithoutFiction()
    {
        $title = 'Title example from phpunit';
        $content = 'This content is generated by testPostNarrative';

        $data = [
            'uuid' => '6153ca18-47a9-4b38-ae72-29e8340060cb',
            'title' => $title,
            'content' => $content,
        ];

        $this->client->request('POST', 'api/narratives', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $data
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

}