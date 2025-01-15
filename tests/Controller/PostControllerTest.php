<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PostControllerTest extends WebTestCase
{
    private const NEW_VALUE = 'Something New';

    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $postRepository;
    private string $path = '/post/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->postRepository = $this->manager->getRepository(Post::class);

        foreach ($this->postRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'post[name]' => 'Testing',
            'post[details]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->postRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Post();
        $fixture->setName('My Title');
        $fixture->setDetails('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Post();
        $fixture->setName('Value');
        $fixture->setDetails('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'post[name]' => self::NEW_VALUE,
            'post[details]' => self::NEW_VALUE,
        ]);

        self::assertResponseRedirects($this->path);

        $fixture = $this->postRepository->findAll();

        self::assertSame(self::NEW_VALUE, $fixture[0]->getName());
        self::assertSame(self::NEW_VALUE, $fixture[0]->getDetails());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Post();
        $fixture->setName('Value');
        $fixture->setDetails('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->postRepository->count([]));
    }
}
