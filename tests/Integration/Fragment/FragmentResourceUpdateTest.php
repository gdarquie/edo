<?php


namespace App\Tests\Integration\Fragment;

use App\DataFixtures\FragmentFixtures;

class FragmentResourceUpdateTest extends AbstractFragmentResource
{
    /**
     * @Description: we create a new fragment for an existing narrative, it's like updating this narrative
     */
    public function testUpdateNarrative()
    {
        // at first, we count the number of existing narratives
        $this->assertEquals(8, count($this->fragmentRepository->findAll()), 'Uncorrect number of narratives');

        // and we count fragments
        $this->assertEquals(11, count($this->versionRepository->findAll()), 'Uncorrect number of fragments');

        // send request to create a new fragment for an existing narrative
        $this->data['uuid'] = '6284e5ac-09cf-4334-9503-dedf31bafdd0';
        $this->data['parent_uuid'] = '1b4705aa-4abd-4931-add0-ac11b6fff0c3';

        // create a new fragment for an existing narrative
        $this->client->request('POST', 'api/fragments', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $this->data
        ]);

        $this->assertResponseIsSuccessful();

        // there are no more narratives but one more fragments
        $this->assertEquals(8, count($this->fragmentRepository->findAll()), 'Uncorrect number of narratives');
        $this->assertEquals(12, count($this->versionRepository->findAll()), 'Uncorrect number of fragments');

        // get the updated narrative
        $response = $this->client->request('GET', 'api/fragments/'.$this->data['uuid']);
        $arrayResponse = $response->toArray();

        // check if there is one more fragment
        $this->assertEquals(3, count($arrayResponse['fragments']));

        // check the fragment data
        $this->assertNotEquals($arrayResponse['fragments'][0]['content'], $arrayResponse['fragments'][2]['content']);
        $this->assertEquals($arrayResponse['fragments'][0]['content'], $this->content);

        // check if narrative infos has been correctly updated
        $this->assertEquals($arrayResponse['content'], $this->content);
        $this->assertEquals($this->data['uuid'], $arrayResponse['uuid']);
        $this->assertEquals($this->data['parent_uuid'], $arrayResponse['parent_uuid']);
    }

    public function testVersionningLimitFragmentsForANarrative()
    {
        // at first, we count the number of existing narratives
        $this->assertEquals(8, count($this->fragmentRepository->findAll()), 'Uncorrect number of narratives');

        // we select an existing narrative and count the number of fragments
        $narrativeUuid = '6284e5ac-09cf-4334-9503-dedf31bafdd0';
        $this->assertEquals(2, count($this->versionRepository->findNarrativeLastFragments($narrativeUuid)), 'Uncorrect number of fragments');

        $narrative = $this->fragmentRepository->findOneByUuid($narrativeUuid);

        // we add 8 fragments
        for ($i=0; $i < 8; $i++) {
            $this->em->persist(FragmentFixtures::generateFragment($narrative));
            $this->em->flush();
        }

        // check we have 10 fragments now
        $this->assertEquals(10, count($this->versionRepository->findNarrativeLastFragments($narrativeUuid)), 'Uncorrect number of fragments');

        // send request to create a new fragment for an existing narrative
        $this->data['uuid'] = $narrativeUuid;

        // create a new fragment for an existing narrative
        $this->client->request('POST', 'api/fragments', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $this->data
        ]);

        // check if we have still max numbers of fragments, VERSIONING_MAX variable in .env.test defines the limit
        $this->assertEquals(10, count($this->versionRepository->findNarrativeLastFragments($narrativeUuid)), 'Uncorrect number of fragments');
    }
}