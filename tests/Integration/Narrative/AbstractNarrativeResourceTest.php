<?php


namespace App\Tests\Integration\Narrative;


use App\Repository\FragmentRepository;
use App\Repository\NarrativeRepository;
use App\Tests\AbstractEdoApiTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;

/**
 * Class AbstractNarrativeResourceTest
 * @package App\Tests\Integration\Narrative
 */
class AbstractNarrativeResourceTest extends AbstractEdoApiTestCase
{
    use FixturesTrait;

    protected $data;

    protected $title;

    protected $content;

    protected $narrativeRepository;

    protected $fragmentRepository;

    public function setUp()
    {
        parent::setUp();

        $this->title = 'Title example from phpunit';
        $this->content = 'This content is generated by testPostNarrative';

        $this->data = [
            'uuid' => '6153ca18-47a9-4b38-ae72-29e8340060cb',
            'title' => $this->title,
            'content' => $this->content
        ];

        $container = self::$container;
        $this->narrativeRepository = $container->get(NarrativeRepository::class);
        $this->fragmentRepository = $container->get(FragmentRepository::class);
    }
}