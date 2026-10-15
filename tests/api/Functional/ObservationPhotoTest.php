<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Observation\Entity\Observation;
use App\Tests\Factory\ObservationFactory;
use App\Tests\Factory\UserFactory;
use App\User\Entity\User;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ObservationPhotoTest extends ApiTestCase
{
    private const string PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    private User $me;
    private Observation $observation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->me = UserFactory::createOne();
        $this->observation = ObservationFactory::createOne(['author' => $this->me]);
        $this->loginAs($this->me);
    }

    protected function tearDown(): void
    {
        $this->storage()->deleteDirectory('observations');

        parent::tearDown();
    }

    public function testAPhotoIsStoredAndServedBack(): void
    {
        $photos = $this->upload();

        $this->assertStatus(201);
        self::assertCount(1, $photos);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/', $photos[0]);
        self::assertTrue($this->storage()->fileExists($this->path($photos[0])));

        $this->client->request('GET', $this->url($photos[0]));
        $this->assertStatus(200);
        self::assertSame('image/png', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testAnObservationTakesThreePhotosAtMost(): void
    {
        $this->upload();
        $this->upload();
        $this->upload();

        $this->upload();

        $this->assertStatus(422);
    }

    public function testOnlyJpegAndPngAreAccepted(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'note');
        file_put_contents($file, 'Juste du texte');

        $this->client->request('POST', $this->url(), files: ['photo' => new UploadedFile($file, 'note.txt', 'text/plain', test: true)]);

        $this->assertStatus(422);
    }

    public function testRemovingAPhotoDeletesTheFile(): void
    {
        $name = $this->upload()[0];

        $observation = $this->json('DELETE', $this->url($name));
        $this->assertStatus(200);
        self::assertSame([], $observation['photos']);
        self::assertFalse($this->storage()->fileExists($this->path($name)));

        $this->json('DELETE', $this->url($name));
        $this->assertStatus(404);
    }

    public function testAnUnknownPhotoIsNotFound(): void
    {
        $this->client->request('GET', $this->url(str_repeat('a', 32).'.jpg'));

        $this->assertStatus(404);
    }

    public function testSomeoneElsesPhotosAreOffLimits(): void
    {
        $name = $this->upload()[0];
        $this->loginAs(UserFactory::createOne());

        $this->client->request('GET', $this->url($name));
        $this->assertStatus(403);

        $this->upload();
        $this->assertStatus(403);

        $this->json('DELETE', $this->url($name));
        $this->assertStatus(403);
    }

    public function testDeletingTheObservationDeletesItsPhotos(): void
    {
        $name = $this->upload()[0];

        $this->json('DELETE', '/api/observations/'.$this->observation->getId());

        $this->assertStatus(204);
        self::assertFalse($this->storage()->fileExists($this->path($name)));
    }

    /**
     * @return list<string>
     */
    private function upload(): array
    {
        $file = tempnam(sys_get_temp_dir(), 'photo');
        file_put_contents($file, base64_decode(self::PNG));

        $this->client->request('POST', $this->url(), files: ['photo' => new UploadedFile($file, 'photo.png', 'image/png', test: true)]);

        return json_decode((string) $this->client->getResponse()->getContent(), true)['photos'] ?? [];
    }

    private function url(?string $name = null): string
    {
        return '/api/observations/'.$this->observation->getId().'/photos'.(null === $name ? '' : '/'.$name);
    }

    private function path(string $name): string
    {
        return 'observations/'.$this->observation->getId().'/'.$name;
    }

    private function storage(): FilesystemOperator
    {
        return static::getContainer()->get('photos.storage');
    }
}
